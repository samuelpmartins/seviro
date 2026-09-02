<?php

namespace App\Http\Controllers;

use App\Enums\TableAssignmentType;
use App\Enums\TableServiceStatus;
use App\Models\Store;
use App\Models\Table;
use App\Models\TableParticipant;
use App\Models\TableParticipantPin;
use App\Models\TableUser;
use App\Models\User;
use Illuminate\Http\Request;

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
                $table->load('activeTableUser');

                // Verifica se o usuário está autenticado na mesa via session
                $sessionKey = 'table_' . $table->id . '_authenticated';
                $participantIdKey = 'table_' . $table->id . '_participant_id';
                $isAuthenticated = $request->session()->get($sessionKey, false);
                $participantId = $request->session()->get($participantIdKey);

                // Se o participante foi removido, invalidar a sessão
                if ($isAuthenticated && $participantId) {
                    $participantExists = \App\Models\TableParticipant::where('id', $participantId)
                        ->where('table_id', $table->id)
                        ->exists();

                    if (!$participantExists) {
                        $request->session()->forget($sessionKey);
                        $request->session()->forget($participantIdKey);
                        $isAuthenticated = false;
                    }
                }

                $hasParticipants = $table->participants()->exists();
                $pinValid = false;

                if ($hasParticipants && !$table->password) {
                    $owner = TableParticipant::where('table_id', $table->id)->where('is_owner', true)->first();
                    if ($owner) {
                        $activePin = TableParticipantPin::where('table_participant_id', $owner->id)
                            ->where('status', 'active')
                            ->latest()
                            ->first();

                        if ($activePin && $activePin->next_validate && now()->lessThanOrEqualTo($activePin->next_validate)) {
                            $pinValid = true;
                        }
                    }

                    if (!$pinValid) {
                        $request->session()->forget($sessionKey);
                        $request->session()->forget($participantIdKey);
                        $isAuthenticated = false;
                    }
                }

                // Se não estiver autenticado e não houver PIN válido, bloquear o acesso ao cardápio da mesa
                // para forçar a validação por PIN/senha antes de mostrar os produtos.
                if (!$isAuthenticated && !$pinValid) {
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
                    ->with('additionalIngredients')
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
                    ->with('additionalIngredients')
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
            //'password' => 'string|size:4|regex:/^[0-9]{4}$/',
            'name' => 'required|string|max:255',
        ]);

        $table = Table::where('qr_code', $request->qr_code)->firstOrFail();

        // Cria o primeiro participante (owner)
        $participant = TableParticipant::create([
            'table_id' => $table->id,
            'name' => $request->name,
            'is_owner' => true,
        ]);

        $pin = $this->generateParticipantPin();
        TableParticipantPin::create([
            'table_participant_id' => $participant->id,
            'pin' => $pin,
            'status' => 'active',
            'next_validate' => now()->addSeconds(10),
        ]);

        $table->update([
            'occupied' => true,
            'occupied_at' => now()
        ]);

        $this->assignAvailableUser($table);

        // Autentica na sessão
        $sessionKey = 'table_' . $table->id . '_authenticated';
        $request->session()->put($sessionKey, true);
        $request->session()->put('table_' . $table->id . '_participant_id', $participant->id);
        $request->session()->put('table_' . $table->id . '_access_pin', $pin);

        return response()->json([
            'success' => true,
            'message' => 'Pin de identificação da mesa',
            'pin' => $pin,
        ]);
    }

    public function validatePin(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        $table = Table::where('qr_code', $request->qr_code)->firstOrFail();

        // Preferência: validar somente contra o PIN do proprietário (owner)
        $owner = TableParticipant::where('table_id', $table->id)->where('is_owner', true)->first();

        if ($owner) {
            $pinRecord = TableParticipantPin::where('table_participant_id', $owner->id)
                ->where('pin', $request->pin)
                ->where('status', 'active')
                ->latest()
                ->first();
        } else {
            // Fallback: aceitar PIN de qualquer participante da mesa (compatibilidade)
            $pinRecord = TableParticipantPin::where('pin', $request->pin)
                ->where('status', 'active')
                ->whereHas('participant', function ($query) use ($table) {
                    $query->where('table_id', $table->id);
                })
                ->latest()
                ->first();
        }

        if (!$pinRecord) {
            return response()->json([
                'success' => false,
                'message' => 'PIN incorreto.'
            ], 401);
        }

        $sessionKey = 'table_' . $table->id . '_pin_validated';
        $request->session()->put($sessionKey, true);

        $pinRecord->update(['next_validate' => now()->addSeconds(10)]);
        $request->session()->forget('table_' . $table->id . '_authenticated');
        $request->session()->forget('table_' . $table->id . '_participant_id');

        return response()->json([
            'success' => true,
            'message' => 'PIN validado!'
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

    private function generateParticipantPin(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function assignAvailableUser(Table $table): void
    {
        $user = User::role('waiter')
            ->where('store_id', $table->store_id)
            ->where('is_attending', true)
            ->withCount([
                'tableUsers as active_table_count' => fn($query) => $query
                    ->where('service_status', TableServiceStatus::Active->value),
            ])
            ->withMax('tableUsers as last_table_assignment_at', 'created_at')
            ->orderBy('active_table_count')
            ->orderBy('last_table_assignment_at')
            ->orderBy('id')
            ->first();

        if (!$user) {
            return;
        }

        TableUser::create([
            'store_id' => $table->store_id,
            'table_id' => $table->id,
            'user_id' => $user->id,
            'service_status' => TableServiceStatus::Active,
            'assignment_type' => TableAssignmentType::Automatic,
        ]);
    }

    public function addParticipant(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        $table = Table::where('qr_code', $request->qr_code)->firstOrFail();

        // Verifica se a senha foi validada, apenas quando a mesa tem senha.
        $passwordValidatedKey = 'table_' . $table->id . '_password_validated';
        $pinValidatedKey = 'table_' . $table->id . '_pin_validated';

        if ($table->password && !$request->session()->get($passwordValidatedKey, false)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha não validada.'
            ], 401);
        }

        if (!$table->password && !$request->session()->get($pinValidatedKey, false)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN não validado.'
            ], 401);
        }

        // Verifica se já existe um participante com o mesmo nome e sem DeletionDate (soft-deleted flag)
        $participant = TableParticipant::where('table_id', $table->id)
            ->where('name', $request->name)
            ->whereNull('DeletionDate')
            ->first();

        // Se não existir, cria um novo participante
        if (!$participant) {
            $participant = TableParticipant::create([
                'table_id' => $table->id,
                'name' => $request->name,
                'is_owner' => false,
            ]);
        }

        // Autentica na sessão
        $sessionKey = 'table_' . $table->id . '_authenticated';
        $request->session()->put($sessionKey, true);
        $request->session()->put('table_' . $table->id . '_participant_id', $participant->id);
        $request->session()->forget($passwordValidatedKey);
        $request->session()->forget($pinValidatedKey);

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

        $hasParticipants = \App\Models\TableParticipant::where('table_id', $table->id)->exists();
        $participantName = null;
        $pinValid = false;

        if ($hasParticipants && !$hasPassword) {
            $owner = TableParticipant::where('table_id', $table->id)->where('is_owner', true)->first();
            if ($owner) {
                $activePin = TableParticipantPin::where('table_participant_id', $owner->id)
                    ->where('status', 'active')
                    ->whereNull('DeletionDate')
                    ->latest()
                    ->first();

                if ($activePin && $activePin->next_validate && now()->lessThanOrEqualTo($activePin->next_validate)) {
                    $pinValid = true;
                }
            }
        }

        $pinValidatedInSession = $request->session()->get('table_' . $table->id . '_pin_validated', false);
        $requiresPinValidation = !$hasPassword && $hasParticipants && !$pinValid && !$pinValidatedInSession;
        $requiresParticipantName = $pinValidatedInSession && !$isAuthenticated;

        if ($isAuthenticated && $participantId) {
            $participantName = \App\Models\TableParticipant::where('id', $participantId)
                ->value('name') ?? $participantName;
        }

        return response()->json([
            'success' => true,
            'has_password' => $hasPassword,
            'has_participants' => $hasParticipants,
            'is_authenticated' => $isAuthenticated,
            'is_pin_validated' => $pinValid,
            'requires_pin_validation' => $requiresPinValidation,
            'requires_participant_name' => $requiresParticipantName,
            'participant_name' => $participantName,
        ]);
    }

    /**
     * Request a new PIN for the table owner (creates a new PIN linked to owner participant)
     */
    public function requestNewPin(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $table = Table::where('qr_code', $request->qr_code)->firstOrFail();

        // Find an owner participant if exists
        $owner = TableParticipant::where('table_id', $table->id)->where('is_owner', true)->first();
        if (!$owner) {
            // fallback to first participant
            $owner = TableParticipant::where('table_id', $table->id)->first();
        }

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum participante encontrado para gerar PIN.'
            ], 400);
        }

        $pin = $this->generateParticipantPin();
        TableParticipantPin::create([
            'table_participant_id' => $owner->id,
            'pin' => $pin,
            'status' => 'active',
            'next_validate' => now()->addSeconds(10),
        ]);

        $request->session()->put('table_' . $table->id . '_access_pin', $pin);

        return response()->json([
            'success' => true,
            'message' => 'Novo PIN gerado.',
            'pin' => $pin,
        ]);
    }

    /**
     * Chama o garçom atribuído à mesa (somente mesas com TableUser ativo).
     */
    public function callWaiter(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $table = Table::where('qr_code', $request->qr_code)
            ->with('activeTableUser.user.pushSubscriptions')
            ->firstOrFail();

        $assignment = $table->activeTableUser;

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum garçom atribuído a esta mesa no momento.'
            ], 404);
        }

        // Evita spam: limita uma chamada a cada 20 segundos por mesa.
        $throttleKey = 'waiter-call-table-' . $table->id;
        if (\Illuminate\Support\Facades\Cache::has($throttleKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Aguarde alguns segundos antes de chamar novamente.'
            ], 429);
        }
        \Illuminate\Support\Facades\Cache::put($throttleKey, true, 20);

        $participantId = $request->session()->get('table_' . $table->id . '_participant_id');
        $participantName = $participantId
            ? (TableParticipant::find($participantId)->name ?? 'Cliente')
            : 'Cliente';

        try {
            $assignment->user->notify(new \App\Notifications\WaiterCalled($table, $participantName));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Falha ao notificar chamada de garçom', [
                'table_id' => $table->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Garçom chamado com sucesso!',
        ]);
    }
}
