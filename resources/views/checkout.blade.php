@extends('layouts.app')

@section('title', 'Pagamento seguro — Finalização de compra')
@section('meta_robots', 'noindex, nofollow')
@section('canonical', route('checkout'))

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div id="tbay-main-content" class="mm-page mm-slideout lv-checkout lv-omniva-checkout">
        <div class="tf-page-title-2">
            <div class="lv-container">
                <div class="tf-page-title-2__content">
                    <ul class="breadcrumb">
                        <li><a href="{{ route('home') }}" class="link-underline">Início</a></li>
                        <li aria-hidden="true">/</li>
                        <li><a href="{{ route('carrinho') }}" class="link-underline">Carrinho</a></li>
                        <li aria-hidden="true">/</li>
                        <li aria-current="page">Checkout</li>
                    </ul>
                    <h1 class="tf-page-title-2__heading">Finalizar compra</h1>
                    <p class="tf-page-title-2__lead">
                        Transferência bancária · Envio apenas para Portugal Continental
                    </p>
                </div>
            </div>
        </div>

        <div class="lv-container flat-spacing-checkout">
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
                    <a href="{{ route('home') }}" class="tf-btn animate-btn">Continuar a comprar</a>
                </div>
            @else
                <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form"
                    class="row gy-30 checkout-layout" aria-label="Finalizar compra">
                    @csrf

                    <input type="hidden" name="shipping_method" id="shipping_method" value="free_shipping:3">
                    <input type="hidden" name="payment_method" id="payment_method" value="bacs">
                    <input type="hidden" name="order_notes" id="order_notes">

                    <div class="col-lg-8">
                        <div class="tf-checkout-cart-main">
                            {{-- Contact + shipping in one form block --}}
                            <div class="box-ip-checkout" id="shipping-fields">
                                <h6 class="title">Checkout</h6>
                                <div class="form-content gap-15" id="contact-fields">
                                    <div class="tf-grid-layout gap-16 sm-col-2">
                                        <div class="tf-field style-2">
                                            <input class="tf-field-input tf-input @error('shipping-first_name') is-invalid @enderror"
                                                id="shipping-first_name" name="shipping-first_name" type="text"
                                                autocomplete="given-name" placeholder=" "
                                                value="{{ old('shipping-first_name') }}" required>
                                            <label class="tf-field-label" for="shipping-first_name">Nome próprio</label>
                                        </div>
                                        <div class="tf-field style-2">
                                            <input class="tf-field-input tf-input @error('shipping-last_name') is-invalid @enderror"
                                                id="shipping-last_name" name="shipping-last_name" type="text"
                                                autocomplete="family-name" placeholder=" "
                                                value="{{ old('shipping-last_name') }}" required>
                                            <label class="tf-field-label" for="shipping-last_name">Apelido</label>
                                        </div>
                                    </div>

                                    <fieldset class="tf-field style-2">
                                        <input class="tf-field-input tf-input @error('email') is-invalid @enderror"
                                            id="email" name="email" type="email" autocomplete="email"
                                            placeholder=" " value="{{ old('email') }}" required>
                                        <label class="tf-field-label" for="email">Endereço de email</label>
                                    </fieldset>
                                    @error('email')
                                        <span class="lv-field-error">{{ $message }}</span>
                                    @enderror

                                    <fieldset class="tf-field style-2 tf-field--select">
                                        <select id="shipping-country" name="shipping-country" autocomplete="country"
                                            class="tf-field-input tf-input tf-select @error('shipping-country') is-invalid @enderror"
                                            required>
                                            <option value="" disabled {{ old('shipping-country') ? '' : '' }}>País/Região</option>
                                            @foreach ($pays as $code => $nom)
                                                <option value="{{ $code }}"
                                                    {{ old('shipping-country', 'PT') == $code ? 'selected' : '' }}>
                                                    {{ $code === 'ES' ? 'Espanha' : ($code === 'PT' ? 'Portugal' : $nom) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label class="tf-field-label is-floated" for="shipping-country">País/Região</label>
                                    </fieldset>

                                    <fieldset class="tf-field style-2">
                                        <input class="tf-field-input tf-input @error('shipping-address_1') is-invalid @enderror"
                                            id="shipping-address_1" name="shipping-address_1" type="text"
                                            autocomplete="address-line1" placeholder=" "
                                            value="{{ old('shipping-address_1') }}" required>
                                        <label class="tf-field-label" for="shipping-address_1">Morada</label>
                                    </fieldset>

                                    <fieldset class="tf-field style-2">
                                        <input class="tf-field-input tf-input" id="shipping-address_2"
                                            name="shipping-address_2" type="text" autocomplete="address-line2"
                                            placeholder=" " value="{{ old('shipping-address_2') }}">
                                        <label class="tf-field-label" for="shipping-address_2">Apartamento, andar… (opcional)</label>
                                    </fieldset>

                                    <div class="tf-grid-layout gap-16 sm-col-2">
                                        <fieldset class="tf-field style-2">
                                            <input class="tf-field-input tf-input @error('shipping-city') is-invalid @enderror"
                                                id="shipping-city" name="shipping-city" type="text"
                                                autocomplete="address-level2" placeholder=" "
                                                value="{{ old('shipping-city') }}" required>
                                            <label class="tf-field-label" for="shipping-city">Localidade</label>
                                        </fieldset>
                                        <fieldset class="tf-field style-2">
                                            <input class="tf-field-input tf-input @error('shipping-postcode') is-invalid @enderror"
                                                id="shipping-postcode" name="shipping-postcode" type="text"
                                                autocomplete="postal-code" placeholder=" "
                                                value="{{ old('shipping-postcode') }}" required>
                                            <label class="tf-field-label" for="shipping-postcode">Código postal</label>
                                        </fieldset>
                                    </div>

                                    <fieldset class="tf-field style-2">
                                        <input class="tf-field-input tf-input" id="shipping-phone" name="shipping-phone"
                                            type="tel" autocomplete="tel" placeholder=" "
                                            value="{{ old('shipping-phone') }}">
                                        <label class="tf-field-label" for="shipping-phone">Telemóvel (recomendado)</label>
                                    </fieldset>
                                </div>
                            </div>

                            {{-- Shipping method --}}
                            <div class="box-ip-shipping">
                                <h6 class="title">Modo de envio</h6>
                                <fieldset class="mb-0">
                                    <label for="freeship" class="check-ship">
                                        <input type="radio" id="freeship" class="tf-check-rounded style-small"
                                            name="checkshipping" checked>
                                        <span class="text text-caption">
                                            <span>Envio padrão — Portugal Continental (3 a 5 dias úteis após pagamento)</span>
                                            <span class="price">Grátis</span>
                                        </span>
                                    </label>
                                </fieldset>
                                <p class="box-hint mt-12">
                                    Palete entregue à porta do camião.
                                    <a href="{{ route('politicaDeEntrega') }}" class="link-underline">Ver política de entrega</a>
                                </p>
                            </div>

                            {{-- Billing --}}
                            <div class="box-ip-checkout" id="billing-fields">
                                <h6 class="title">Morada de faturação</h6>
                                <div class="checkbox-wrap gap-8 mb-16">
                                    <input type="checkbox" class="tf-check" id="same-address-checkbox" checked>
                                    <label for="same-address-checkbox" class="text-caption">
                                        Usar a mesma morada para a faturação
                                    </label>
                                </div>

                                <div class="form-content gap-15" id="billing-address-wrapper" hidden>
                                    <div class="tf-grid-layout gap-16 sm-col-2">
                                        <div class="tf-field style-2">
                                            <input class="tf-field-input tf-input" id="billing-first_name"
                                                name="billing-first_name" type="text" autocomplete="given-name"
                                                placeholder=" " value="{{ old('billing-first_name') }}">
                                            <label class="tf-field-label" for="billing-first_name">Nome próprio</label>
                                        </div>
                                        <div class="tf-field style-2">
                                            <input class="tf-field-input tf-input" id="billing-last_name"
                                                name="billing-last_name" type="text" autocomplete="family-name"
                                                placeholder=" " value="{{ old('billing-last_name') }}">
                                            <label class="tf-field-label" for="billing-last_name">Apelido</label>
                                        </div>
                                    </div>

                                    <fieldset class="tf-field style-2 tf-field--select">
                                        <select id="billing-country" name="billing-country" autocomplete="country"
                                            class="tf-field-input tf-input tf-select">
                                            <option value="" disabled {{ old('billing-country') ? '' : 'selected' }}>
                                                País/Região
                                            </option>
                                            @foreach ($pays as $code => $nom)
                                                <option value="{{ $code }}"
                                                    {{ old('billing-country') == $code ? 'selected' : '' }}>
                                                    {{ $code === 'ES' ? 'Espanha' : ($code === 'PT' ? 'Portugal' : $nom) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label class="tf-field-label is-floated" for="billing-country">País/Região</label>
                                    </fieldset>

                                    <fieldset class="tf-field style-2">
                                        <input class="tf-field-input tf-input" id="billing-address_1"
                                            name="billing-address_1" type="text" autocomplete="address-line1"
                                            placeholder=" " value="{{ old('billing-address_1') }}">
                                        <label class="tf-field-label" for="billing-address_1">Morada</label>
                                    </fieldset>

                                    <fieldset class="tf-field style-2">
                                        <input class="tf-field-input tf-input" id="billing-address_2"
                                            name="billing-address_2" type="text" autocomplete="address-line2"
                                            placeholder=" " value="{{ old('billing-address_2') }}">
                                        <label class="tf-field-label" for="billing-address_2">Apartamento, andar… (opcional)</label>
                                    </fieldset>

                                    <div class="tf-grid-layout gap-16 sm-col-2">
                                        <fieldset class="tf-field style-2">
                                            <input class="tf-field-input tf-input" id="billing-city"
                                                name="billing-city" type="text" autocomplete="address-level2"
                                                placeholder=" " value="{{ old('billing-city') }}">
                                            <label class="tf-field-label" for="billing-city">Localidade</label>
                                        </fieldset>
                                        <fieldset class="tf-field style-2">
                                            <input class="tf-field-input tf-input" id="billing-postcode"
                                                name="billing-postcode" type="text" autocomplete="postal-code"
                                                placeholder=" " value="{{ old('billing-postcode') }}">
                                            <label class="tf-field-label" for="billing-postcode">Código postal</label>
                                        </fieldset>
                                    </div>

                                    <fieldset class="tf-field style-2">
                                        <input class="tf-field-input tf-input" id="billing-phone"
                                            name="billing-phone" type="tel" autocomplete="tel"
                                            placeholder=" " value="{{ old('billing-phone') }}">
                                        <label class="tf-field-label" for="billing-phone">Telemóvel (opcional)</label>
                                    </fieldset>
                                </div>
                            </div>

                            {{-- Payment --}}
                            <div class="box-ip-payment">
                                <div class="title">
                                    <h6 class="mb-4">Pagamento</h6>
                                    <p class="text-caption cl-text-4">Todas as transações são seguras e encriptadas.</p>
                                </div>
                                <fieldset class="mb-12">
                                    <label for="bank-transfer" class="check-payment">
                                        <input type="radio" id="bank-transfer" class="tf-check-rounded style-small"
                                            name="payment-ui" value="bacs" checked>
                                        <span class="text-payment text-caption">Transferência bancária</span>
                                    </label>
                                </fieldset>
                                <p class="text-caption cl-text-4 mb-16">
                                    Efetue o pagamento a partir da sua conta bancária. Indique o seu nome e o número
                                    da encomenda na descrição. A encomenda é preparada assim que recebermos os fundos.
                                    Receberá os dados bancários por email e na página de confirmação.
                                    <a href="{{ route('politicaDePagamento') }}" class="link-underline">Política de pagamento</a>
                                </p>

                                <div class="checkbox-wrap gap-8 mb-16">
                                    <input type="checkbox" class="tf-check" id="add-note-checkbox">
                                    <label for="add-note-checkbox" class="text-caption">
                                        Adicionar uma nota à encomenda (acessos, horário…)
                                    </label>
                                </div>
                                <fieldset class="tf-field style-2 area" id="order-notes-wrap" hidden>
                                    <textarea id="order-notes-textarea" class="tf-field-input tf-input" rows="3"
                                        placeholder=" ">{{ old('order_notes') }}</textarea>
                                    <label class="tf-field-label" for="order-notes-textarea">Notas de entrega</label>
                                </fieldset>

                                <div class="checkbox-wrap gap-8 mt-16">
                                    <input type="checkbox" class="tf-check" id="terms-checkbox" name="terms_checkbox"
                                        value="1" {{ old('terms_checkbox') ? 'checked' : '' }} required>
                                    <label for="terms-checkbox" class="text-caption">
                                        Ao continuar, aceito as
                                        <a href="{{ route('condicoes-gerais-de-venda-cgv') }}" target="_blank" rel="noopener" class="link-underline">condições gerais de venda</a>
                                        e a
                                        <a href="{{ route('politica-de-privacidade') }}" target="_blank" rel="noopener" class="link-underline">política de privacidade</a>.
                                    </label>
                                </div>
                                @error('terms_checkbox')
                                    <span class="lv-field-error">{{ $message }}</span>
                                @enderror

                                <p class="text-caption cl-text-main mt-16">
                                    Os seus dados pessoais serão utilizados para processar a encomenda e melhorar a sua
                                    experiência, conforme a nossa
                                    <a href="{{ route('politica-de-privacidade') }}" class="link-underline">política de privacidade</a>.
                                </p>

                                <div class="btn-order checkout-submit">
                                    <button type="submit" id="submit-order" class="tf-btn animate-btn size3 rounded-6 w-100">
                                        <span class="lv-btn__text">Finalizar compra · {{ $formattedTotalPrice }} €</span>
                                    </button>
                                    <a href="{{ route('carrinho') }}" class="tf-btn-line back-cart">Voltar ao carrinho</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="tf-page-cart-sidebar sticky-top">
                            <div class="cart-box order-box">
                                <h6 class="title cl-text-main">No seu carrinho</h6>
                                <ul class="list-order-product">
                                    @foreach ($cart as $productId => $item)
                                        @php
                                            $rawPrice = is_string($item['price'] ?? null)
                                                ? (float) str_replace([',', ' '], '', $item['price'])
                                                : (float) ($item['price'] ?? 0);
                                            $itemQuantity = (int) ($item['quantity'] ?? 0);
                                            $itemTotal = $rawPrice * $itemQuantity;
                                        @endphp
                                        <li class="order-item">
                                            <figure class="img-product">
                                                @if (!empty($item['image']))
                                                    <img src="{{ asset($item['image']) }}"
                                                        alt="{{ $item['title'] }}" width="72" height="94" loading="lazy">
                                                @endif
                                                <span class="quantity">{{ $itemQuantity }}</span>
                                            </figure>
                                            <div class="content">
                                                <div class="info">
                                                    <p class="name text-caption fw-medium cl-text-main">{{ $item['title'] }}</p>
                                                    <span class="variant text-extra-small fw-medium">IVA incluído</span>
                                                </div>
                                                <span class="price text-caption fw-medium">
                                                    {{ number_format($itemTotal, 2, ',', ' ') }} €
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <ul class="list-total cl-text-main">
                                    <li class="total-item text-caption d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span class="price-sub fw-medium">{{ $formattedTotalPrice }} €</span>
                                    </li>
                                    <li class="total-item text-caption d-flex justify-content-between">
                                        <span>Envio (PT Continental):</span>
                                        <span class="price-ship fw-medium">Grátis</span>
                                    </li>
                                </ul>
                                <div class="subtotal h6 d-flex justify-content-between cl-text-main">
                                    <span>Total:</span>
                                    <span class="total-price-order">{{ $formattedTotalPrice }} €</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
            const $notesWrap = $('#order-notes-wrap');
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
                    $notesWrap.prop('hidden', false).slideDown(150);
                    $notesTextarea.trigger('focus');
                } else {
                    $notesWrap.slideUp(150, function() {
                        $notesWrap.prop('hidden', true);
                    });
                }
            });

            if ($notesTextarea.val().trim() !== '') {
                $addNote.prop('checked', true);
                $notesWrap.prop('hidden', false).show();
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
                        $el.closest('.tf-field').append(
                            '<span class="lv-field-error js-client">Este campo é obrigatório</span>');
                    }
                });

                const email = $('#email').val();
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    isValid = false;
                    $('#email').addClass('is-invalid');
                    if (!$('#email').closest('.tf-field').find('.lv-field-error').length) {
                        $('#email').closest('.tf-field').append(
                            '<span class="lv-field-error js-client">Introduza um email válido</span>');
                    }
                }

                if (!$('#terms-checkbox').is(':checked')) {
                    isValid = false;
                    $('#terms-checkbox').addClass('is-invalid');
                    if (!$('#terms-checkbox').closest('.checkbox-wrap').next('.lv-field-error').length) {
                        $('#terms-checkbox').closest('.checkbox-wrap').after(
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
