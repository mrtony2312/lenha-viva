@extends('layouts.app')

@php
    $seoPriceNum = \App\Support\MerchantCatalog::cleanPrice($product['price'] ?? 0);
    $seoPrice = number_format($seoPriceNum, 2, '.', '');
    $seoImages = collect(\App\Support\MerchantCatalog::images($product))
        ->map(fn ($image) => asset($image))
        ->values();
    $seoCategoryLabel = \App\Support\CategoryLabels::label($product['category']);
    $seoBrand = \App\Support\MerchantCatalog::brand($product);
    $seoSku = trim((string) ($product['ref'] ?? ''));
    // Duplicate listings point at the primary product so Google indexes a single URL.
    $seoCanonical = route('product.show', ['slug' => $product['canonical_slug'] ?? $product['slug']]);
    $seoPlainDescription = \Illuminate\Support\Str::limit(
        \App\Support\MerchantCatalog::plainText((string) ($product['description'] ?? ($product['short_description'] ?? $product['title']))),
        500,
    );

    $seoProductSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['title'],
        'description' => $seoPlainDescription,
        'image' => $seoImages->all(),
        'sku' => $seoSku !== '' ? $seoSku : (string) $product['id'],
        'brand' => [
            '@type' => 'Brand',
            'name' => $seoBrand,
        ],
        'category' => $seoCategoryLabel,
    ];

    if ($seoSku !== '') {
        $seoProductSchema['mpn'] = $seoSku;
    }

    // A product without a price would otherwise be published as a 0 € offer.
    if ($seoPriceNum > 0) {
        $seoProductSchema['offers'] = [
            '@type' => 'Offer',
            'url' => $seoCanonical,
            'price' => $seoPrice,
            'priceCurrency' => 'EUR',
            'availability' => ! empty($product['in_stock']) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => [
                '@type' => 'Organization',
                'name' => config('company.legal_name'),
                'url' => config('company.website'),
            ],
            'shippingDetails' => [
                '@type' => 'OfferShippingDetails',
                'shippingRate' => [
                    '@type' => 'MonetaryAmount',
                    'value' => '0',
                    'currency' => 'EUR',
                ],
                'shippingDestination' => [
                    ['@type' => 'DefinedRegion', 'addressCountry' => 'PT'],
                ],
                'deliveryTime' => [
                    '@type' => 'ShippingDeliveryTime',
                    'handlingTime' => [
                        '@type' => 'QuantitativeValue',
                        'minValue' => (int) config('merchant.min_handling_time', 1),
                        'maxValue' => (int) config('merchant.max_handling_time', 2),
                        'unitCode' => 'DAY',
                    ],
                    'transitTime' => [
                        '@type' => 'QuantitativeValue',
                        'minValue' => (int) config('merchant.min_transit_time', 3),
                        'maxValue' => (int) config('merchant.max_transit_time', 5),
                        'unitCode' => 'DAY',
                    ],
                ],
            ],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => ['PT'],
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays' => 14,
                'returnMethod' => 'https://schema.org/ReturnByMail',
                'returnFees' => 'https://schema.org/ReturnShippingFees',
                'merchantReturnLink' => route('politicaDeReembolso'),
            ],
        ];
    }

    $seoBreadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Loja', 'item' => route('loja')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $seoCategoryLabel, 'item' => \App\Support\CategoryLabels::route($product['category'])],
            ['@type' => 'ListItem', 'position' => 4, 'name' => $product['title'], 'item' => $seoCanonical],
        ],
    ];

    $seoJsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

    $priceNum = (float) str_replace(',', '', $product['price'] ?? 0);
    $oldPriceNum = (float) str_replace(',', '', $product['old_price'] ?? 0);
    $hasDiscount = $oldPriceNum > $priceNum && $priceNum > 0;
    $discountPct = $hasDiscount ? (int) round((($oldPriceNum - $priceNum) / $oldPriceNum) * 100) : 0;
    $inWishlist = in_array($product['id'], array_keys(Session::get('wishlist', [])));
    $galleryImages = collect($product['images'] ?? [])->filter()->values();
    if (! empty($product['hover_image']) && ! $galleryImages->contains($product['hover_image'])) {
        $galleryImages->push($product['hover_image']);
    }
    $galleryImages = $galleryImages->values();
    $mainImage = $galleryImages->first();
