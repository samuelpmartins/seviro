<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    public function index()
    {
        $store = auth()->user()->store;

        // Garantir que a loja tenha um QR code de balcão
        if (!$store->counter_qr_code) {
            do {
                $counterQrCode = Str::random(32);
            } while (\App\Models\Store::where('counter_qr_code', $counterQrCode)->exists());

            $store->update(['counter_qr_code' => $counterQrCode]);
        }

        $tables = $store->tables()
            ->with('participants')
            ->orderBy('number')
            ->paginate(10);
        return view('store.tables.index', compact('tables', 'store'));
    }

    public function create()
    {
        return view('store.tables.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|string|max:10',
        ]);

        $store = auth()->user()->store;

        // Gerar QR Code único
        do {
            $qrCode = Str::random(32);
        } while ($store->tables()->where('qr_code', $qrCode)->exists());

        $store->tables()->create([
            'number' => $request->number,
            'qr_code' => $qrCode,
        ]);

        // Verificar de onde veio a requisição para redirecionar corretamente
        $referer = $request->headers->get('referer');
        if (str_contains($referer, '/store/manage')) {
            return redirect()->route('store.manage')->with('success', 'Mesa criada com sucesso!');
        } elseif (str_contains($referer, '/store/dashboard')) {
            return redirect()->route('store.dashboard')->with('success', 'Mesa criada com sucesso!');
        }

        return redirect()->route('store.tables.index')
            ->with('success', 'Mesa criada com sucesso!');
    }

    public function edit(Table $table)
    {
        $this->authorize('update', $table);
        return view('store.tables.edit', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $this->authorize('update', $table);

        $request->validate([
            'number' => 'required|string|max:10',
        ]);

        $table->update([
            'number' => $request->number,
        ]);

        return redirect()->route('store.tables.index')
            ->with('success', 'Mesa atualizada com sucesso!');
    }

    public function destroy(Table $table)
    {
        $this->authorize('delete', $table);

        $table->delete();

        return redirect()->route('store.tables.index')
            ->with('success', 'Mesa excluída com sucesso!');
    }

    public function generateQrCode(Table $table)
    {
        $this->authorize('view', $table);

        $url = route('menu.show', $table->qr_code);
        $qrCodeSvg = QrCode::size(300)->generate($url);

        // Modificar o SVG para incluir o número da mesa no centro
        $qrCodeWithTableNumber = $this->addTableNumberToQrCode($qrCodeSvg, $table->number);

        return view('store.tables.qrcode', compact('table', 'qrCodeWithTableNumber', 'url'));
    }

    /**
     * Gerar QR Code do balcão (sem mesa)
     */
    public function generateCounterQrCode()
    {
        $store = auth()->user()->store;

        // Garantir que a loja tenha um QR code de balcão
        if (!$store->counter_qr_code) {
            do {
                $counterQrCode = Str::random(32);
            } while (\App\Models\Store::where('counter_qr_code', $counterQrCode)->exists());

            $store->update(['counter_qr_code' => $counterQrCode]);
        }

        $url = route('menu.show', $store->counter_qr_code);
        $qrCodeSvg = QrCode::size(300)->generate($url);

        // Modificar o SVG para incluir "BALCÃO" no centro
        $qrCodeWithLabel = $this->addCounterLabelToQrCode($qrCodeSvg);

        return view('store.tables.counter-qrcode', compact('store', 'qrCodeWithLabel', 'url'));
    }

    private function addTableNumberToQrCode($svg, $tableNumber)
    {
        // Encontrar o centro do QR code (assumindo que é um quadrado)
        $centerX = 150; // 300px / 2
        $centerY = 150; // 300px / 2

        // Criar o círculo branco de fundo
        $circleBg = '<circle cx="' . $centerX . '" cy="' . $centerY . '" r="35" fill="white" stroke="#2c3e50" stroke-width="3"/>';

        // Criar o texto do número da mesa
        $text = '<text x="' . $centerX . '" y="' . ($centerY + 8) . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="#2c3e50">' . $tableNumber . '</text>';

        // Inserir o círculo e texto antes do fechamento do SVG
        $modifiedSvg = str_replace('</svg>', $circleBg . $text . '</svg>', $svg);

        return $modifiedSvg;
    }

    private function addCounterLabelToQrCode($svg)
    {
        // Encontrar o centro do QR code (assumindo que é um quadrado)
        $centerX = 150; // 300px / 2
        $centerY = 150; // 300px / 2

        // Criar um retângulo branco de fundo (mais largo para "BALCÃO")
        $rectBg = '<rect x="' . ($centerX - 55) . '" y="' . ($centerY - 20) . '" width="110" height="40" fill="white" stroke="#2c3e50" stroke-width="3" rx="5"/>';

        // Criar o texto "BALCÃO"
        $text = '<text x="' . $centerX . '" y="' . ($centerY + 8) . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="#2c3e50">BALCÃO</text>';

        // Inserir o retângulo e texto antes do fechamento do SVG
        $modifiedSvg = str_replace('</svg>', $rectBg . $text . '</svg>', $svg);

        return $modifiedSvg;
    }

    public function clear(Table $table)
    {
        $this->authorize('update', $table);

        $table->clearTable();

        return redirect()->route('store.tables.index')
            ->with('success', 'Mesa desocupada com sucesso!');
    }

    /**
     * Tela de atendimento das mesas
     */
    public function serviceScreen()
    {
        $store = auth()->user()->store;
        $tables = $store->tables()->with(['participants'])->orderBy('number')->get();

        // Para cada mesa, buscar informações de pedidos da sessão atual
        foreach ($tables as $table) {
            // Buscar IDs dos participantes ativos
            $activeParticipantIds = $table->participants->pluck('id')->toArray();

            // Se a mesa tem participantes ativos, mostrar apenas pedidos da sessão atual
            if (!empty($activeParticipantIds)) {
                // Total de pedidos não pagos da sessão atual
                $unpaidOrders = Order::where('table_id', $table->id)
                    ->whereIn('participant_id', $activeParticipantIds)
                    ->where('status', '!=', 'Pago')
                    ->get();

                $table->unpaid_total = $unpaidOrders->sum('total');
                $table->unpaid_count = $unpaidOrders->count();

                // Total de pedidos feitos na sessão atual
                $table->total_orders = Order::where('table_id', $table->id)
                    ->whereIn('participant_id', $activeParticipantIds)
                    ->count();
            } else {
                // Mesa desocupada - sem pedidos
                $table->unpaid_total = 0;
                $table->unpaid_count = 0;
                $table->total_orders = 0;
            }
        }

        return view('store.service', compact('tables'));
    }

    /**
     * Pagar todos os pedidos da mesa como pagos
     */
    public function payTable(Table $table)
    {
        $this->authorize('update', $table);

        // Buscar IDs dos participantes ativos da sessão atual
        $activeParticipantIds = $table->participants()->pluck('id')->toArray();

        // Se há participantes ativos, pagar apenas pedidos da sessão atual
        if (!empty($activeParticipantIds)) {
            $orders = Order::where('table_id', $table->id)
                ->whereIn('participant_id', $activeParticipantIds)
                ->where('status', '!=', 'Pago')
                ->get();

            foreach ($orders as $order) {
                $order->update(['status' => 'Pago']);
            }
        }

        return redirect()->route('store.service.index')
            ->with('success', 'Todos os pedidos da mesa ' . $table->number . ' foram pagos!');
    }

    /**
     * Pagar parcialmente os pedidos da mesa
     */
    public function payTablePartial(Request $request, Table $table)
    {
        $this->authorize('update', $table);

        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => ['required', Rule::exists('orders', 'id')->whereNull('DeletionDate')]
        ]);

        // Alterar os pedidos selecionados para status "Pago"
        Order::whereIn('id', $request->order_ids)
            ->update(['status' => 'Pago']);

        return redirect()->route('store.service.index')
            ->with('success', 'Os pedidos selecionados da mesa ' . $table->number . ' foram pagos!');
    }
}
