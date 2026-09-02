<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Table;
use App\Models\Order;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rota para buscar mesa por QR code
Route::get('/tables/qrcode/{qr_code}', function ($qrCode) {
    $table = Table::where('qr_code', $qrCode)->first();
    if ($table) {
        return response()->json(['table_id' => $table->id]);
    }
    return response()->json(['message' => 'Mesa não encontrada'], 404);
});

// Rota para buscar detalhes do produto
Route::get('/products/{id}', function ($id) {
    $product = \App\Models\Product::with(['additionalIngredients'])->find($id);
    if ($product) {
        $payload = $product->toArray();
        $payload['additional_ingredients'] = $product->additionalIngredients->toArray();
        return response()->json($payload);
    }
    return response()->json(['message' => 'Produto não encontrado'], 404);
});


// Rota para buscar participantes de uma mesa
Route::get('/tables/{id}/participants', function ($id, Request $request) {
    $table = Table::findOrFail($id);

    $participants = $table->participants()->get();

    return response()->json(['participants' => $participants]);
});

// Rota para buscar pedidos de uma mesa pelo QR code
Route::get('/table-orders/{qr_code}', function ($qrCode) {
    $table = Table::where('qr_code', $qrCode)->first();

    if (!$table) {
        return response()->json(['message' => 'Mesa não encontrada'], 404);
    }

    // Buscar apenas pedidos dos participantes ativos (sessão atual)
    $activeParticipantIds = $table->participants()->pluck('id')->toArray();

    // Se não há participantes ativos, retornar array vazio
    if (empty($activeParticipantIds)) {
        return response()->json(['orders' => []]);
    }

    $orders = Order::where('table_id', $table->id)
        ->whereIn('participant_id', $activeParticipantIds)
        ->with(['items'])
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json(['orders' => $orders]);
});

