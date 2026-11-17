@extends('layouts.app')

@section('title', 'Encomenda confirmada — '.$order->reference)
@section('meta_robots', 'noindex, nofollow')
@section('canonical', route('checkout.confirmation', $order->reference))

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div id="tbay-main-content" class="mm-page mm-slideout">
        <section class="lv-confirmation">
            <div class="lv-confirmation__inner">
                <header class="lv-confirmation__header">
                    <h1>Obrigado. A sua encomenda foi recebida.</h1>
                    <p>Encomenda <strong>{{ $order->reference }}</strong>. Uma confirmação foi enviada para <span class="lv-break">{{ $order->email }}</span>.</p>
                </header>

                @if (session('success'))
                    <div class="lv-confirmation__alert" role="status">{{ session('success') }}</div>
                @endif
                @if (session('status'))
                    <div class="lv-confirmation__alert" role="status">{{ session('status') }}</div>
                @endif

                <div class="lv-confirmation__card">
                    <ul class="lv-confirmation__lines">
                        @foreach ($order->items as $item)
                            <li>
                                <span>{{ $item->name }} × {{ $item->quantity }}</span>
                                <span>{{ format_eur($item->line_total) }}</span>
                            </li>
                        @endforeach
                        <li>
                            <span>Subtotal</span>
                            <span>{{ format_eur($order->subtotal) }}</span>
                        </li>
                        <li>
                            <span>Envio</span>
                            <span>Grátis (Portugal Continental)</span>
                        </li>
                        <li class="lv-confirmation__total">
                            <span>Total</span>
                            <span>{{ format_eur($order->total) }}</span>
                        </li>
                        <li class="lv-confirmation__muted">
                            <span>Pagamento</span>
                            <span>{{ $order->paymentMethodLabel() }}</span>
                        </li>
                    </ul>

                    <div class="lv-confirmation__block">
                        <strong>Morada de envio</strong>
                        <p>
                            {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                            {{ $order->shipping_address_1 }}@if($order->shipping_address_2), {{ $order->shipping_address_2 }}@endif<br>
                            {{ $order->shipping_postcode }} {{ $order->shipping_city }}<br>
                            {{ $order->shipping_country }}
                        </p>
                    </div>

                    <div class="lv-confirmation__block lv-confirmation__bank">
                        <h2>Pague agora por transferência bancária</h2>
                        <p class="lv-confirmation__hint">Transfira o valor total para a conta abaixo. O envio inicia-se após a confirmação do pagamento.</p>
                        <p>Titular: <strong>{{ $bank['account_holder'] ?: '—' }}</strong></p>
                        @if ($bank['bank_name'])
                            <p>Banco: {{ $bank['bank_name'] }}</p>
                        @endif
                        <p class="lv-break">IBAN: <strong>{{ format_iban($bank['iban']) ?: '—' }}</strong></p>
                        @if ($bank['bic'])
                            <p>BIC: <strong>{{ $bank['bic'] }}</strong></p>
                        @endif
                        <p>Montante: <strong>{{ format_eur($order->total) }}</strong></p>
                        <p>Referência / descritivo: <strong>{{ $order->reference }}</strong></p>
                        @if ($bank['note'])
                            <p class="lv-confirmation__hint">{{ $bank['note'] }}</p>
                        @endif
                    </div>

                    <div class="lv-confirmation__block lv-confirmation__proof" lang="pt">
                        <h2>Enviar comprovativo de transferência</h2>
                        <p class="lv-confirmation__hint">Depois de transferir, carregue o comprovativo (imagem ou PDF). Receberemos o ficheiro por email.</p>

                        @if ($order->payment_proof_path)
                            <p><strong>Estado:</strong> já enviou um comprovativo. Pode enviar outro ficheiro se necessário.</p>
                        @endif

                        <form action="{{ route('checkout.payment-proof', $order->reference) }}" method="POST" enctype="multipart/form-data" class="lv-proof-form">
                            @csrf
                            <span class="lv-proof-label" id="payment_proof_label">Comprovativo de transferência *</span>
                            <label class="lv-proof-picker @error('payment_proof') is-invalid @enderror" for="payment_proof" aria-labelledby="payment_proof_label">
                                <input type="file" name="payment_proof" id="payment_proof" class="lv-proof-input" accept=".jpg,.jpeg,.png,.pdf,.webp,image/*,application/pdf" required>
                                <span class="lv-proof-btn" aria-hidden="true">Escolher ficheiro</span>
                                <span class="lv-proof-name" id="payment_proof_name">Nenhum ficheiro selecionado</span>
                            </label>
                            <p class="lv-confirmation__hint">JPG, PNG, WEBP ou PDF, máx. 8 MB.</p>
                            @error('payment_proof')
                                <div class="lv-field-error">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="lv-btn lv-btn--primary lv-proof-submit">Enviar</button>
                        </form>
                    </div>
                </div>

                <div class="lv-confirmation__cta">
                    <a href="{{ route('loja') }}" class="lv-btn lv-btn--primary">Continuar a comprar</a>
                </div>
            </div>
        </section>
    </div>

    @include('layouts.partials.footer.public')

    <style>
        .lv-confirmation { padding: 24px 0 48px; }
        .lv-confirmation__inner { max-width: 720px; margin: 0 auto; padding: 0 16px; }
        .lv-confirmation__header h1 { font-size: clamp(1.2rem, 4vw, 1.75rem); margin: 0 0 8px; }
        .lv-confirmation__header p { margin: 0; color: #555; }
        .lv-break { overflow-wrap: anywhere; word-break: break-word; }
        .lv-confirmation__alert { margin: 16px 0; padding: 12px 14px; border-radius: 8px; background: #e8f5e9; color: #1b5e20; }
        .lv-confirmation__card { margin-top: 18px; border: 1px solid #e5e0d8; border-radius: 12px; background: #fff; padding: 16px; }
        .lv-confirmation__lines { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
        .lv-confirmation__lines li { display: flex; justify-content: space-between; gap: 12px; font-size: .95rem; }
        .lv-confirmation__total { font-weight: 700; padding-top: 8px; border-top: 1px solid #e5e0d8; }
        .lv-confirmation__muted { font-size: .85rem; opacity: .85; }
        .lv-confirmation__block { margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e0d8; }
        .lv-confirmation__block h2 { margin: 0 0 8px; font-size: 1.05rem; }
        .lv-confirmation__hint { margin: 0 0 10px; color: #666; font-size: .9rem; }
        .lv-proof-label { display: block; font-weight: 600; margin-bottom: 8px; }
        .lv-proof-picker { position: relative; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #d5c9b8; border-radius: 8px; background: #fff; cursor: pointer; }
        .lv-proof-picker.is-invalid { border-color: #dc3545; }
        .lv-proof-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
        .lv-proof-btn { pointer-events: none; border-radius: 6px; padding: 8px 14px; background: #1b3022; color: #fff; font-weight: 600; font-size: 14px; }
        .lv-proof-name { pointer-events: none; color: #666; font-size: 14px; word-break: break-all; }
        .lv-proof-submit { width: 100%; margin-top: 14px; }
        .lv-confirmation__cta { text-align: center; margin-top: 22px; }
        .lv-field-error { color: #8a1f11; margin-top: 6px; font-size: .9rem; }
        @media (min-width: 576px) {
            .lv-confirmation__card { padding: 24px; }
        }
    </style>
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('payment_proof');
    var nameEl = document.getElementById('payment_proof_name');
    if (!input || !nameEl) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        nameEl.textContent = file ? file.name : 'Nenhum ficheiro selecionado';
    });
})();
</script>
@endpush
