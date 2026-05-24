@extends('layouts.app')

@section('content')
<div style="min-height: 100vh; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; padding: 3rem; border-radius: 20px; text-align: center; max-width: 90%; width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        @if($success)
            <div style="width: 80px; height: 80px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i class="fas fa-check" style="font-size: 2.5rem; color: white;"></i>
            </div>
            <h3 style="color: #000; font-weight: 700; margin-bottom: 1rem;">Pagamento Confirmado!</h3>
            <p style="color: #666; font-size: 1.1rem; margin-bottom: 0.5rem;">
                Valor: <strong>R$ {{ number_format($amount, 2, ',', '.') }}</strong>
            </p>
            @if($tableCleared)
                <p style="color: #666; font-size: 1rem; margin-bottom: 2rem;">
                    Obrigado pela preferência! Volte sempre!
                </p>
            @else
                <p style="color: #666; font-size: 1rem; margin-bottom: 2rem;">
                    Pagamento processado com sucesso.
                </p>
            @endif
        @else
            <div style="width: 80px; height: 80px; background: {{ $status === 'processing' ? '#f59e0b' : '#ef4444' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i class="fas fa-{{ $status === 'processing' ? 'clock' : 'times' }}" style="font-size: 2.5rem; color: white;"></i>
            </div>
            <h3 style="color: #000; font-weight: 700; margin-bottom: 1rem;">
                {{ $status === 'processing' ? 'Processando...' : 'Pagamento não concluído' }}
            </h3>
            <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">
                {{ $message }}
            </p>
        @endif

        <a href="{{ route('menu.show', $qrCode) }}" class="btn" style="background: #000; color: white; border: none; padding: 1rem 2rem; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%; display: inline-block; text-decoration: none;">
            Voltar ao Cardápio
        </a>

        @if(!$success && $status !== 'processing')
            <a href="{{ route('payment.show', $qrCode) }}" class="btn mt-2" style="background: white; color: #000; border: 2px solid #000; padding: 1rem 2rem; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%; display: inline-block; text-decoration: none;">
                Tentar novamente
            </a>
        @endif
    </div>
</div>
@endsection
