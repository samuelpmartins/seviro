@php
    $itemPayload = json_decode($item->notes, true);
    $itemText =
        is_array($itemPayload) && array_key_exists('notes', $itemPayload)
            ? trim((string) $itemPayload['notes'])
            : trim((string) $item->notes);
    $added = [];
    $removed = [];

    foreach (preg_split('/\s*\|\s*/', $itemText, -1, PREG_SPLIT_NO_EMPTY) as $part) {
        if (str_starts_with($part, 'Adicionados:')) {
            $added[] = trim(substr($part, 12));
        } elseif (str_starts_with($part, 'Removidos:')) {
            $removed[] = trim(substr($part, 10));
        }
    }

    $itemObservation = preg_replace('/(^|\s*\|\s*)(Adicionados|Removidos):\s*[^|]*/', '', $itemText);
    $itemObservation = trim($itemObservation, " |\t\n\r\0\x0B");
@endphp

<li class="list-group-item">
    <div class="d-flex gap-3 align-items-start">
        @if ($item->product?->image)
            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}"
                class="order-item-image">
        @else
            <div class="order-item-image order-item-image-placeholder">
                <i class="fas fa-image"></i>
            </div>
        @endif

        <div class="flex-grow-1">
            <div class="order-summary-top">
                <strong>{{ $item->quantity }}x {{ $item->product?->name ?? 'Produto removido' }}</strong>
                <span>R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</span>
            </div>

            @if ($item->product?->description)
                <div class="small text-muted mt-1">{{ $item->product->description }}</div>
            @endif
            @if ($itemObservation !== '')
                <div class="small text-muted mt-2"><strong>Observação:</strong> {{ $itemObservation }}</div>
            @endif
            @if ($added)
                <div class="small text-success mt-1"><strong>Adicionados:</strong> {{ implode('; ', $added) }}</div>
            @endif
            @if ($removed)
                <div class="small text-danger mt-1"><strong>Removidos:</strong> {{ implode('; ', $removed) }}</div>
            @endif
        </div>
    </div>
</li>