// Rotas para autenticação de mesa (com suporte a sessão)
Route::middleware(['web'])->group(function () {
    Route::post('/table/create-password', [MenuController::class, 'createPassword']);
    Route::post('/table/validate-password', [MenuController::class, 'validatePassword']);
    Route::post('/table/request-pin', [MenuController::class, 'requestNewPin']);
    Route::post('/table/validate-pin', [MenuController::class, 'validatePin']);
    Route::post('/table/add-participant', [MenuController::class, 'addParticipant']);
    Route::get('/table/{qrCode}/participants', [MenuController::class, 'getParticipants']);
    Route::get('/table/{qrCode}/status', [MenuController::class, 'checkTableStatus']);
    Route::post('/table/call-waiter', [MenuController::class, 'callWaiter']);

    // Rota para criar pedido (precisa de sessão para capturar participant_id)
    Route::post('/orders', function (Request $request) {
        try {
            $validated = $request->validate([
                'store_id' => ['required', \Illuminate\Validation\Rule::exists('stores', 'id')->whereNull('DeletionDate')],
                'table_id' => ['nullable', \Illuminate\Validation\Rule::exists('tables', 'id')->whereNull('DeletionDate')],
                'items' => 'required|array',
                'items.*.product_id' => ['required', \Illuminate\Validation\Rule::exists('products', 'id')->whereNull('DeletionDate')],
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.notes' => 'nullable|string',
                'items.*.unit_price' => 'nullable|numeric',
                'items.*.selected_ingredients' => 'nullable|array',
                'items.*.selected_ingredients.*.id' => ['required_with:items.*.selected_ingredients', \Illuminate\Validation\Rule::exists('product_ingredients', 'id')->whereNull('DeletionDate')],
                'items.*.selected_ingredients.*.selected_amount' => 'required_with:items.*.selected_ingredients|integer|min:0',
                'notes' => 'nullable|string',
            ]);

            // Obter o participant_id da sessão
            $participantId = null;
            if ($validated['table_id']) {
                $participantId = $request->session()->get('table_' . $validated['table_id'] . '_participant_id');
            }

            // Obter a store para verificar se tem garçons
            $store = \App\Models\Store::findOrFail($validated['store_id']);

            // Verificar se o pedido contém APENAS itens rápidos
            $onlyQuickItems = true;
            $allProducts = [];
            foreach ($validated['items'] as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                $allProducts[] = ['item' => $item, 'product' => $product];
                if (!$product->is_quick_item) {
                    $onlyQuickItems = false;
                }
            }

            // Definir status inicial
            if ($onlyQuickItems) {
                // Itens rápidos também aguardam o início da produção quando há garçons.
                $initialStatus = $store->hasWaiters() ? 'Aguardando produção' : 'Finalizado';
            } else {
                // Todo novo pedido aguarda o início da produção quando há garçons.
                $initialStatus = $store->hasWaiters() ? 'Aguardando produção' : 'Aguardando pagamento';
            }

            // Criar o pedido com retry para evitar colisões no order_number
            $orderAttributes = [
                'store_id' => $validated['store_id'],
                'table_id' => $validated['table_id'] ?? null,
                'participant_id' => $participantId,
                'status' => $initialStatus,
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
            ];
            // Calcular o total usando ingredientes selecionados (segurança: servidor recalcula preço)
            $total = 0;
            foreach ($allProducts as $data) {
                $item = $data['item'];
                $product = $data['product'];

                $unitPrice = floatval($product->price);

                // aplicar ingredientes selecionados (se houver)
                if (!empty($item['selected_ingredients']) && is_array($item['selected_ingredients'])) {
                    foreach ($item['selected_ingredients'] as $sel) {
                        $ing = \App\Models\ProductIngredient::find($sel['id']);
                        if ($ing && $ing->product_id == $product->id) {
                            $base = intval($ing->amount_item);
                            $selected = intval($sel['selected_amount']);
                            $diff = $selected - $base;
                            if ($diff > 0) {
                                $unitPrice += $diff * floatval($ing->additional_price);
                            }
                        }
                    }
                }

                $total += $unitPrice * intval($item['quantity']);
                // store computed unit price for later item creation
                $data['computed_unit_price'] = $unitPrice;
            }
            $orderAttributes['total'] = $total;

            $order = Order::createWithRetry($orderAttributes, function ($order) use ($allProducts, $request) {
                // Adicionar os itens do pedido (salvando preço calculado e ingredientes selecionados no campo notes como JSON)
                foreach ($allProducts as $data) {
                    $item = $data['item'];
                    $unitPrice = isset($data['computed_unit_price']) ? $data['computed_unit_price'] : floatval($data['product']->price);

                    $notesPayload = [
                        'notes' => $item['notes'] ?? null,
                        'selected_ingredients' => collect($item['selected_ingredients'] ?? [])
                            ->map(fn($ingredient) => [
                                'id' => $ingredient['id'],
                                'selected_amount' => $ingredient['selected_amount'],
                            ])->values()->all(),
                    ];

                    $order->items()->create([
                        'product_id' => $data['product']->id,
                        'quantity' => $item['quantity'],
                        'price' => $unitPrice,
                        'notes' => json_encode($notesPayload, JSON_UNESCAPED_UNICODE),
                    ]);
                }

                // Armazenar ID do pedido na sessão para rastreamento de notificações
                $sessionOrderIds = $request->session()->get('client_order_ids', []);
                $sessionOrderIds[] = $order->id;
                $request->session()->put('client_order_ids', array_unique($sessionOrderIds));

                // Disparar evento de pedido criado (para notificações)
                event(new \App\Events\OrderCreated($order));
            });

            return response()->json(['success' => true, 'order' => $order->load('items')], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    // Push notifications endpoints
    Route::post('/push/register', [NotificationController::class, 'registerDeviceToken']);
    Route::post('/push/unregister', [NotificationController::class, 'unregisterDeviceToken']);
    Route::post('/push/test', [NotificationController::class, 'sendTestPush']);

    // Web Push (VAPID) - exige usuário autenticado via sessão web
    Route::middleware('auth')->group(function () {
        Route::post('/push/webpush-subscribe', [NotificationController::class, 'subscribeWebPush']);
        Route::post('/push/webpush-unsubscribe', [NotificationController::class, 'unsubscribeWebPush']);
    });

    // Rotas de pedidos (com suporte a sessão)
    Route::get('/tables/{id}/orders', function ($id, Request $request) {
        $table = Table::findOrFail($id);

        // Obter o participant_id do usuário atual da sessão
        $currentParticipantId = $request->session()->get('table_' . $id . '_participant_id');

        // Buscar todos os participant_ids ativos (participantes que ainda existem na mesa)
        $activeParticipantIds = $table->participants()->pluck('id')->toArray();

        // Se não há participantes ativos, retornar array vazio
        if (empty($activeParticipantIds)) {
            return response()->json(['orders' => []]);
        }

        // Filtrar apenas pedidos de participantes ativos (sessão atual)
        $orders = Order::where('table_id', $id)
            ->whereIn('participant_id', $activeParticipantIds)
            ->with(['items.product', 'participant'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total' => $order->total,
                    'created_at' => $order->created_at,
                    'participant_name' => $order->participant ? $order->participant->name : 'Desconhecido',
                    'items' => $order->items->map(function ($item) {
                        $rawNotes = $item->notes;
                        $decodedNotes = $rawNotes;
                        $selected = [];
                        if (!empty($rawNotes)) {
                            $tmp = json_decode($rawNotes, true);
                            if (is_array($tmp) && array_key_exists('notes', $tmp)) {
                                $decodedNotes = $tmp['notes'];
                                $selected = $tmp['selected_ingredients'] ?? [];
                            }
                        }

                        return [
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'notes' => $decodedNotes,
                            'selected_ingredients' => $selected,
                        ];
                    }),
                ];
            });

        return response()->json(['orders' => $orders]);
    });

    // Rota para buscar pedidos do balcão (por sessão)
    Route::get('/counter/{qrCode}/orders', function ($qrCode, Request $request) {
        // Verificar se é um QR code de balcão válido
        $store = \App\Models\Store::where('counter_qr_code', $qrCode)->first();

        if (!$store) {
            return response()->json(['message' => 'QR Code de balcão não encontrado'], 404);
        }

        // Buscar pedidos da sessão atual (baseado em IDs armazenados na sessão)
        $sessionOrderIds = $request->session()->get('client_order_ids', []);

        if (empty($sessionOrderIds)) {
            return response()->json(['orders' => []]);
        }

        // Buscar pedidos do balcão (sem table_id) desta loja que pertencem à sessão
        $orders = Order::whereNull('table_id')
            ->where('store_id', $store->id)
            ->whereIn('id', $sessionOrderIds)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total' => $order->total,
                    'created_at' => $order->created_at,
                    'participant_name' => null,
                    'items' => $order->items->map(function ($item) {
                        $rawNotes = $item->notes;
                        $decodedNotes = null;
                        $selected = [];
                        if (!empty($rawNotes)) {
                            $tmp = json_decode($rawNotes, true);
                            if (is_array($tmp)) {
                                $decodedNotes = $tmp['notes'] ?? null;
                                $selected = $tmp['selected_ingredients'] ?? [];
                            }
                        }

                        return [
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'notes' => $decodedNotes,
                            'selected_ingredients' => $selected,
                        ];
                    }),
                ];
            });

        return response()->json(['orders' => $orders]);
    });

    Route::get('/tables/{table}/unpaid-orders', function ($tableId, Request $request) {
        $table = Table::findOrFail($tableId);

        // Buscar todos os pedidos pendentes da mesa, incluindo aqueles sem participant_id.
        $orders = Order::where('table_id', $table->id)
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->with(['participant'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['orders' => $orders]);
    });

    // Rotas de pagamento (com suporte a sessão)
    Route::get('/payment/{qrCode}/orders', [PaymentController::class, 'getOrdersForPayment']);
    Route::post('/payment/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payment/get-pix-info', [PaymentController::class, 'getPixInfo']);
    Route::post('/payment/confirm', [PaymentController::class, 'confirmPayment']);
    Route::post('/payment/check-status', [PaymentController::class, 'checkPaymentStatus']);
    Route::post('/payment/request-cash', [PaymentController::class, 'requestCashPayment']);
});

// Webhook do Stripe (sem verificação CSRF)
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Rotas de notificações (requer autenticação)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::get('/notifications/all', [NotificationController::class, 'all']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/print', [NotificationController::class, 'printOrder']);

    // Rota para buscar detalhes de um pedido para usuários autenticados
    Route::get('/orders/{order}', function (Order $order) {
        $user = auth()->user();
        if ($user->store_id !== $order->store_id) {
            return response()->json(['success' => false, 'message' => 'Não autorizado'], 403);
        }

        $orderData = $order->load(['items.product', 'table', 'participant' => function ($query) {
            $query->withTrashed();
        }, 'user']);

        $orderArray = $orderData->toArray();
        $orderArray['items'] = collect($orderArray['items'])->map(function ($item) {
            $payload = json_decode($item['notes'] ?? '', true);
            if (is_array($payload) && array_key_exists('notes', $payload)) {
                $item['notes'] = $payload['notes'];
                $item['selected_ingredients'] = $payload['selected_ingredients'] ?? [];
            } else {
                $item['selected_ingredients'] = [];
            }
            return $item;
        })->values()->all();
        $orderArray['participant_name'] = $orderData->participant?->name;
        $orderArray['table_display'] = $orderData->table ? 'Mesa ' . $orderData->table->number : 'Balcão';

        return response()->json([
            'success' => true,
            'order' => $orderArray
        ], 200);
    });
});
