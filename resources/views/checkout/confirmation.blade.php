@extends('layouts.app')

@section('title', 'Encomenda confirmada — '.$order->reference)
@section('meta_robots', 'noindex, nofollow')
@section('canonical', route('checkout.confirmation', $order->reference))

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div id="tbay-main-content" class="mm-page mm-slideout lv-omniva-checkout lv-order-flow">
        <div class="lv-order-hero">
            <div class="lv-container">
                <div class="lv-order-hero__inner">
                    <span class="lv-order-hero__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M8 12.5 10.8 15.2 16 9.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h1 class="lv-order-hero__title">Obrigado pela sua encomenda!</h1>
                    <p class="lv-order-hero__lead">
                        Encomenda <strong>{{ $order->reference }}</strong> · Confirmação enviada para
                        <span class="lv-break">{{ $order->email }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="lv-container lv-order-flow__body">
            @if (session('success'))
                <div class="lv-alert lv-alert--success" role="status">{{ session('success') }}</div>
            @endif

            <div class="lv-order-progress" role="list">
                <div class="lv-order-progress__item" role="listitem">
                    <span class="lv-order-progress__label">N.º encomenda</span>
                    <span class="lv-order-progress__value">{{ $order->reference }}</span>
                </div>
                <div class="lv-order-progress__item" role="listitem">
                    <span class="lv-order-progress__label">Data</span>
                    <span class="lv-order-progress__value">{{ $order->created_at?->timezone(config('app.timezone'))->format('d/m/Y') }}</span>
                </div>
                <div class="lv-order-progress__item" role="listitem">
                    <span class="lv-order-progress__label">Total</span>
                    <span class="lv-order-progress__value">{{ format_eur($order->total) }}</span>
                </div>
                <div class="lv-order-progress__item" role="listitem">
                    <span class="lv-order-progress__label">Pagamento</span>
                    <span class="lv-order-progress__value">{{ $order->paymentMethodLabel() }}</span>
                </div>
            </div>

            <div class="lv-order-layout">
                <div class="lv-order-layout__main">
                    <section class="lv-order-card lv-order-bank">
                        <h2>Pague agora por transferência bancária</h2>
                        <p class="lv-order-card__hint">
                            Transfira imediatamente o valor total para a conta abaixo. Indique a referência no descritivo.
                            O envio inicia-se após a confirmação do pagamento.
                        </p>
                        <dl class="lv-bank-details">
                            <div>
                                <dt>Titular</dt>
                                <dd><strong>{{ $bank['account_holder'] ?: '—' }}</strong></dd>
                            </div>
                            @if ($bank['bank_name'])
                                <div>
                                    <dt>Banco</dt>
                                    <dd>{{ $bank['bank_name'] }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt>IBAN</dt>
                                <dd class="lv-break"><strong>{{ format_iban($bank['iban']) ?: '—' }}</strong></dd>
                            </div>
                            @if ($bank['bic'])
                                <div>
                                    <dt>BIC</dt>
                                    <dd><strong>{{ $bank['bic'] }}</strong></dd>
                                </div>
                            @endif
                            <div>
                                <dt>Montante</dt>
                                <dd><strong>{{ format_eur($order->total) }}</strong></dd>
                            </div>
                            <div>
                                <dt>Referência / descritivo</dt>
                                <dd><strong>{{ $order->reference }}</strong></dd>
                            </div>
                        </dl>
                        @if ($bank['note'])
                            <p class="lv-order-card__hint">{{ $bank['note'] }}</p>
                        @endif
                    </section>

                    <section class="lv-order-card lv-order-proof" lang="pt">
                        <h2>Enviar comprovativo de transferência</h2>
                        <p class="lv-order-card__hint">
                            Depois de transferir, carregue o comprovativo. Será redirecionado para a página de sucesso
                            e poderá descarregar o recibo enquanto aguardamos a validação.
                        </p>

                        <form action="{{ route('checkout.payment-proof', $order->reference) }}" method="POST"
                            enctype="multipart/form-data" class="lv-proof-form" id="lv-proof-form">
                            @csrf
                            <span class="lv-proof-label" id="payment_proof_label">Comprovativo de transferência *</span>
                            <label class="lv-proof-picker @error('payment_proof') is-invalid @enderror" for="payment_proof"
                                aria-labelledby="payment_proof_label">
                                <input type="file" name="payment_proof" id="payment_proof" class="lv-proof-input"
                                    accept=".jpg,.jpeg,.png,.pdf,.webp,image/*,application/pdf" required>
                                <span class="lv-proof-btn" aria-hidden="true">Escolher ficheiro</span>
                                <span class="lv-proof-name" id="payment_proof_name">Nenhum ficheiro selecionado</span>
                            </label>
                            <p class="lv-order-card__hint">JPG, PNG, WEBP ou PDF · máx. 8 MB</p>
                            @error('payment_proof')
                                <div class="lv-field-error">{{ $message }}</div>
                            @enderror
                            <button type="submit" id="lv-proof-submit" class="tf-btn animate-btn size3 rounded-6 w-100 lv-proof-submit">
                                <span class="lv-btn__text">Enviar comprovativo</span>
                            </button>
                        </form>
                    </section>

                    <section class="lv-order-card">
                        <h2>Morada de envio</h2>
                        <p class="lv-order-address">
                            {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                            {{ $order->shipping_address_1 }}@if($order->shipping_address_2), {{ $order->shipping_address_2 }}@endif<br>
                            {{ $order->shipping_postcode }} {{ $order->shipping_city }}<br>
                            {{ $order->shipping_country }}
                        </p>
                    </section>
                </div>

                <aside class="lv-order-layout__side">
                    <div class="cart-box order-box lv-order-summary">
                        <h6 class="title cl-text-main">Detalhes da encomenda</h6>
                        <ul class="list-order-product">
                            @foreach ($order->items as $item)
                                <li class="order-item">
                                    <div class="content" style="width:100%">
                                        <div class="info">
                                            <p class="name text-caption fw-medium cl-text-main">{{ $item->name }}</p>
                                            <span class="variant text-extra-small fw-medium">Qtd. {{ $item->quantity }}</span>
                                        </div>
                                        <span class="price text-caption fw-medium">{{ format_eur($item->line_total) }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <ul class="list-total cl-text-main">
                            <li class="total-item text-caption d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span class="fw-medium">{{ format_eur($order->subtotal) }}</span>
                            </li>
                            <li class="total-item text-caption d-flex justify-content-between">
                                <span>Envio:</span>
                                <span class="fw-medium">Grátis</span>
                            </li>
                        </ul>
                        <div class="subtotal h6 d-flex justify-content-between cl-text-main">
                            <span>Total:</span>
                            <span>{{ format_eur($order->total) }}</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        @if (isset($relatedProducts) && $relatedProducts->count() > 0)
            <section class="lv-related" aria-labelledby="lv-related-title">
                <div class="lv-container">
                    <h2 id="lv-related-title" class="lv-related__title">Produtos semelhantes</h2>
                    <p class="lv-related__lead">Da mesma categoria da sua encomenda</p>
                    <div class="lv-product-grid">
                        @foreach ($relatedProducts as $relatedProduct)
                            <x-product-card :product="$relatedProduct" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>

    @include('layouts.partials.footer.public')
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('payment_proof');
    var nameEl = document.getElementById('payment_proof_name');
    var form = document.getElementById('lv-proof-form');
    var btn = document.getElementById('lv-proof-submit');
    if (!input || !nameEl) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        nameEl.textContent = file ? file.name : 'Nenhum ficheiro selecionado';
    });
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.disabled = true;
            var label = btn.querySelector('.lv-btn__text');
            if (label) label.textContent = 'A enviar…';
        });
    }
})();
</script>
@endpush
