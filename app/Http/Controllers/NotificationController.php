<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\DeviceToken;
use App\Services\PushService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Lista notificações não lidas do usuário autenticado
     */
    public function unread(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $notifications = $user->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'unknown',
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Envia instrução de impressão para o Agent com os dados do pedido
     */
    public function printOrder(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'agent_url' => 'nullable|url',
            'agent_model' => 'nullable|string',
            'printer_address' => 'nullable|string',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        /** @var Order $order */
        $order = Order::with(['items.product', 'table', 'participant' => function ($query) {
            $query->withTrashed();
        }, 'user'])->findOrFail($data['order_id']);

        if ($user->store_id !== $order->store_id) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado'
            ], 403);
        }

        $agentUrl = $data['agent_url'] ?? config('app.printer_agent_base_url');
        if (!$agentUrl) {
            return response()->json([
                'success' => false,
                'message' => 'URL do Agent de impressão não configurada.'
            ], 500);
        }

        $tableName = $order->table ? 'Mesa ' . $order->table->number : 'Balcão';
        $participantName = $order->participant?->name ?? $order->user?->name ?? 'Sem participante';
        $orderLabel = sprintf('%s - %s', $tableName, $participantName);
        // avoid printing to output (which breaks JSON responses) — log instead
        Log::debug('printOrder prepared label', ['order_id' => $order->id, 'label' => $orderLabel]);

        $items = $order->items->map(function ($item) {
            $observations = [];
            if ($item->notes) {
                $observations = array_filter(array_map('trim', preg_split('/\r?\n|;|\\|/', $item->notes)));
                $observations = array_map([$this, 'normalizeText'], $observations);
            }

            $description = $item->product->name ?? $item->name ?? 'Item sem descrição';
            $description = $this->normalizeText($description);

            $result = [
                'Quantity' => $item->quantity ?? 1,
                'Description' => $description,
            ];

            if (!empty($observations)) {
                $result['Observations'] = array_values($observations);
            }

            return $result;
        })->all();

        $printOrder = [
            'OrderNumber' => $orderLabel,
            'CreatedAt' => $order->created_at?->toIso8601String() ?? now()->toIso8601String(),
            'Items' => $items,
        ];

        $printRequest = [
            'PrinterAddress' => $data['printer_address'],
            'Copies' => 1,
            'CutPaper' => true,
            'Content' => json_encode($printOrder, JSON_UNESCAPED_UNICODE),
        ];

        try {
            $response = Http::timeout(15)->post(rtrim($agentUrl, '/') . '/print', $printRequest);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar a impressão para o Agent.',
                    'details' => $response->body()
                ], $response->status() ?: 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Impressão enviada com sucesso.',
                'agent_response' => $response->json()
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar a impressão: ' . $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Marca uma notificação como lida
     */
    private function normalizeText(string $text): string
    {
        // Remove acentuação e caracteres especiais, preservando letras, números e espaços.
        if (class_exists('\Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_D);
            $text = preg_replace('/\p{M}/u', '', $text);
        }

        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notificação não encontrada'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notificação marcada como lida'
        ]);
    }


    /**
     * Lista notificações não lidas para clientes não autenticados
     * Baseado nos pedidos feitos na sessão atual
     */
    public function clientUnread(Request $request)
    {
        // Buscar pedidos da sessão atual
        $orderIds = $this->getClientOrderIdsFromSession($request);

        if (empty($orderIds)) {
            return response()->json([
                'success' => true,
                'notifications' => [],
                'count' => 0,
            ]);
        }

        // Buscar notificações relacionadas a esses pedidos
        // Incluindo notificações de guests (notifiable_type = Order)
        $notifications = DB::table('notifications')
            ->where(function ($query) use ($orderIds) {
                // Notificações de usuários autenticados
                $query->where(function ($q) use ($orderIds) {
                    $q->where('notifiable_type', 'App\\Models\\User')
                        ->where(function ($subQ) use ($orderIds) {
                            foreach ($orderIds as $orderId) {
                                $subQ->orWhere('data', 'like', '%"order_id":' . $orderId . '%');
                            }
                        });
                })
                    // OU notificações de guests (associadas diretamente ao pedido)
                    ->orWhere(function ($q) use ($orderIds) {
                        $q->where('notifiable_type', 'App\\Models\\Order')
                            ->whereIn('notifiable_id', $orderIds);
                    });
            })
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                $data = json_decode($notification->data, true);
                return [
                    'id' => $notification->id,
                    'type' => $data['type'] ?? 'unknown',
                    'data' => $data,
                    'created_at' => Carbon::parse($notification->created_at)->toIso8601String(),
                ];
            })
            ->filter(function ($notification) {
                // Filtrar apenas notificações do tipo order_ready_client
                return $notification['type'] === 'order_ready_client';
            })
            ->values();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Marca notificação como lida para cliente não autenticado
     */
    public function clientMarkAsRead(Request $request, $id)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notificação não encontrada'
            ], 404);
        }

        DB::table('notifications')
            ->where('id', $id)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notificação marcada como lida'
        ]);
    }

    /**
     * Obtém IDs dos pedidos do cliente baseado na sessão
     */
    private function getClientOrderIdsFromSession(Request $request)
    {
        $orderIds = [];

        // 1. Buscar pedidos armazenados diretamente na sessão (mais recente)
        $sessionOrderIds = $request->session()->get('client_order_ids', []);
        if (!empty($sessionOrderIds)) {
            $orderIds = array_merge($orderIds, $sessionOrderIds);
        }

        // 2. Buscar pedidos do usuário autenticado (se houver)
        if (auth()->check()) {
            $userOrders = Order::where('user_id', auth()->id())->pluck('id')->toArray();
            $orderIds = array_merge($orderIds, $userOrders);
        }

        // 3. Buscar pedidos de todas as mesas que o usuário participou (baseado na sessão)
        $sessionKeys = $request->session()->all();
        foreach ($sessionKeys as $key => $value) {
            if (str_starts_with($key, 'table_') && str_ends_with($key, '_participant_id')) {
                $participantId = $value;
                $participantOrders = Order::where('participant_id', $participantId)->pluck('id')->toArray();
                $orderIds = array_merge($orderIds, $participantOrders);
            }
        }

        return array_unique($orderIds);
    }

    /**
     * Lista TODAS as notificações (lidas e não lidas) do usuário autenticado
     */
    public function all(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'unknown',
                    'data' => $notification->data,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Lista TODAS as notificações para cliente não autenticado
     */
    public function clientAll(Request $request)
    {
        $orderIds = $this->getClientOrderIdsFromSession($request);

        if (empty($orderIds)) {
            return response()->json([
                'success' => true,
                'notifications' => [],
            ]);
        }

        $notifications = DB::table('notifications')
            ->where(function ($query) use ($orderIds) {
                $query->where(function ($q) use ($orderIds) {
                    $q->where('notifiable_type', 'App\\Models\\User')
                        ->where(function ($subQ) use ($orderIds) {
                            foreach ($orderIds as $orderId) {
                                $subQ->orWhere('data', 'like', '%"order_id":' . $orderId . '%');
                            }
                        });
                })
                    ->orWhere(function ($q) use ($orderIds) {
                        $q->where('notifiable_type', 'App\\Models\\Order')
                            ->whereIn('notifiable_id', $orderIds);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($notification) {
                $data = json_decode($notification->data, true);
                return [
                    'id' => $notification->id,
                    'type' => $data['type'] ?? 'unknown',
                    'data' => $data,
                    'read_at' => $notification->read_at ? Carbon::parse($notification->read_at)->toIso8601String() : null,
                    'created_at' => Carbon::parse($notification->created_at)->toIso8601String(),
                ];
            })
            ->filter(function ($notification) {
                return $notification['type'] === 'order_ready_client';
            })
            ->values();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Marca todas as notificações como lidas para usuário autenticado
     */
    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Todas as notificações foram marcadas como lidas',
        ]);
    }

    /**
     * Marca todas as notificações como lidas para cliente não autenticado
     */
    public function clientMarkAllAsRead(Request $request)
    {
        $orderIds = $this->getClientOrderIdsFromSession($request);

        if (empty($orderIds)) {
            return response()->json([
                'success' => true,
                'message' => 'Nenhuma notificação para marcar',
            ]);
        }

        DB::table('notifications')
            ->where(function ($query) use ($orderIds) {
                $query->where(function ($q) use ($orderIds) {
                    $q->where('notifiable_type', 'App\\Models\\User')
                        ->where(function ($subQ) use ($orderIds) {
                            foreach ($orderIds as $orderId) {
                                $subQ->orWhere('data', 'like', '%"order_id":' . $orderId . '%');
                            }
                        });
                })
                    ->orWhere(function ($q) use ($orderIds) {
                        $q->where('notifiable_type', 'App\\Models\\Order')
                            ->whereIn('notifiable_id', $orderIds);
                    });
            })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Todas as notificações foram marcadas como lidas',
        ]);
    }

    /**
     * Registra um token de dispositivo para receber push notifications.
     * - `token` (string) obrigatório
     * - `notifiable_type` (string) opcional
     * - `notifiable_id` (int) opcional
     */
    public function registerDeviceToken(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'notifiable_type' => 'nullable|string',
            'notifiable_id' => 'nullable|integer',
        ]);

        $token = $data['token'];

        $payload = [
            'token' => $token,
            'notifiable_type' => $data['notifiable_type'] ?? null,
            'notifiable_id' => $data['notifiable_id'] ?? null,
        ];

        if (auth()->check()) {
            $payload['user_id'] = auth()->id();
        } else {
            $payload['session_id'] = $request->session()->getId();
        }

        DeviceToken::updateOrCreate(['token' => $token], $payload);

        return response()->json(['success' => true]);
    }

    /**
     * Remove um token de dispositivo.
     * - `token` (string) obrigatório
     */
    public function unregisterDeviceToken(Request $request)
    {
        $data = $request->validate(['token' => 'required|string']);
        DeviceToken::where('token', $data['token'])->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Envía push de teste para tokens vinculados ao usuário autenticado.
     */
    public function sendTestPush(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
        }

        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
        try {
            $res = PushService::sendToTokens($tokens, 'Teste', 'Notificação de teste', ['test' => true]);
            return response()->json(['success' => true, 'result' => $res]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
