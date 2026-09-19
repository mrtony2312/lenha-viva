@extends('layouts.app')

@section('title', 'Pagamento seguro — Finalização de compra')
@section('meta_robots', 'noindex, nofollow')
@section('canonical', route('checkout'))

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div id="tbay-main-content" class="mm-page mm-slideout lv-checkout">
        <div class="lv-checkout__secure-bar">
            <div class="lv-container lv-checkout__secure-bar-inner">
                <p class="lv-checkout__secure-badge">
                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="currentColor">
                        <path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                    </svg>
                    Pagamento seguro
                </p>
                <p class="lv-checkout__secure-hint">Transferência bancária · Envio grátis em Portugal Continental</p>
            </div>
        </div>

        <div class="lv-container lv-checkout__body">
            <header class="lv-checkout__intro">
                <h1 class="lv-checkout__intro-title">Pagamento</h1>
                <p class="lv-checkout__intro-lead">Finalize a sua encomenda com segurança. O envio é feito após a confirmação do pagamento.</p>
            </header>

            @if (session('success'))
                <div class="lv-alert lv-alert--success" role="status">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="lv-alert lv-alert--error" role="alert">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="lv-alert lv-alert--error" role="alert" tabindex="-1" id="checkout-error-summary">
                    <p class="lv-alert__title">Verifique estes campos:</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($isEmpty)
                <div class="lv-checkout__empty">
                    <h2>O seu carrinho está vazio</h2>
                    <p>Adicione produtos ao carrinho antes de finalizar a compra.</p>
                    <a href="{{ route('home') }}" class="lv-btn lv-btn--primary">Continuar a comprar</a>
                </div>
            @else
                <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form"
                    class="lv-checkout__grid" aria-label="Finalizar compra">
                    @csrf

                    <input type="hidden" name="shipping_method" id="shipping_method" value="free_shipping:3">
                    <input type="hidden" name="payment_method" id="payment_method" value="bacs">
                    <input type="hidden" name="order_notes" id="order_notes">

                    <div class="lv-checkout__main">
                        {{-- 1. Contact --}}
                        <section class="lv-card lv-checkout-card" id="contact-fields" aria-labelledby="checkout-contact-title">
                            <h2 id="checkout-contact-title" class="lv-card__title">
                                <span class="lv-step-num" aria-hidden="true">1</span>
                                Informação de contacto
                            </h2>
                            <p class="lv-card__desc">Utilizaremos este email para lhe enviar a confirmação e o seguimento da encomenda.</p>
                            <div class="lv-field">
                                <label for="email">Endereço de email</label>
                                <input type="email" id="email" name="email" autocomplete="email"
                                    placeholder="o.seu@email.com"
                                    class="lv-input @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}"
                                    @error('email') aria-describedby="email-error" @enderror required>
                                @error('email')
                                    <span class="lv-field-error" id="email-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <p class="lv-card__note">Está a finalizar a encomenda como convidado.</p>
                        </section>

                        {{-- 2. Shipping --}}
                        <section class="lv-card lv-checkout-card" id="shipping-fields" aria-labelledby="checkout-shipping-title">
                            <h2 id="checkout-shipping-title" class="lv-card__title">
                                <span class="lv-step-num" aria-hidden="true">2</span>
                                Morada de envio
                            </h2>
                            <p class="lv-card__desc">Indique onde deseja receber a palete ou o equipamento.</p>

                            <div class="lv-field-grid">
                                <div class="lv-field lv-field--full">
                                    <label for="shipping-country">País/Região</label>
                                    <select id="shipping-country" name="shipping-country" autocomplete="country"
                                        class="lv-input lv-select @error('shipping-country') is-invalid @enderror"
                                        @error('shipping-country') aria-describedby="shipping-country-error" @enderror
                                        required>
                                        <option value="" disabled>Selecione um país/região</option>
                                        @foreach ($pays as $code => $nom)
                                            <option value="{{ $code }}"
                                                {{ old('shipping-country', 'PT') == $code ? 'selected' : '' }}>
                                                {{ $code === 'ES' ? 'Espanha' : ($code === 'PT' ? 'Portugal' : $nom) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shipping-country')
                                        <span class="lv-field-error" id="shipping-country-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field">
                                    <label for="shipping-first_name">Nome próprio</label>
                                    <input type="text" id="shipping-first_name" name="shipping-first_name"
                                        autocomplete="given-name"
                                        class="lv-input @error('shipping-first_name') is-invalid @enderror"
                                        value="{{ old('shipping-first_name') }}"
                                        @error('shipping-first_name') aria-describedby="shipping-first_name-error" @enderror required>
                                    @error('shipping-first_name')
                                        <span class="lv-field-error" id="shipping-first_name-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field">
                                    <label for="shipping-last_name">Apelido</label>
                                    <input type="text" id="shipping-last_name" name="shipping-last_name"
                                        autocomplete="family-name"
                                        class="lv-input @error('shipping-last_name') is-invalid @enderror"
                                        value="{{ old('shipping-last_name') }}"
                                        @error('shipping-last_name') aria-describedby="shipping-last_name-error" @enderror required>
                                    @error('shipping-last_name')
                                        <span class="lv-field-error" id="shipping-last_name-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field lv-field--full">
                                    <label for="shipping-address_1">Morada</label>
                                    <input type="text" id="shipping-address_1" name="shipping-address_1"
                                        autocomplete="address-line1"
                                        placeholder="Rua, número…"
                                        class="lv-input @error('shipping-address_1') is-invalid @enderror"
                                        value="{{ old('shipping-address_1') }}"
                                        @error('shipping-address_1') aria-describedby="shipping-address_1-error" @enderror required>
                                    @error('shipping-address_1')
                                        <span class="lv-field-error" id="shipping-address_1-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field lv-field--full">
                                    <label for="shipping-address_2">Morada (linha 2, opcional)</label>
                                    <input type="text" id="shipping-address_2" name="shipping-address_2"
                                        autocomplete="address-line2" class="lv-input"
                                        value="{{ old('shipping-address_2') }}">
                                </div>

                                <div class="lv-field">
                                    <label for="shipping-postcode">Código postal</label>
                                    <input type="text" id="shipping-postcode" name="shipping-postcode"
                                        autocomplete="postal-code"
                                        class="lv-input @error('shipping-postcode') is-invalid @enderror"
                                        value="{{ old('shipping-postcode') }}"
                                        @error('shipping-postcode') aria-describedby="shipping-postcode-error" @enderror required>
                                    @error('shipping-postcode')
                                        <span class="lv-field-error" id="shipping-postcode-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field">
                                    <label for="shipping-city">Localidade</label>
                                    <input type="text" id="shipping-city" name="shipping-city"
                                        autocomplete="address-level2"
                                        class="lv-input @error('shipping-city') is-invalid @enderror"
                                        value="{{ old('shipping-city') }}"
                                        @error('shipping-city') aria-describedby="shipping-city-error" @enderror required>
                                    @error('shipping-city')
                                        <span class="lv-field-error" id="shipping-city-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field lv-field--full">
                                    <label for="shipping-phone">Telemóvel (recomendado para a entrega)</label>
                                    <input type="tel" id="shipping-phone" name="shipping-phone" autocomplete="tel"
                                        class="lv-input" value="{{ old('shipping-phone') }}">
                                </div>
                            </div>
                        </section>

                        {{-- 3. Shipping method --}}
                        <section class="lv-card lv-checkout-card" aria-labelledby="checkout-method-title">
                            <h2 id="checkout-method-title" class="lv-card__title">
                                <span class="lv-step-num" aria-hidden="true">3</span>
                                Modo de envio
                            </h2>
                            <div class="lv-ship-options" role="list">
                                <div class="lv-ship-option lv-ship-option--selected" role="listitem">
                                    <div class="lv-ship-option__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.75">
                                            <rect x="1" y="7" width="13" height="10" rx="1"/>
                                            <path d="M14 10h4l3 3v4h-7"/>
                                            <circle cx="6" cy="18" r="2"/>
                                            <circle cx="18" cy="18" r="2"/>
                                        </svg>
                                    </div>
                                    <div class="lv-ship-option__body">
                                        <div class="lv-ship-option__head">
                                            <h3>Envio padrão — Portugal Continental</h3>
                                            <span class="lv-ship-option__price">Grátis</span>
                                        </div>
                                        <p>Palete entregue à porta do camião. Prazo habitual: 3 a 5 dias úteis após a confirmação do pagamento. O envio gratuito aplica-se apenas a Portugal Continental. Açores e Madeira: <a href="{{ route('politicaDeEntrega') }}">sob consulta</a> (não estão incluídos no envio grátis automático).</p>
                                        <a class="lv-ship-option__link" href="{{ route('politicaDeEntrega') }}">Ver política de entrega</a>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- 4. Billing --}}
                        <section class="lv-card lv-checkout-card" id="billing-fields" aria-labelledby="checkout-billing-title">
                            <h2 id="checkout-billing-title" class="lv-card__title">
                                <span class="lv-step-num" aria-hidden="true">4</span>
                                Morada de faturação
                            </h2>

                            <label class="lv-checkbox">
                                <input type="checkbox" id="same-address-checkbox" checked>
                                <span>Usar a mesma morada para a faturação</span>
                            </label>

                            <div class="lv-field-grid" id="billing-address-wrapper" hidden>
                                <div class="lv-field lv-field--full">
                                    <label for="billing-country">País/Região</label>
                                    <select id="billing-country" name="billing-country" autocomplete="country"
                                        class="lv-input lv-select @error('billing-country') is-invalid @enderror"
                                        @error('billing-country') aria-describedby="billing-country-error" @enderror>
                                        <option value="" disabled {{ old('billing-country') ? '' : 'selected' }}>
                                            Selecione um país/região</option>
                                        @foreach ($pays as $code => $nom)
                                            <option value="{{ $code }}"
                                                {{ old('billing-country') == $code ? 'selected' : '' }}>
                                                {{ $code === 'ES' ? 'Espanha' : ($code === 'PT' ? 'Portugal' : $nom) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('billing-country')
                                        <span class="lv-field-error" id="billing-country-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field">
                                    <label for="billing-first_name">Nome próprio</label>
                                    <input type="text" id="billing-first_name" name="billing-first_name"
                                        autocomplete="given-name"
                                        class="lv-input @error('billing-first_name') is-invalid @enderror"
                                        value="{{ old('billing-first_name') }}"
                                        @error('billing-first_name') aria-describedby="billing-first_name-error" @enderror>
                                    @error('billing-first_name')
                                        <span class="lv-field-error" id="billing-first_name-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field">
                                    <label for="billing-last_name">Apelido</label>
                                    <input type="text" id="billing-last_name" name="billing-last_name"
                                        autocomplete="family-name"
                                        class="lv-input @error('billing-last_name') is-invalid @enderror"
                                        value="{{ old('billing-last_name') }}"
                                        @error('billing-last_name') aria-describedby="billing-last_name-error" @enderror>
                                    @error('billing-last_name')
                                        <span class="lv-field-error" id="billing-last_name-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field lv-field--full">
                                    <label for="billing-address_1">Morada</label>
                                    <input type="text" id="billing-address_1" name="billing-address_1"
                                        autocomplete="address-line1"
                                        class="lv-input @error('billing-address_1') is-invalid @enderror"
                                        value="{{ old('billing-address_1') }}"
                                        @error('billing-address_1') aria-describedby="billing-address_1-error" @enderror>
                                    @error('billing-address_1')
                                        <span class="lv-field-error" id="billing-address_1-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field lv-field--full">
                                    <label for="billing-address_2">Morada (linha 2, opcional)</label>
                                    <input type="text" id="billing-address_2" name="billing-address_2"
                                        autocomplete="address-line2" class="lv-input"
                                        value="{{ old('billing-address_2') }}">
                                </div>

                                <div class="lv-field">
                                    <label for="billing-postcode">Código postal</label>
                                    <input type="text" id="billing-postcode" name="billing-postcode"
                                        autocomplete="postal-code"
                                        class="lv-input @error('billing-postcode') is-invalid @enderror"
                                        value="{{ old('billing-postcode') }}"
                                        @error('billing-postcode') aria-describedby="billing-postcode-error" @enderror>
                                    @error('billing-postcode')
                                        <span class="lv-field-error" id="billing-postcode-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field">
                                    <label for="billing-city">Localidade</label>
                                    <input type="text" id="billing-city" name="billing-city"
                                        autocomplete="address-level2"
                                        class="lv-input @error('billing-city') is-invalid @enderror"
                                        value="{{ old('billing-city') }}"
                                        @error('billing-city') aria-describedby="billing-city-error" @enderror>
                                    @error('billing-city')
                                        <span class="lv-field-error" id="billing-city-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="lv-field lv-field--full">
                                    <label for="billing-phone">Telemóvel (opcional)</label>
                                    <input type="tel" id="billing-phone" name="billing-phone" autocomplete="tel"
                                        class="lv-input" value="{{ old('billing-phone') }}">
                                </div>
                            </div>
                        </section>

                        {{-- 5. Payment --}}
                        <section class="lv-card lv-checkout-card" aria-labelledby="checkout-payment-title">
                            <h2 id="checkout-payment-title" class="lv-card__title">
                                <span class="lv-step-num" aria-hidden="true">5</span>
                                Pagamento
                            </h2>

                            <div class="lv-pay-method lv-pay-method--selected">
                                <div class="lv-pay-method__head">
                                    <span class="lv-pay-method__radio" aria-hidden="true"></span>
                                    <strong>Transferência bancária</strong>
                                </div>
                                <p>
                                    Efetue o pagamento a partir da sua conta bancária. Indique o seu nome e o número
                                    da encomenda na descrição. A encomenda é preparada assim que recebermos os fundos.
                                </p>
                                <p class="lv-pay-method__note">
                                    Receberá os dados bancários por email depois de confirmar a encomenda.
                                    Consulte também a
                                    <a href="{{ route('politicaDePagamento') }}">política de pagamento</a>.
                                </p>
                            </div>

                            <div class="lv-secure-note">
                                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="currentColor">
                                    <path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                                </svg>
                                <div>
                                    <p class="lv-secure-note__title">Ligação segura SSL</p>
                                    <p>Os seus dados são transmitidos de forma cifrada. Não pedimos nem guardamos dados de cartão.</p>
                                </div>
                            </div>
                        </section>

                        {{-- Notes + terms --}}
                        <section class="lv-card lv-checkout-card">
                            <label class="lv-checkbox">
                                <input type="checkbox" id="add-note-checkbox">
                                <span>Adicionar uma nota à encomenda (acessos, horário…)</span>
                            </label>
                            <textarea id="order-notes-textarea" class="lv-textarea" hidden rows="3"
                                placeholder="Notas sobre a entrega (centro histórico, condomínio, etc.).">{{ old('order_notes') }}</textarea>
                        </section>

                        <section class="lv-card lv-checkout-card">
                            <label class="lv-checkbox">
                                <input type="checkbox" id="terms-checkbox" name="terms_checkbox" value="1"
                                    {{ old('terms_checkbox') ? 'checked' : '' }}>
                                <span>
                                    Ao continuar, aceito as
                                    <a href="{{ route('condicoes-gerais-de-venda-cgv') }}" target="_blank" rel="noopener">condições gerais de venda</a>
                                    e a
                                    <a href="{{ route('politica-de-privacidade') }}" target="_blank" rel="noopener">política de privacidade</a>.
                                </span>
                            </label>
                            @error('terms')
                                <span class="lv-field-error">{{ $message }}</span>
                            @enderror
                        </section>
                    </div>

                    <aside class="lv-checkout__summary">
                        <div class="lv-summary-panel">
                            <div class="lv-summary-panel__head">
                                <h2>Resumo da encomenda</h2>
                            </div>

                            <ul class="lv-summary-items">
                                @foreach ($cart as $productId => $item)
                                    @php
                                        $rawPrice = is_string($item['price'] ?? null)
                                            ? (float) str_replace([',', ' '], '', $item['price'])
                                            : (float) ($item['price'] ?? 0);
                                        $itemQuantity = (int) ($item['quantity'] ?? 0);
                                        $itemTotal = $rawPrice * $itemQuantity;
                                    @endphp
                                    <li class="lv-summary-item">
                                        <div class="lv-summary-item__image">
                                            @if (!empty($item['image']))
                                                <img src="{{ asset($item['image']) }}"
                                                    alt="{{ $item['title'] }}" width="72" height="72" loading="lazy">
                                            @endif
                                            <span class="lv-summary-item__qty">{{ $itemQuantity }}</span>
                                        </div>
                                        <div class="lv-summary-item__body">
                                            <p class="lv-summary-item__title">{{ $item['title'] }}</p>
                                            <p class="lv-summary-item__desc">IVA incluído</p>
                                        </div>
                                        <div class="lv-summary-item__total">
                                            {{ number_format($itemTotal, 2, ',', ' ') }} €
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="lv-summary-panel__foot">
                                <div class="lv-summary-totals">
                                    <div class="lv-summary-totals__row">
                                        <span>Subtotal</span>
                                        <span>{{ $formattedTotalPrice }} €</span>
                                    </div>
                                    <div class="lv-summary-totals__row">
                                        <span>Envio (Portugal Continental)</span>
                                        <span class="lv-summary-totals__free">Grátis</span>
                                    </div>
                                    <div class="lv-summary-totals__row lv-summary-totals__row--total">
                                        <span>Total</span>
                                        <span class="lv-summary-totals__amount">
                                            <span class="lv-summary-totals__currency">EUR</span>
                                            {{ $formattedTotalPrice }} €
                                        </span>
                                    </div>
                                </div>

                                <button type="submit" id="submit-order" class="lv-btn lv-btn--primary lv-btn--block lv-btn--checkout">
                                    <span class="lv-btn__text">Confirmar encomenda · {{ $formattedTotalPrice }} €</span>
                                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor">
                                        <path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                                    </svg>
                                </button>
                                <a href="{{ route('carrinho') }}" class="lv-btn lv-btn--ghost lv-btn--block">Voltar ao carrinho</a>
                                <p class="lv-summary-legal">
                                    Ao confirmar, aceita as nossas
                                    <a href="{{ route('condicoes-gerais-de-venda-cgv') }}" target="_blank" rel="noopener">CGV</a>.
                                </p>
                            </div>
                        </div>

                        <div class="lv-trust-badges">
                            <div class="lv-trust-badge">
                                <span class="lv-trust-badge__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="lv-trust-badge__title">Envio grátis</p>
                                    <p>Para todo o Portugal continental.</p>
                                </div>
                            </div>
                            <div class="lv-trust-badge">
                                <span class="lv-trust-badge__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="lv-trust-badge__title">Paletes à porta do camião</p>
                                    <p>Logística profissional para pellets e lenha.</p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </form>
            @endif
        </div>
    </div>

    @include('layouts.partials.footer.public')
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const $form = $('#checkout-form');
            if (!$form.length) {
                return;
            }

            const $sameAddress = $('#same-address-checkbox');
            const $billingWrapper = $('#billing-address-wrapper');
            const $addNote = $('#add-note-checkbox');
            const $notesTextarea = $('#order-notes-textarea');
            const $submitBtn = $('#submit-order');
            const defaultSubmitText = $submitBtn.find('.lv-btn__text').text();

            function toggleBillingAddress() {
                if ($sameAddress.is(':checked')) {
                    $billingWrapper.prop('hidden', true).hide();
                    copyShippingToBilling();
                } else {
                    $billingWrapper.prop('hidden', false).slideDown(150);
                }
            }

            function copyShippingToBilling() {
                ['first_name', 'last_name', 'address_1', 'address_2', 'city', 'postcode', 'country', 'phone']
                    .forEach(field => {
                        $(`#billing-${field}`).val($(`#shipping-${field}`).val());
                    });
            }

            $sameAddress.on('change', toggleBillingAddress);
            toggleBillingAddress();

            $addNote.on('change', function() {
                if ($addNote.is(':checked')) {
                    $notesTextarea.prop('hidden', false).slideDown(150).trigger('focus');
                } else {
                    $notesTextarea.slideUp(150, function() {
                        $notesTextarea.prop('hidden', true);
                    });
                }
            });

            if ($notesTextarea.val().trim() !== '') {
                $addNote.prop('checked', true);
                $notesTextarea.prop('hidden', false).show();
            }

            $form.on('submit', function(e) {
                $submitBtn.prop('disabled', true);
                $submitBtn.find('.lv-btn__text').text('A processar…');

                let isValid = true;
                const requiredFields = [
                    'email', 'shipping-first_name', 'shipping-last_name', 'shipping-address_1',
                    'shipping-city', 'shipping-postcode', 'shipping-country',
                ];

                if (!$sameAddress.is(':checked')) {
                    requiredFields.push(
                        'billing-first_name', 'billing-last_name',
                        'billing-address_1', 'billing-city', 'billing-postcode', 'billing-country'
                    );
                }

                $('.lv-field-error.js-client').remove();
                $('.is-invalid').removeClass('is-invalid');

                requiredFields.forEach(field => {
                    const $el = $(`[name="${field}"]`);
                    if ($el.length && !$el.val().trim()) {
                        isValid = false;
                        $el.addClass('is-invalid');
                        $el.closest('.lv-field').append(
                            '<span class="lv-field-error js-client">Este campo é obrigatório</span>');
                    }
                });

                const email = $('#email').val();
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    isValid = false;
                    $('#email').addClass('is-invalid');
                    if (!$('#email').closest('.lv-field').find('.lv-field-error').length) {
                        $('#email').closest('.lv-field').append(
                            '<span class="lv-field-error js-client">Introduza um email válido</span>');
                    }
                }

                if (!$('#terms-checkbox').is(':checked')) {
                    isValid = false;
                    $('#terms-checkbox').addClass('is-invalid');
                    if (!$('#terms-checkbox').closest('.lv-checkbox').next('.lv-field-error').length) {
                        $('#terms-checkbox').closest('.lv-checkbox').after(
                            '<span class="lv-field-error js-client">Tem de aceitar as condições</span>'
                        );
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    $submitBtn.prop('disabled', false);
                    $submitBtn.find('.lv-btn__text').text(defaultSubmitText);

                    const $firstError = $('.is-invalid').first();
                    if ($firstError.length) {
                        $('html, body').animate({
                            scrollTop: $firstError.offset().top - 120
                        }, 400);
                        $firstError.trigger('focus');
                    }
                    return false;
                }

                if ($sameAddress.is(':checked')) {
                    copyShippingToBilling();
                }
                $('#order_notes').val($addNote.is(':checked') ? $notesTextarea.val() : '');
                localStorage.removeItem('checkout_form_data');
            });

            function saveFormData() {
                const formData = {};
                $form.find('input, select, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (!name || name === '_token') {
                        return;
                    }
                    if ($(this).attr('type') === 'checkbox') {
                        formData[name] = $(this).is(':checked') ? '1' : '0';
                    } else {
                        const value = $(this).val();
                        if (value && String(value).trim() !== '') {
                            formData[name] = value;
                        }
                    }
                });
                if (Object.keys(formData).length > 0) {
                    localStorage.setItem('checkout_form_data', JSON.stringify(formData));
                }
            }

            function loadSavedData() {
                const saved = localStorage.getItem('checkout_form_data');
                if (!saved) {
                    return;
                }
                try {
                    const data = JSON.parse(saved);
                    if (@json(old() ? true : false)) {
                        return;
                    }
                    Object.keys(data).forEach(key => {
                        const $el = $(`[name="${key}"]`);
                        if (!$el.length) {
                            return;
                        }
                        if ($el.attr('type') === 'checkbox') {
                            $el.prop('checked', data[key] === '1').trigger('change');
                        } else if (!$el.val()) {
                            $el.val(data[key]);
                        }
                    });
                } catch (e) {
                    localStorage.removeItem('checkout_form_data');
                }
            }

            loadSavedData();

            let saveTimeout;
            $form.on('input change', 'input, select, textarea', function() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(saveFormData, 500);
            });

            @if (session('success'))
                localStorage.removeItem('checkout_form_data');
            @endif

            setInterval(function() {
                if ($submitBtn.prop('disabled')) {
                    return;
                }
                $.get('{{ route('refresh') }}', function(data) {
                    $('input[name="_token"]').val(data.token);
                    $('meta[name="csrf-token"]').attr('content', data.token);
                });
            }, 15 * 60 * 1000);
        });
    </script>
@endpush
