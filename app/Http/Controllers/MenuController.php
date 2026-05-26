<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Table;
use App\Models\TableParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function show(Request $request, string $qrCode = null)
    {
        // Inicializar variáveis
        $isCounter = false;
        $table = null;

        // Se tiver um QR code, busca a mesa específica ou verifica se é QR code de balcão
        if ($qrCode) {
            // Tentar encontrar uma mesa com esse QR code
            $table = Table::where('qr_code', $qrCode)->first();

            if ($table) {
                // É uma mesa
                $store = $table->store;

                // Verifica se o usuário está autenticado na mesa via session
                $sessionKey = 'table_' . $table->id . '_authenticated';
                $participantIdKey = 'table_' . $table->id . '_participant_id';
                $isAuthenticated = $request->session()->get($sessionKey, false);
                $participantId = $request->session()->get($participantIdKey);

                // Verificar se o participante ainda existe (pode ter sido removido ao desocupar a mesa)
                if ($isAuthenticated && $participantId) {
                    $participantExists = \App\Models\TableParticipant::where('id', $participantId)
                        ->where('table_id', $table->id)
                        ->exists();

                    // Se o participante não existe mais, invalidar a sessão
                    if (!$participantExists) {
                        $request->session()->forget($sessionKey);
                        $request->session()->forget($participantIdKey);
                        $isAuthenticated = false;
                    }
                }

                // Se não estiver autenticado, não carrega o cardápio completo
                if (!$isAuthenticated) {
                    $categories = collect(); // Array vazio
                    return view('menu.show', compact('store', 'table', 'categories', 'isCounter'));
                }
            } else {
                // Não é uma mesa, verificar se é QR code de balcão
                $store = Store::where('counter_qr_code', $qrCode)->first();

                if (!$store) {
                    // QR code não encontrado
                    abort(404, 'QR Code não encontrado');
                }

                // É um QR code de balcão, carregar cardápio sem mesa
                $table = null;
                $isCounter = true;

                // Marcar na sessão que é pedido de balcão
                $request->session()->put('counter_order_store_' . $store->id, true);
                $request->session()->put('counter_qr_code', $qrCode);
            }
        } else {
            // Se não tiver QR code, pega a loja do usuário logado (preview do cardápio)
            $store = auth()->user()->store;
            $table = null;
            $isCounter = false;
        }

        // Carrega as categorias com seus produtos
        $categories = $store->categories()
            ->with(['products' => function ($query) {
                $query->where('active', true)
                    ->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        return view('menu.show', compact('store', 'categories', 'table', 'isCounter'));
    }

    public function preview()
    {
        $store = auth()->user()->store;
        $table = null;
        $isCounter = false;

        // Carrega as categorias com seus produtos
        $categories = $store->categories()
            ->with(['products' => function ($query) {
                $query->where('active', true)
                    ->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        return view('menu.show', compact('store', 'categories', 'table', 'isCounter'));
    }

    public function createPassword(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'password' => 'string|size:4|regex:/^[0-9]{4}$/',
            'name' => 'required|string|max:255',
        ]);

        $table = Table::where('qr_code', $request->qr_code)->firstOrFail();

        // Verifica se a mesa já tem senha
        if ($table->password) {
            return response()->json([
                'success' => false,
                'message' => 'Esta mesa já possui uma senha definida.'
            ], 400);
        }


        if (!empty($request->password)) {
            // Define a senha
            $table->update([
                'password' => $request->password,
                'occupied' => true,
                'occupied_at' => now()
            ]);
        }

        // Cria o primeiro participante (owner)
        $participant = TableParticipant::create([
            'table_id' => $table->id,
            'name' => $request->name,
            'is_owner' => true,
        ]);

        // Autentica na sessão
        $sessionKey = 'table_' . $table->id . '_authenticated';
        $request->session()->put($sessionKey, true);
        $request->session()->put('table_' . $table->id . '_participant_id', $participant->id);

        return response()->json([
            'success' => true,
            'message' => 'Senha criada com sucesso!'
        ]);
    }

    public function validatePassword(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'password' => 'required|string|size:4',
        ]);

        $table = Table::where('qr_code', $request->qr_code)->firstOrFail();

        // Verifica se a senha está correta
        if ($table->password !== $request->password) {
            return response()->json([
                'success' => false,
                'message' => 'Senha incorreta.'
            ], 401);
        }

        // Autentica temporariamente na sessão (aguardando nome)
        $sessionKey = 'table_' . $table->id . '_password_validated';
        $request->session()->put($sessionKey, true);

        return response()->json([
            'success' => true,
            'message' => 'Senha validada!'
        ]);
    }

    public function addParticipant(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        $table = Table::where('qr_code', $request->qr_code)->firstOrFail();

        // Verifica se a senha foi validada
        $passwordValidatedKey = 'table_' . $table->id . '_password_validated';
        if (!$request->session()->get($passwordValidatedKey, false)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha não validada.'
            ], 401);
        }

        // Cria o participante
        $participant = TableParticipant::create([
            'table_id' => $table->id,
            'name' => $request->name,
            'is_owner' => false,
        ]);

        // Autentica na sessão
        $sessionKey = 'table_' . $table->id . '_authenticated';
        $request->session()->put($sessionKey, true);
        $request->session()->put('table_' . $table->id . '_participant_id', $participant->id);
        $request->session()->forget($passwordValidatedKey);

        return response()->json([
            'success' => true,
            'message' => 'Bem-vindo à mesa!'
        ]);
    }

    public function getParticipants(Request $request, string $qrCode)
    {
        $table = Table::where('qr_code', $qrCode)
            ->with('participants')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'participants' => $table->participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'is_owner' => $participant->is_owner,
                    'joined_at' => $participant->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    public function checkTableStatus(Request $request, string $qrCode)
    {
        $table = Table::where('qr_code', $qrCode)->firstOrFail();

        $hasPassword = !empty($table->password);
        $sessionKey = 'table_' . $table->id . '_authenticated';
        $participantIdKey = 'table_' . $table->id . '_participant_id';
        $isAuthenticated = $request->session()->get($sessionKey, false);
        $participantId = $request->session()->get($participantIdKey);

        // Verificar se o participante ainda existe (pode ter sido removido ao desocupar a mesa)
        if ($isAuthenticated && $participantId) {
            $participantExists = \App\Models\TableParticipant::where('id', $participantId)
                ->where('table_id', $table->id)
                ->exists();

            // Se o participante não existe mais, invalidar a sessão
            if (!$participantExists) {
                $request->session()->forget($sessionKey);
                $request->session()->forget($participantIdKey);
                $isAuthenticated = false;
            }
        }

        return response()->json([
            'success' => true,
            'has_password' => $hasPassword,
            'is_authenticated' => $isAuthenticated,
        ]);
    }
}
