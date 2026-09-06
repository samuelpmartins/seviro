<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Events\DemoRequestApproved;

class DemoRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function index()
    {
        $requests = DemoRequest::orderby('created_at', 'desc')->paginate(15);
        return view('admin.demo-requests.index', compact('requests'));
    }

    public function show(DemoRequest $demoRequest)
    {
        return view('admin.demo_requests.show', compact('demoRequest'));
    }

    public function createDemoRequest(Request $request)
    {
        $request->merge([
            'document' => preg_replace('/\D/', '', $request->document),
            'phone' => preg_replace('/\D/', '', $request->phone),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:demo_requests,email',
            'phone' => 'nullable|string|max:20',
            'document' => 'required|string|max:20|unique:demo_requests,document',
        ]);

        try {
            DemoRequest::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'document' => $request->document,
                'status' => 'created',
            ]);

            return view('welcome', ['success' => true, 'message' => 'Solicitação de demonstração criada com sucesso.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao criar a solicitação: ' . $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function createUser(Request $request, DemoRequest $demoRequest)
    {
        if ($demoRequest->isCreated()) {
            return redirect()->json([
                'success' => false,
                'message' => 'Apenas solicitações com status "Em validação" podem ser convertidas em usuário.'
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $user = $demoRequest->user()->first();

        if ($user) {
            return redirect()->json([
                'success' => false,
                'message' => 'Esta solicitação já está associada a um usuário.'
            ]);
        }

        try {
            $tempPassword = Str::random(12);

            $user = User::create([
                'name' => $demoRequest->name,
                'email' => $demoRequest->email,
                'password' => $tempPassword,
                'first_access' => true,
            ]);

            $storeRole = Role::firstOrCreate(['name' => 'store', 'guard_name' => 'web']);
            $user->syncRoles([$storeRole]);

            $store = Store::create([
                'name' => $demoRequest->name,
                'document' => $demoRequest->document,
                'phone' => $demoRequest->phone ?? '',
                'address' => '',
                'user_id' => $user->id,
            ]);

            $user->update(['store_id' => $store->id]);

            $demoRequest->update([
                'status' => 'approved',
                'user_id' => $user->id,
            ]);

            try {
                event(new DemoRequestApproved($demoRequest, $tempPassword));
            } catch (\Exception $error) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário criado, mas ocorreu um erro ao enviar o e-mail: ' . $error->getMessage(),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado e e-mail disparado com sucesso.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao criar o usuário: ' . $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function updatePending(DemoRequest $demoRequest)
    {
        if ($demoRequest->isPending()) {
            return response()->json(['success' => false, 'message' => 'A solicitação já está com status "Em validação".']);
        }

        $demoRequest->update(['status' => 'pending']);

        return response()->json(['success' => true, 'message' => 'Status atualizado para "Em validação" com sucesso.']);
    }

    public function resendAccessEmail(DemoRequest $demoRequest)
    {
        if (!$demoRequest->isApproved() || !$demoRequest->user) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitação ainda não possui um usuário aprovado.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $tempPassword = Str::random(12);
            $demoRequest->user->update([
                'password' => $tempPassword,
                'first_access' => true,
            ]);

            event(new DemoRequestApproved($demoRequest, $tempPassword));

            return response()->json([
                'success' => true,
                'message' => 'Uma nova senha temporária foi gerada e o e-mail foi reenviado.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível reenviar o e-mail. Verifique o SMTP e tente novamente.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateStatus(Request $request, DemoRequest $demoRequest)
    {
        $request->validate([
            'status' => 'required/in:created,pending'
        ]);

        $demoRequest->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status atualizado com sucesso.']);
    }

    public function destroy(DemoRequest $demoRequest)
    {
        $demoRequest->delete();

        return response()->json(['success' => true, 'message' => 'Solicitação excluída com sucesso.']);
    }
}
