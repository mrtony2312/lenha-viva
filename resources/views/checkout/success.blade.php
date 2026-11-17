@extends('layouts.app')

@section('title', 'Encomenda registada — '.$order->reference)
@section('meta_robots', 'noindex, nofollow')
@section('canonical', route('checkout.success', $order->reference))

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div id="tbay-main-content" class="mm-page mm-slideout lv-omniva-checkout lv-order-flow">
        <div class="lv-order-hero">
            <div class="lv-container">
                <div class="lv-order-hero__inner">
                    <span class="lv-order-hero__icon lv-order-hero__icon--success" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M8 12.5 10.8 15.2 16 9.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h1 class="lv-order-hero__title">Encomenda registada com sucesso!</h1>
                    <p class="lv-order-hero__lead">
                        Obrigado, {{ $order->shipping_first_name }}. O comprovativo foi recebido.
                        Aguardamos a validação do pagamento para preparar o envio.
                    </p>
                </div>
            </div>
        </div>

        <div class="lv-container lv-order-flow__body">
            @if (session('status'))
                <div class="lv-alert lv-alert--success" role="status">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="lv-alert lv-alert--error" role="alert">{{ session('error') }}</div>
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
                    <span class="lv-order-progress__label">Estado</span>
                    <span class="lv-order-progress__value">Aguardar validação</span>
                </div>
            </div>

            <ol class="lv-order-timeline" aria-label="Estado da encomenda">
                <li class="lv-order-timeline__item is-done">
                    <span class="lv-order-timeline__dot" aria-hidden="true"></span>
                    <div>
                        <strong>Encomenda confirmada</strong>
                        <span>{{ $order->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</span>
                    </div>
                </li>
                <li class="lv-order-timeline__item is-done">
                    <span class="lv-order-timeline__dot" aria-hidden="true"></span>
                    <div>
                        <strong>Comprovativo enviado</strong>
                        <span>Recebido — em análise</span>
                    </div>
                </li>
                <li class="lv-order-timeline__item is-current">
                    <span class="lv-order-timeline__dot" aria-hidden="true"></span>
                    <div>
                        <strong>Validação do pagamento</strong>
                        <span>Em curso — prepararemos o envio após confirmação</span>
                    </div>
                </li>
                <li class="lv-order-timeline__item">
                    <span class="lv-order-timeline__dot" aria-hidden="true"></span>
                    <div>
                        <strong>Envio</strong>
                        <span>Após validação do pagamento</span>
                    </div>
                </li>
            </ol>

            <div class="lv-order-layout">
                <div class="lv-order-layout__main">
                    <section class="lv-order-card">
                        <h2>O que pode fazer agora</h2>
                        <p class="lv-order-card__hint">
                            Guarde o recibo da encomenda. Pode também descarregar o comprovativo que enviou.
                            Receberá atualizações por email ({{ $order->email }}).
                        </p>
                        <div class="lv-order-actions">
                            <a href="{{ route('checkout.receipt', $order->reference) }}" class="tf-btn animate-btn size3 rounded-6">
                                Descarregar recibo
                            </a>
                            <a href="{{ route('checkout.proof.download', $order->reference) }}" class="tf-btn tf-btn--outline size3 rounded-6">
                                Descarregar comprovativo
                            </a>
                            <a href="{{ route('loja') }}" class="tf-btn tf-btn--outline size3 rounded-6">
                                Continuar a comprar
                            </a>
                        </div>
                    </section>

                    <section class="lv-order-card lv-order-bank">
                        <h2>Dados da transferência</h2>
                        <dl class="lv-bank-details">
                            <div>
                                <dt>Titular</dt>
                                <dd><strong>{{ $bank['account_holder'] ?: '—' }}</strong></dd>
                            </div>
                            <div>
                                <dt>IBAN</dt>
                                <dd class="lv-break"><strong>{{ format_iban($bank['iban']) ?: '—' }}</strong></dd>
                            </div>
                            <div>
                                <dt>Montante</dt>
                                <dd><strong>{{ format_eur($order->total) }}</strong></dd>
                            </div>
                            <div>
                                <dt>Referência</dt>
                                <dd><strong>{{ $order->reference }}</strong></dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <aside class="lv-order-layout__side">
                    <div class="cart-box order-box lv-order-summary">
                        <h6 class="title cl-text-main">Resumo</h6>
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
                        <div class="subtotal h6 d-flex justify-content-between cl-text-main">
                            <span>Total:</span>
                            <span>{{ format_eur($order->total) }}</span>
                        </div>
                        <div class="btn-order" style="margin-top:20px">
                            <a href="{{ route('loja') }}" class="tf-btn animate-btn size3 rounded-6 w-100">Continuar a comprar</a>
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
