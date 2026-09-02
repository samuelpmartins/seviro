<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\TableParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Mostra a página de pagamento
     */
    public function showPaymentPage(string $qrCode)
    {
        // Tentar encontrar mesa primeiro
        $table = Table::where('qr_code', $qrCode)->first();

        if ($table) {
            // É uma mesa
            $store = $table->store;
            $isCounter = false;
        } else {
            // Verificar se é QR code de balcão
            $store = \App\Models\Store::where('counter_qr_code', $qrCode)->first();

            if (!$store) {
                abort(404, 'QR Code não encontrado');
            }

            $table = null;
            $isCounter = true;
        }

        return view('payment.show', [
            'table' => $table,
            'qrCode' => $qrCode,
            'hasWaiters' => $store->hasWaiters(),
            'logoClickable' => false,
            'isCounter' => $isCounter,
            'store' => $store
        ]);
    }

    /**
     * Retorna os pedidos disponíveis para pagamento
     * TODOS os usuários podem ver e pagar por TODOS os pedidos não pagos da mesa ou balcão
     */
    public function getOrdersForPayment(Request $request, string $qrCode)
    {
        try {
            // Tentar encontrar mesa primeiro
            $table = Table::where('qr_code', $qrCode)->first();

            if (!$table) {
                // Verificar se é QR code de balcão
                $store = \App\Models\Store::where('counter_qr_code', $qrCode)->first();

                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'QR Code não encontrado'
                    ], 404);
                }

                // É um pedido de balcão
                return $this->getCounterOrdersForPayment($request, $store, $qrCode);
            }

            // Verificar se o usuário está autenticado na mesa
            $sessionKey = 'table_' . $table->id . '_authenticated';
            $isAuthenticated = $request->session()->get($sessionKey, false);

            $participant = null; // Inicializar a variável

            // Buscar todos os participant_ids ativos (participantes que ainda existem na mesa)
            // Isso garante que apenas pedidos da sessão atual sejam mostrados
            $activeParticipantIds = $table->participants()->pluck('id')->toArray();

            // Buscar TODOS os pedidos pendentes da mesa
            // Incluindo pedidos dos participantes ativos E pedidos sem participante (antigos)
            // Restringe à ocupação atual: pedidos de sessões anteriores (antes da mesa ser limpa)
            // não devem ressurgir para os novos ocupantes.
            $ordersQuery = Order::where('table_id', $table->id)
                ->where('payment_status', Order::PAYMENT_STATUS_PENDING);

            if ($table->occupied_at) {
                $ordersQuery->where('created_at', '>=', $table->occupied_at);
            }

            // Se há participantes ativos, incluir seus pedidos E os pedidos sem participante
            if (!empty($activeParticipantIds)) {
                $ordersQuery->where(function ($query) use ($activeParticipantIds) {
                    $query->whereIn('participant_id', $activeParticipantIds)
                        ->orWhereNull('participant_id');
                });
            } else {
                // Se não há participantes, mostrar apenas pedidos sem participante
                $ordersQuery->whereNull('participant_id');
            }

            $orders = $ordersQuery->with(['items.product', 'participant'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Se estiver autenticado, buscar informações do participante
            if ($isAuthenticated) {
                $participantId = $request->session()->get('table_' . $table->id . '_participant_id');
                $participant = TableParticipant::find($participantId);

                if (!$participant) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Participante não encontrado.'
                    ], 404);
                }
            }

            // Agrupar pedidos por participante para exibição
            $ordersByParticipant = $orders->groupBy(function ($order) {
                return $order->participant_id ?? 'sem_participante';
            });

            // OTIMIZAÇÃO: Carregar todos os participantes de uma vez (evita N+1)
            $participantIds = array_filter(
                $ordersByParticipant->keys()->toArray(),
                fn($id) => $id !== 'sem_participante' && is_numeric($id)
            );
            $participants = [];
            if (!empty($participantIds)) {
                $participants = TableParticipant::whereIn('id', $participantIds)
                    ->pluck('name', 'id')
                    ->toArray();
            }

            $formattedOrders = [];
            foreach ($ordersByParticipant as $participantId => $participantOrders) {
                // Usar dados já carregados em vez de fazer nova query
                $participantName = $participantId === 'sem_participante'
                    ? 'Pedidos Gerais'
                    : ($participants[$participantId] ?? 'Participante Removido');

                $formattedOrders[] = [
                    'participant_id' => $participantId,
                    'participant_name' => $participantName,
                    'orders' => $participantOrders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'table_display' => $order->getTableDisplayName(),
                            'total' => $order->total,
                            'status' => $order->status,
                            'payment_status' => $order->payment_status,
                            'created_at' => $order->created_at->format('H:i'),
                            'items' => $order->items->map(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'product_name' => $item->product->name ?? 'Produto',
                                    'quantity' => $item->quantity,
                                    'price' => $item->price,
                                    'subtotal' => $item->price * $item->quantity,
                                ];
                            }),
                        ];
                    }),
                    'subtotal' => $participantOrders->sum('total'),
                ];
            }

            // Preparar resposta baseado na autenticação
            $response = [
                'success' => true,
                'orders' => $formattedOrders,
                'total' => $orders->sum('total'),
            ];

            // Se estiver autenticado, adicionar informações do participante
            if ($isAuthenticated && $participant) {
                $response['is_owner'] = $participant->is_owner;
                $response['current_participant_id'] = $participant->id;
                $response['current_participant_name'] = $participant->name;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar pedidos para pagamento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna os pedidos de balcão disponíveis para pagamento
     */
    private function getCounterOrdersForPayment(Request $request, $store, string $qrCode)
    {
        try {
            // Buscar pedidos da sessão atual
            $sessionOrderIds = $request->session()->get('client_order_ids', []);

            if (empty($sessionOrderIds)) {
                return response()->json([
                    'success' => true,
                    'orders' => [],
                    'total' => 0,
                ]);
            }

            // Buscar pedidos do balcão (sem table_id) desta loja que pertencem à sessão
            $orders = Order::whereNull('table_id')
                ->where('store_id', $store->id)
                ->whereIn('id', $sessionOrderIds)
                ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedOrders = [];
            if ($orders->isNotEmpty()) {
                $formattedOrders[] = [
                    'participant_id' => 'balcao',
                    'participant_name' => 'Meus Pedidos',
                    'orders' => $orders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'table_display' => $order->getTableDisplayName(),
                            'total' => $order->total,
                            'status' => $order->status,
                            'payment_status' => $order->payment_status,
                            'created_at' => $order->created_at->format('H:i'),
                            'items' => $order->items->map(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'product_name' => $item->product->name ?? 'Produto',
                                    'quantity' => $item->quantity,
                                    'price' => $item->price,
                                    'subtotal' => $item->price * $item->quantity,
                                ];
                            }),
                        ];
                    }),
                    'subtotal' => $orders->sum('total'),
                ];
            }

            return response()->json([
                'success' => true,
                'orders' => $formattedOrders,
                'total' => $orders->sum('total'),
                'is_counter' => true,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar pedidos de balcão para pagamento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cria um PaymentIntent do Stripe com métodos de pagamento automáticos (Payment Element)
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $request->validate([
                'qr_code' => 'required|string',
                'order_ids' => 'required|array',
                'order_ids.*' => ['required', 'integer', Rule::exists('orders', 'id')->whereNull('DeletionDate')],
            ]);

            // Tentar encontrar mesa primeiro
            $table = Table::where('qr_code', $request->qr_code)->first();
            $isCounter = false;
            $store = null;
            $participantId = null;

            if ($table) {
                $store = $table->store;

                $orders = Order::whereIn('id', $request->order_ids)
                    ->where('table_id', $table->id)
                    ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                    ->when($table->occupied_at, fn($query) => $query->where('created_at', '>=', $table->occupied_at))
                    ->get();

                $sessionKey = 'table_' . $table->id . '_authenticated';
                if ($request->session()->get($sessionKey, false)) {
                    $participantId = $request->session()->get('table_' . $table->id . '_participant_id');
                }
            } else {
                $store = \App\Models\Store::where('counter_qr_code', $request->qr_code)->first();

                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'QR Code não encontrado.'
                    ], 404);
                }

                $isCounter = true;

                $orders = Order::whereIn('id', $request->order_ids)
                    ->whereNull('table_id')
                    ->where('store_id', $store->id)
                    ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                    ->get();
            }

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum pedido válido selecionado.'
                ], 400);
            }

            $amount = $orders->sum('total');
            $amountInCents = (int) ($amount * 100);

            $paymentIntentData = [
                'amount' => $amountInCents,
                'currency' => 'brl',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'table_id' => $table ? $table->id : null,
                    'store_id' => $store->id,
                    'order_ids' => json_encode($request->order_ids),
                    'participant_id' => $participantId,
                    'is_counter' => $isCounter ? 'true' : 'false',
                ],
            ];

            $paymentIntent = PaymentIntent::create($paymentIntentData);

            $payment = Payment::create([
                'store_id' => $store->id,
                'table_id' => $table ? $table->id : null,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'payment_method' => 'card',
                'amount' => $amount,
                'status' => Payment::STATUS_PENDING,
                'order_ids' => $request->order_ids,
                'paid_by_participant_id' => $participantId,
            ]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar PaymentIntent: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém informações do PIX após criação do PaymentIntent
     */
    public function getPixInfo(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if (isset($paymentIntent->next_action->pix_display_qr_code)) {
                $pixInfo = $paymentIntent->next_action->pix_display_qr_code;

                // Atualizar o registro de pagamento com as informações do PIX
                $payment = Payment::where('stripe_payment_intent_id', $request->payment_intent_id)->first();
                if ($payment) {
                    $payment->update([
                        'pix_qr_code' => $pixInfo->image_url_png ?? null,
                        'pix_code' => $pixInfo->data ?? null,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'qr_code_url' => $pixInfo->image_url_png ?? null,
                    'pix_code' => $pixInfo->data ?? null,
                    'expires_at' => $pixInfo->expires_at ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Informações do PIX não disponíveis.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Erro ao obter informações do PIX: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter informações do PIX.'
            ], 500);
        }
    }

    /**
     * Confirma o pagamento após sucesso do Stripe
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $payment = Payment::where('stripe_payment_intent_id', $request->payment_intent_id)->first();

                if ($payment) {
                    $actualMethod = $this->resolvePaymentMethod($paymentIntent);
                    $payment->update([
                        'status' => Payment::STATUS_SUCCEEDED,
                        'stripe_payment_id' => $paymentIntent->latest_charge ?? null,
                        'payment_method' => $actualMethod,
                    ]);

                    $payment->markOrdersAsPaid();

                    // Verificar se a mesa foi desocupada
                    $table = Table::find($payment->table_id);
                    $tableCleared = $table ? !$table->occupied : false;

                    $response = [
                        'success' => true,
                        'message' => 'Pagamento confirmado com sucesso!',
                        'table_cleared' => $tableCleared,
                    ];

                    if ($tableCleared) {
                        $response['thank_you_message'] = 'Obrigado pela preferência! Volte sempre!';
                    }

                    return response()->json($response);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Pagamento ainda não confirmado.',
                'status' => $paymentIntent->status,
            ], 400);
        } catch (\Exception $e) {
            Log::error('Erro ao confirmar pagamento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar pagamento.'
            ], 500);
        }
    }

    /**
     * Verifica o status de um pagamento PIX
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'payment_id' => ['required', 'integer', Rule::exists('payments', 'id')->whereNull('DeletionDate')],
        ]);

        $payment = Payment::findOrFail($request->payment_id);

        // Se já está com sucesso, retornar
        if ($payment->status === Payment::STATUS_SUCCEEDED) {
            // Verificar se a mesa foi desocupada
            $table = Table::find($payment->table_id);
            $tableCleared = $table ? !$table->occupied : false;

            $response = [
                'success' => true,
                'status' => 'succeeded',
                'message' => 'Pagamento confirmado!',
                'table_cleared' => $tableCleared,
            ];

            if ($tableCleared) {
                $response['thank_you_message'] = 'Obrigado pela preferência! Volte sempre!';
            }

            return response()->json($response);
        }

        // Verificar no Stripe
        try {
            $paymentIntent = PaymentIntent::retrieve($payment->stripe_payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $payment->update([
                    'status' => Payment::STATUS_SUCCEEDED,
                    'stripe_payment_id' => $paymentIntent->latest_charge ?? null,
                ]);
                $payment->markOrdersAsPaid();

                // Verificar se a mesa foi desocupada
                $table = Table::find($payment->table_id);
                $tableCleared = $table ? !$table->occupied : false;

                $response = [
                    'success' => true,
                    'status' => 'succeeded',
                    'message' => 'Pagamento confirmado!',
                    'table_cleared' => $tableCleared,
                ];

                if ($tableCleared) {
                    $response['thank_you_message'] = 'Obrigado pela preferência! Volte sempre!';
                }

                return response()->json($response);
            }

            // Verificar se expirou
            if ($payment->payment_method === Payment::METHOD_PIX && $payment->isPixExpired()) {
                $payment->update(['status' => Payment::STATUS_CANCELED]);
                return response()->json([
                    'success' => false,
                    'status' => 'expired',
                    'message' => 'O PIX expirou. Por favor, tente novamente.',
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $paymentIntent->status,
                'message' => 'Aguardando pagamento...',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do pagamento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status do pagamento.'
            ], 500);
        }
    }

    /**
     * Marca pedidos como pagos em dinheiro (apenas para garçom/loja)
     */
    public function markAsCash(Request $request, Table $table)
    {
        $this->authorize('update', $table);

        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => ['required', 'integer', Rule::exists('orders', 'id')->whereNull('DeletionDate')],
            'cash_received' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)
            ->where('table_id', $table->id)
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhum pedido válido selecionado.');
        }

        $totalAmount = $orders->sum('total');
        $changeGiven = max(0, $request->cash_received - $totalAmount);

        // Criar registro de pagamento
        $payment = Payment::create([
            'store_id' => $table->store_id,
            'table_id' => $table->id,
            'payment_method' => Payment::METHOD_CASH,
            'amount' => $totalAmount,
            'status' => Payment::STATUS_SUCCEEDED,
            'order_ids' => $request->order_ids,
            'marked_by_user_id' => auth()->id(),
            'cash_received' => $request->cash_received,
            'change_given' => $changeGiven,
            'notes' => $request->notes,
        ]);

        // Marcar os pedidos como pagos (isso também verifica se deve desocupar a mesa)
        $payment->markOrdersAsPaid();

        $message = 'Pagamento em dinheiro registrado com sucesso! Troco: R$ ' . number_format($changeGiven, 2, ',', '.');

        // Verificar se a mesa foi desocupada
        $table->refresh();
        if (!$table->occupied) {
            $message .= ' A mesa foi desocupada automaticamente pois todos os pedidos foram pagos.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Solicita pagamento em dinheiro (cliente chama garçom)
     */
    public function requestCashPayment(Request $request)
    {
        try {
            $request->validate([
                'qr_code' => 'required|string',
                'order_ids' => 'required|array',
                'order_ids.*' => ['required', 'integer', Rule::exists('orders', 'id')->whereNull('DeletionDate')],
            ]);

            // Tentar encontrar mesa primeiro
            $table = Table::where('qr_code', $request->qr_code)->first();
            $isCounter = false;
            $store = null;
            $participantId = null;
            $message = 'Solicitação enviada! O garçom virá até sua mesa para receber o pagamento.';

            if ($table) {
                // É uma mesa
                $store = $table->store;

                // Verificar se há autenticação (opcional)
                $sessionKey = 'table_' . $table->id . '_authenticated';
                if ($request->session()->get($sessionKey, false)) {
                    $participantId = $request->session()->get('table_' . $table->id . '_participant_id');
                }

                // Criar um registro de pagamento pendente para dinheiro
                $orders = Order::whereIn('id', $request->order_ids)
                    ->where('table_id', $table->id)
                    ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                    ->get();
            } else {
                // Verificar se é QR code de balcão
                $store = \App\Models\Store::where('counter_qr_code', $request->qr_code)->first();

                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'QR Code não encontrado.'
                    ], 404);
                }

                $isCounter = true;
                $message = 'Solicitação enviada! Dirija-se ao balcão para efetuar o pagamento.';

                // Buscar pedidos do balcão
                $orders = Order::whereIn('id', $request->order_ids)
                    ->whereNull('table_id')
                    ->where('store_id', $store->id)
                    ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                    ->get();
            }

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum pedido válido selecionado.'
                ], 400);
            }

            $totalAmount = $orders->sum('total');

            $payment = Payment::create([
                'store_id' => $store->id,
                'table_id' => $table ? $table->id : null,
                'payment_method' => Payment::METHOD_CASH,
                'amount' => $totalAmount,
                'status' => Payment::STATUS_PENDING,
                'order_ids' => $request->order_ids,
                'paid_by_participant_id' => $participantId,
                'notes' => $isCounter ? 'Pedido de balcão - Aguardando pagamento' : 'Aguardando garçom',
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'payment_id' => $payment->id,
                'total' => $totalAmount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao solicitar pagamento em dinheiro: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar solicitação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook do Stripe para processar eventos
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;
            }

            return response()->json(['status' => 'success']);
        } catch (\UnexpectedValueException $e) {
            Log::error('Webhook Stripe - Payload inválido: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook Stripe - Assinatura inválida: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }

    /**
     * Processa pagamento bem sucedido
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment && $payment->status !== Payment::STATUS_SUCCEEDED) {
            $actualMethod = $this->resolvePaymentMethod($paymentIntent);

            $payment->update([
                'status' => Payment::STATUS_SUCCEEDED,
                'stripe_payment_id' => $paymentIntent->latest_charge ?? null,
                'payment_method' => $actualMethod,
            ]);

            $payment->markOrdersAsPaid();

            $table = Table::find($payment->table_id);
            if ($table && !$table->occupied) {
                Log::info('Mesa ' . $table->number . ' foi desocupada automaticamente após pagamento completo. Payment ID: ' . $payment->id);
            }

            Log::info('Pagamento confirmado via webhook: ' . $payment->id);
        }
    }

    /**
     * Resolve o método de pagamento real usado a partir do PaymentIntent
     */
    private function resolvePaymentMethod($paymentIntent): string
    {
        $pmTypes = $paymentIntent->payment_method_types ?? [];
        if (in_array('pix', $pmTypes) && count($pmTypes) === 1) {
            return Payment::METHOD_PIX;
        }

        try {
            $pmId = $paymentIntent->payment_method;
            if ($pmId) {
                $pm = \Stripe\PaymentMethod::retrieve($pmId);
                if ($pm->type === 'pix') {
                    return Payment::METHOD_PIX;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Não foi possível resolver payment method: ' . $e->getMessage());
        }

        return Payment::METHOD_CARD;
    }

    /**
     * Processa falha de pagamento
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update(['status' => Payment::STATUS_FAILED]);
            Log::info('Pagamento falhou: ' . $payment->id);
        }
    }

    /**
     * Página de retorno após redirect do Stripe (PIX via banco, 3DS, etc.)
     */
    public function paymentComplete(Request $request, string $qrCode)
    {
        $paymentIntentId = $request->query('payment_intent');

        if (!$paymentIntentId) {
            return redirect()->route('payment.show', $qrCode)
                ->with('error', 'Informações de pagamento não encontradas.');
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

            if ($paymentIntent->status === 'succeeded' && $payment) {
                if ($payment->status !== Payment::STATUS_SUCCEEDED) {
                    $actualMethod = $this->resolvePaymentMethod($paymentIntent);
                    $payment->update([
                        'status' => Payment::STATUS_SUCCEEDED,
                        'stripe_payment_id' => $paymentIntent->latest_charge ?? null,
                        'payment_method' => $actualMethod,
                    ]);
                    $payment->markOrdersAsPaid();
                }

                $table = $payment->table_id ? Table::find($payment->table_id) : null;
                $tableCleared = $table ? !$table->occupied : false;

                return view('payment.complete', [
                    'success' => true,
                    'qrCode' => $qrCode,
                    'amount' => $payment->amount,
                    'tableCleared' => $tableCleared,
                ]);
            }

            $status = $paymentIntent->status;
            $message = match ($status) {
                'processing' => 'Seu pagamento está sendo processado.',
                'requires_payment_method' => 'O pagamento falhou. Tente novamente.',
                default => 'Status do pagamento: ' . $status,
            };

            return view('payment.complete', [
                'success' => false,
                'qrCode' => $qrCode,
                'message' => $message,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na página de retorno do pagamento: ' . $e->getMessage());
            return redirect()->route('payment.show', $qrCode)
                ->with('error', 'Erro ao verificar pagamento.');
        }
    }
}
