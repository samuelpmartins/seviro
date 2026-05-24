<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        
        $notifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'unknown',
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }
    
    /**
     * Marca uma notificação como lida
     */
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
            ->where(function($query) use ($orderIds) {
                // Notificações de usuários autenticados
                $query->where(function($q) use ($orderIds) {
                    $q->where('notifiable_type', 'App\\Models\\User')
                      ->where(function($subQ) use ($orderIds) {
                          foreach ($orderIds as $orderId) {
                              $subQ->orWhere('data', 'like', '%"order_id":' . $orderId . '%');
                          }
                      });
                })
                // OU notificações de guests (associadas diretamente ao pedido)
                ->orWhere(function($q) use ($orderIds) {
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
                    'created_at' => $notification->created_at,
                ];
            })
            ->filter(function($notification) {
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
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at->toISOString(),
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
            ->where(function($query) use ($orderIds) {
                $query->where(function($q) use ($orderIds) {
                    $q->where('notifiable_type', 'App\\Models\\User')
                      ->where(function($subQ) use ($orderIds) {
                          foreach ($orderIds as $orderId) {
                              $subQ->orWhere('data', 'like', '%"order_id":' . $orderId . '%');
                          }
                      });
                })
                ->orWhere(function($q) use ($orderIds) {
                    $q->where('notifiable_type', 'App\\Models\\Order')
                      ->whereIn('notifiable_id', $orderIds);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($notification) {
                $data = json_decode($notification->data, true);
                return [
                    'id' => $notification->id,
                    'type' => $data['type'] ?? 'unknown',
                    'data' => $data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            })
            ->filter(function($notification) {
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
        
        $user->unreadNotifications()->update(['read_at' => now()]);
        
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
            ->where(function($query) use ($orderIds) {
                $query->where(function($q) use ($orderIds) {
                    $q->where('notifiable_type', 'App\\Models\\User')
                      ->where(function($subQ) use ($orderIds) {
                          foreach ($orderIds as $orderId) {
                              $subQ->orWhere('data', 'like', '%"order_id":' . $orderId . '%');
                          }
                      });
                })
                ->orWhere(function($q) use ($orderIds) {
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
}