@endphp

@section('title', $product['seo_title'] ?? $product['title'])
@section('meta_description', $product['seo_description'] ?? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($product['short_description'] ?? ($product['description'] ?? $product['title'])))), 155))
@section('og_type', 'product')
@section('canonical', $seoCanonical)
@section('og_image', $seoImages->first() ?? asset($product['hover_image'] ?: config('company.logo')))
@section('og_image_alt', $product['title'])

@push('head')
    @if((float) $seoPrice > 0)
        <meta property="product:price:amount" content="{{ $seoPrice }}">
        <meta property="product:price:currency" content="EUR">
    @endif
    <meta property="product:availability" content="{{ $product['in_stock'] ? 'in stock' : 'out of stock' }}">
    <script type="application/ld+json">{!! json_encode($seoProductSchema, $seoJsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($seoBreadcrumbSchema, $seoJsonFlags) !!}</script>
@endpush

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div class="lv-product">
        <nav class="lv-product__crumbbar" aria-label="Navegação estrutural">
            <div class="lv-container">
                <ol class="lv-product__crumbs">
                    <li><a href="{{ route('home') }}">Início</a></li>
                    <li aria-hidden="true" class="lv-product__crumb-dot"></li>
                    <li><a href="{{ route('loja') }}">Loja</a></li>
                    <li aria-hidden="true" class="lv-product__crumb-dot"></li>
                    <li><a href="{{ \App\Support\CategoryLabels::route($product['category']) }}">{{ $seoCategoryLabel }}</a></li>
                    <li aria-hidden="true" class="lv-product__crumb-dot"></li>
                    <li aria-current="page">{{ $product['title'] }}</li>
                </ol>
            </div>
        </nav>

        <section class="lv-product__single">
            <div class="lv-container">
                <div class="lv-product__layout">
                    <div class="lv-gallery {{ $galleryImages->count() > 1 ? 'lv-gallery--thumbs' : '' }}">
                        @if($galleryImages->count() > 1)
                            <div class="lv-gallery__thumbs" role="list">
                                @foreach($galleryImages as $index => $image)
                                    <button type="button"
                                        class="lv-gallery__thumb {{ $index === 0 ? 'is-active' : '' }}"
                                        data-src="{{ asset($image) }}"
                                        aria-label="Ver imagem {{ $index + 1 }} de {{ $galleryImages->count() }}">
                                        <img src="{{ asset($image) }}" alt="{{ $product['title'] }} — imagem {{ $index + 1 }}" width="100" height="100" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="lv-gallery__main">
                            @if($galleryImages->count() > 1)
                                <button type="button" class="lv-gallery__arrow lv-gallery__arrow--prev" aria-label="Imagem anterior">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                            @endif
                            <img id="lv-gallery-main-img"
                                src="{{ asset($mainImage) }}"
                                alt="{{ $product['title'] }}"
                                width="640"
                                height="640">
                            @if($galleryImages->count() > 1)
                                <button type="button" class="lv-gallery__arrow lv-gallery__arrow--next" aria-label="Imagem seguinte">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="lv-product__info">
                        <p class="lv-product__badge">{{ $seoCategoryLabel }}</p>
                        <h1 class="lv-product__title">{{ $product['title'] }}</h1>

                        <div class="lv-product__price-row">
                            <span class="lv-product__price {{ $hasDiscount ? 'lv-product__price--sale' : '' }}">{{ $product['price'] }} €</span>
                            @if($hasDiscount)
                                <span class="lv-product__price-old">{{ $product['old_price'] }} €</span>
                                <span class="lv-product__sale">-{{ $discountPct }}%</span>
                            @endif
                            <span class="lv-product__price-note">IVA incluído</span>
                        </div>

                        <p class="lv-product__shipping">
                            <a href="{{ route('politicaDeEntrega') }}">Envio</a>
                            grátis em Portugal Continental (após confirmação do pagamento). Açores e Madeira sob consulta.
                            <strong>Não enviamos para Espanha nem para outros países.</strong>
                        </p>
                        <p class="lv-product__payment-note">
                            Pagamento por <a href="{{ route('politicaDePagamento') }}">transferência bancária</a>
                            · <a href="{{ route('politicaDeReembolso') }}">14 dias para devolver</a>
                            · Vendedor: {{ config('company.legal_name') }} (NIF {{ config('company.nif') }})
                        </p>

                        @if(!empty($product['short_description']))
                            <p class="lv-product__desc">{!! nl2br(e($product['short_description'])) !!}</p>
                        @endif

                        @if(!empty($product['color']))
                            <div class="lv-product__variant">
                                <p class="lv-product__variant-label">
                                    <span>Cor:</span> {{ $product['color'] }}
                                </p>
                            </div>
                        @endif

                        <form class="lv-product__buy cart" action="{{ route('cart.add') }}" method="post">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">

                            <div class="lv-product__qty-row" id="lv-product-atc">
                                <div class="lv-product__qty quantity-selector">
                                    <label class="screen-reader-text" for="quantity_{{ $product['id'] }}">Quantidade de {{ $product['title'] }}</label>
                                    <button type="button" class="quantity-m" aria-label="Diminuir quantidade">&minus;</button>
                                    <input type="number" id="quantity_{{ $product['id'] }}" class="quantity-add" name="quantity" value="1" min="1" inputmode="numeric" aria-label="Quantidade do produto">
                                    <button type="button" class="quantity-p" aria-label="Aumentar quantidade">&plus;</button>
                                </div>

                                <a href="javascript:void(0);"
                                   data-product-id="{{ $product['id'] }}"
                                   aria-label="Adicionar ao carrinho: {{ $product['title'] }}"
                                   class="lv-product__atc single_add_to_cart_button ajax_add_to_cart {{ ! $product['in_stock'] ? 'is-disabled' : '' }}"
                                   @if(! $product['in_stock']) aria-disabled="true" @endif>
                                    {{ $product['in_stock'] ? 'Adicionar ao carrinho' : 'Esgotado' }}
                                </a>

                                <button type="button"
                                   class="lv-product__wishlist-btn wishlist-button {{ $inWishlist ? 'wishlist-added' : '' }}"
                                   data-product-id="{{ $product['id'] }}"
                                   data-product-title="{{ $product['title'] }}"
                                   data-product-price="{{ $product['price'] }}"
                                   data-product-image="{{ asset($mainImage) }}"
                                   data-product-slug="{{ $product['slug'] }}"
                                   aria-label="{{ $inWishlist ? 'Na lista de desejos' : 'Adicionar à lista de desejos' }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"></path>
                                    </svg>
                                </button>
                            </div>

                            @if($product['in_stock'])
                                <a href="javascript:void(0);" class="lv-product__buynow" id="lv-buy-now">Comprar agora</a>
                            @endif
                        </form>

                        <div class="lv-product__delivery">
                            <div class="lv-product__delivery-item">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <rect x="1" y="7" width="15" height="10" rx="1.5"/>
                                    <path d="M16 10h4l3 3v4h-7"/>
                                    <circle cx="6.5" cy="18.5" r="1.5"/>
                                    <circle cx="18.5" cy="18.5" r="1.5"/>
                                </svg>
                                <p>Prazo habitual: <strong>3–5 dias úteis após a confirmação do pagamento</strong> (Portugal Continental)</p>
                            </div>
                            <span class="lv-product__delivery-sep" aria-hidden="true"></span>
                            <div class="lv-product__delivery-item">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    <path d="M3.3 7 12 12l8.7-5M12 22V12"/>
                                </svg>
                                <p><strong>Envio grátis</strong> em Portugal Continental. Açores e Madeira: <a href="{{ route('politicaDeEntrega') }}">sob consulta</a>.</p>
                            </div>
                        </div>

                        <div class="lv-product__pickup {{ $product['in_stock'] ? 'is-in' : 'is-out' }}">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                @if($product['in_stock'])
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                @endif
                            </svg>
                            <div>
                                <p>
                                    @if($product['in_stock'])
                                        <strong>Em stock</strong> em {{ config('company.address.city') }}. A expedição inicia-se após a confirmação do pagamento por transferência.
                                    @else
                                        <strong>Esgotado de momento.</strong> Contacte-nos para disponibilidade.
                                    @endif
                                </p>
                                <a href="{{ route('contacto') }}">Ver informações da loja</a>
                            </div>
                        </div>

                        <div class="lv-product-acc">
                            @if(!empty($product['description']))
                                <details class="lv-product-acc__item">
                                    <summary class="lv-product-acc__summary">
                                        <span>Descrição</span>
                                        <span class="lv-product-acc__icon" aria-hidden="true"></span>
                                    </summary>
                                    <div class="lv-product-acc__body">
                                        {!! nl2br(e($product['description'])) !!}
                                    </div>
                                </details>
                            @endif

                            <details class="lv-product-acc__item">
                                <summary class="lv-product-acc__summary">
                                    <span>Especificações</span>
                                    <span class="lv-product-acc__icon" aria-hidden="true"></span>
                                </summary>
                                <div class="lv-product-acc__body">
                                    <ul class="lv-product-acc__list">
                                        <li>Referência: {{ $product['ref'] }}</li>
                                        <li>Categoria: {{ $seoCategoryLabel }}</li>
                                        @if(!empty($product['color']))
                                            <li>Cor: {{ $product['color'] }}</li>
                                        @endif
                                        <li>Disponibilidade: {{ $product['in_stock'] ? 'Em stock' : 'Esgotado' }}</li>
                                        <li>IVA incluído no preço apresentado</li>
                                    </ul>
                                </div>
                            </details>

                            <details class="lv-product-acc__item">
                                <summary class="lv-product-acc__summary">
                                    <span>Política de devolução</span>
                                    <span class="lv-product-acc__icon" aria-hidden="true"></span>
                                </summary>
                                <div class="lv-product-acc__body">
                                    <p>Tem 14 dias para exercer o direito de livre resolução, nos termos da lei.</p>
                                    <ul class="lv-product-acc__list">
                                        <li>Prazo: 14 dias após a entrega</li>
                                        <li>Os artigos devem estar por abrir, sem uso e em condições de revenda</li>
                                        <li>O envio em Portugal Continental é gratuito; o reenvio de devolução pode ter custos, salvo falta de conformidade</li>
                                    </ul>
                                    <p><a href="{{ route('politicaDeReembolso') }}">Ler a política de reembolso</a></p>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if($relatedProducts->count() > 0)
            <section class="lv-related" aria-labelledby="lv-related-title">
                <div class="lv-container">
                    <h2 id="lv-related-title" class="lv-related__title">As pessoas também compraram</h2>
                    <div class="lv-product-grid">
                        @foreach($relatedProducts as $relatedProduct)
                            <x-product-card :product="$relatedProduct" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>

    @if($product['in_stock'])
        <div class="lv-product-sticky" id="lv-product-sticky" hidden>
            <div class="lv-container lv-product-sticky__inner">
                <div class="lv-product-sticky__product">
                    <img src="{{ asset($mainImage) }}" alt="{{ $product['title'] }}" width="56" height="56">
                    <p>{{ $product['title'] }}</p>
                </div>
                <div class="lv-product-sticky__actions">
                    <div class="lv-product__qty quantity-selector lv-product__qty--sticky">
                        <button type="button" class="quantity-m" aria-label="Diminuir quantidade">&minus;</button>
                        <input type="number" class="quantity-add" id="quantity_sticky_{{ $product['id'] }}" value="1" min="1" inputmode="numeric" aria-label="Quantidade do produto">
                        <button type="button" class="quantity-p" aria-label="Aumentar quantidade">&plus;</button>
                    </div>
                    <a href="javascript:void(0);"
                       data-product-id="{{ $product['id'] }}"
                       class="lv-product-sticky__atc ajax_add_to_cart"
                       aria-label="Adicionar ao carrinho: {{ $product['title'] }}">
                        Adicionar ao carrinho
                    </a>
                </div>
            </div>
        </div>
    @endif

    @include('layouts.partials.footer.public')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qtyInputs = Array.from(document.querySelectorAll('.lv-product .quantity-add, .lv-product-sticky .quantity-add'));

            function clampQty(value) {
                const n = parseInt(value, 10);
                return isNaN(n) || n < 1 ? 1 : n;
            }

            function syncQty(source) {
                const value = clampQty(source.value);
                qtyInputs.forEach(function (input) {
                    input.value = value;
                });
            }

            document.querySelectorAll('.quantity-selector').forEach(function (selector) {
                const input = selector.querySelector('.quantity-add');
                const minusBtn = selector.querySelector('.quantity-m');
                const plusBtn = selector.querySelector('.quantity-p');
                if (!input) return;

                plusBtn?.addEventListener('click', function () {
                    input.value = clampQty(input.value) + 1;
                    syncQty(input);
                });

                minusBtn?.addEventListener('click', function () {
                    input.value = Math.max(1, clampQty(input.value) - 1);
                    syncQty(input);
                });

                input.addEventListener('input', function () {
                    syncQty(input);
                });
            });

            const mainImg = document.getElementById('lv-gallery-main-img');
            const thumbs = document.querySelectorAll('.lv-gallery__thumb');
            let currentIndex = 0;

            function setActiveImage(index) {
                if (!thumbs.length || !mainImg) return;
                index = (index + thumbs.length) % thumbs.length;
                currentIndex = index;
                const thumb = thumbs[index];
                mainImg.src = thumb.dataset.src;
                thumbs.forEach(function (t) { t.classList.remove('is-active'); });
                thumb.classList.add('is-active');
            }

            thumbs.forEach(function (thumb, index) {
                thumb.addEventListener('click', function () { setActiveImage(index); });
            });

            document.querySelector('.lv-gallery__arrow--prev')?.addEventListener('click', function () {
                setActiveImage(currentIndex - 1);
            });
            document.querySelector('.lv-gallery__arrow--next')?.addEventListener('click', function () {
                setActiveImage(currentIndex + 1);
            });

            const buyNowBtn = document.getElementById('lv-buy-now');
            if (buyNowBtn) {
                buyNowBtn.addEventListener('click', function () {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const quantity = document.querySelector('.quantity-add')?.value || 1;
                    const originalLabel = buyNowBtn.textContent;
                    buyNowBtn.textContent = 'A processar...';
                    buyNowBtn.setAttribute('aria-busy', 'true');

                    fetch("{{ route('cart.add') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            product_id: '{{ $product['id'] }}',
                            quantity: quantity
                        })
                    })
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            if (data.success) {
                                window.location.href = "{{ route('checkout') }}";
                            } else {
                                buyNowBtn.textContent = originalLabel;
                                buyNowBtn.removeAttribute('aria-busy');
                                alert(data.message || 'Não foi possível adicionar o produto ao carrinho.');
                            }
                        })
                        .catch(function () {
                            buyNowBtn.textContent = originalLabel;
                            buyNowBtn.removeAttribute('aria-busy');
                            alert('Erro de ligação.');
                        });
                });
            }

            const atcAnchor = document.getElementById('lv-product-atc');
            const stickyBar = document.getElementById('lv-product-sticky');
            if (atcAnchor && stickyBar && 'IntersectionObserver' in window) {
                stickyBar.hidden = false;
                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        stickyBar.classList.toggle('is-visible', !entry.isIntersecting);
                    });
                }, { threshold: 0.15 });
                observer.observe(atcAnchor);
            }
        });
    </script>
@endpush
