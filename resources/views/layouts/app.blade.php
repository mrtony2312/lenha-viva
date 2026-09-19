<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @php
        $seoTitle = trim($__env->yieldContent('title'));
        // Past ~60 characters Google truncates the tag, so the brand suffix is dropped.
        $seoFullTitle = match (true) {
            $seoTitle === '' => config('app.name').' — Pellets, lenha e aquecimento ao domicílio',
            mb_strlen($seoTitle) > 47 => $seoTitle,
            default => $seoTitle.' | '.config('app.name'),
        };
        $seoDescription = trim($__env->yieldContent('meta_description'))
            ?: 'Naturalenha: pellets de madeira, lenha e equipamentos de aquecimento com envio grátis em Portugal Continental. Pagamento seguro por transferência bancária.';
        $seoCanonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $seoCanonical = strtok($seoCanonical, '?') ?: $seoCanonical;
        if (! \App\Support\Seo::isThinListingQuery()) {
            $seoPage = (int) request('page', 1);
            if ($seoPage > 1) {
                $seoCanonical .= '?page='.$seoPage;
            }
        }
        $seoOgImage = trim($__env->yieldContent('og_image')) ?: asset(config('company.logo'));
        $seoRobots = \App\Support\Seo::robots(trim($__env->yieldContent('meta_robots')));
        $seoOgType = trim($__env->yieldContent('og_type')) ?: 'website';
        $seoIndexable = ! str_contains(strtolower($seoRobots), 'noindex');
        $seoJsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $orgAddress = [
            '@type' => 'PostalAddress',
            'streetAddress' => trim(config('company.address.line_1').', '.config('company.address.line_2')),
            'addressLocality' => config('company.address.city'),
            'addressRegion' => config('company.address.district'),
            'postalCode' => config('company.address.postcode'),
            'addressCountry' => 'PT',
        ];
        $orgSchema = [
            '@context' => 'https://schema.org',
            '@type' => ['Organization', 'LocalBusiness'],
            '@id' => config('company.website').'#organization',
            'name' => config('company.legal_name'),
            'legalName' => config('company.legal_name'),
            'url' => config('company.website'),
            'logo' => asset(config('company.logo')),
            'image' => asset(config('company.logo')),
            'email' => config('company.email'),
            'telephone' => config('company.phone'),
            'vatID' => config('company.vat'),
            'taxID' => config('company.nif'),
            'address' => $orgAddress,
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Portugal',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => config('company.phone'),
                'email' => config('company.email'),
                'contactType' => 'customer service',
                'availableLanguage' => ['Portuguese'],
                'areaServed' => 'PT',
            ],
        ];
        $graph = [$orgSchema];
        if (request()->routeIs('home')) {
            $graph[] = [
                '@type' => 'WebSite',
                '@id' => config('company.website').'#website',
                'url' => config('company.website'),
                'name' => config('company.brand'),
                'inLanguage' => 'pt-PT',
                'publisher' => ['@id' => config('company.website').'#organization'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => route('loja').'?s={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ];
        }
        $seoGraph = ['@context' => 'https://schema.org', '@graph' => $graph];
    @endphp
    <title>{{ $seoFullTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <meta name="author" content="{{ config('company.legal_name') }}">
    <meta name="theme-color" content="#1b3022">
    <link rel="canonical" href="{{ $seoCanonical }}">
    @if($seoIndexable)
        <link rel="alternate" hreflang="pt-PT" href="{{ $seoCanonical }}">
        <link rel="alternate" hreflang="x-default" href="{{ $seoCanonical }}">
    @endif
    <meta property="og:locale" content="pt_PT">
    <meta property="og:type" content="{{ $seoOgType }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ $seoFullTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoOgImage }}">
    <meta property="og:image:alt" content="{{ trim($__env->yieldContent('og_image_alt')) ?: config('company.legal_name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoFullTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoOgImage }}">
    <script type="application/ld+json">{!! json_encode($seoGraph, $seoJsonFlags) !!}</script>

    <link rel="icon" type="image/png" href="{{ asset(config('company.logo')) }}" sizes="96x96">
    <link rel="shortcut icon" href="{{ asset(config('company.logo')) }}">


    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce/assets/js/frontend/add-to-cart-variation.mind3a6.js') }}"
        id="wc-add-to-cart-variation-js" defer="defer" data-wp-strategy="defer"></script>


    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce/assets/js/frontend/single-product.mind3a6.js') }}"
        id="wc-single-product-js" defer="defer" data-wp-strategy="defer"></script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <link rel='stylesheet' id='maia-theme-fonts-css'
        href='https://fonts.googleapis.com/css?family=Lato%3A400%2C500%2C600%2C700%7CCormorant%20Garamond%3A400%2C500%2C600%2C700&amp;subset=latin%2Clatin-ext&amp;display=swap'
        type='text/css' media='all' />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel='stylesheet' id='lv-artisan-fonts-css'
        href='https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap'
        type='text/css' media='all' />
    <link rel='stylesheet' id='woocommerce-general-css'
        href='{{ asset('wp-content/plugins/woocommerce/assets/css/woocommerce-layoutd3a6.css') }}' type='text/css'
        media='all' />
    <link rel='stylesheet' id='woocommerce-general-css'
        href='{{ asset('wp-content/plugins/woocommerce/assets/css/woocommerced3a6.css') }}' type='text/css'
        media='all' />
    <link rel='stylesheet' id='chaty-front-css-css'
        href="{{ asset('wp-content/plugins/chaty/css/chaty-front.mindd1c.css') }}" type='text/css' media='all' />
    <link rel='stylesheet' id='elementor-frontend-css'
        href="{{ asset('wp-content/plugins/elementor/assets/css/frontend.min4bf6.css') }}" type='text/css'
        media='all' />

    <link rel='stylesheet' id='font-awesome-5-all-css'
        href="{{ asset('wp-content/plugins/elementor/assets/lib/font-awesome/css/all.min4bf6.css') }}" type='text/css'
        media='all' />
    <link rel='stylesheet' id='font-awesome-4-shim-css'
        href="{{ asset('wp-content/plugins/elementor/assets/lib/font-awesome/css/v4-shims.min4bf6.css') }}"
        type='text/css' media='all' />
    <link rel='stylesheet' id='widget-spacer-css'
        href="{{ asset('wp-content/plugins/elementor/assets/css/widget-spacer.min4bf6.css') }}" type='text/css'
        media='all' />
    <link rel='stylesheet' id='widget-icon-box-css'
        href="{{ asset('wp-content/plugins/elementor/assets/css/widget-icon-box.min4bf6.css') }}" type='text/css'
        media='all' />
    <link rel='stylesheet' id='bootstrap-css' href="{{ asset('wp-content/themes/maia/css/bootstrapc721.css') }}"
        type='text/css' media='all' />
    <link rel='stylesheet' id='maia-template-css' href="{{ asset('wp-content/themes/maia/css/template5152.css') }}"
        type='text/css' media='all' />
    <link rel='stylesheet' id='maia-style-css' href="{{ asset('wp-content/themes/maia/style5152.css') }}"
        type='text/css' media='all' />

    <link rel='stylesheet' id='maia-font-tbay-custom-css'
        href="{{ asset('wp-content/themes/maia/css/font-tbay-custom8a54.css') }}" type='text/css' media='all' />
    <link rel='stylesheet' id='simple-line-icons-css'
        href="{{ asset('wp-content/themes/maia/css/simple-line-icons8d5a.css') }}" type='text/css' media='all' />
    <link rel='stylesheet' id='material-design-iconic-font-css'
        href="{{ asset('wp-content/themes/maia/css/material-design-iconic-font3601.css') }}" type='text/css'
        media='all' />
    <link rel='stylesheet' id='animate-css' href="{{ asset('wp-content/themes/maia/css/animate3b71.css') }}"
        type='text/css' media='all' />
    <link rel='stylesheet' id='jquery-treeview-css'
        href="{{ asset('wp-content/themes/maia/css/jquery.treeview8a54.css') }}" type='text/css' media='all' />
    <link rel='stylesheet' id='magnific-popup-css'
        href="{{ asset('wp-content/themes/maia/css/magnific-popupf488.css') }}" type='text/css' media='all' />
    <link rel='stylesheet' id='maia-child-style-css'
        href="{{ asset('wp-content/themes/maia-child/style2be9.css') }}" type='text/css' media='all' />
    <link rel='stylesheet' id='elementor-gf-roboto-css'
        href='https://fonts.googleapis.com/css?family=Roboto:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&amp;display=swap'
        type='text/css' media='all' />
    <link rel='stylesheet' id='elementor-gf-robotoslab-css'
        href='https://fonts.googleapis.com/css?family=Roboto+Slab:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&amp;display=swap'
        type='text/css' media='all' />
    <link rel='stylesheet' id='elementor-gf-cormorantgaramond-css'
        href='https://fonts.googleapis.com/css?family=Cormorant+Garamond:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&amp;display=swap'
        type='text/css' media='all' />
    <link rel='stylesheet' id='elementor-gf-lato-css'
        href='https://fonts.googleapis.com/css?family=Lato:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&amp;display=swap'
        type='text/css' media='all' />

    <script type="text/javascript" src="{{ asset('wp-includes/js/jquery/jquery-migrate.min5589.js') }}"
        id="jquery-migrate-js"></script>

    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/js/easy-autocomplete/jquery.easy-autocomplete.min22ef.js') }}"
        id="easy-autocomplete-js"></script>

    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/by_text/assets/js/husky22ef.js') }}"
        id="woof-husky-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/plugins/revslider/public/js/libs/tptools6266.js') }}"
        id="tp-tools-js" async="async" data-wp-strategy="async"></script>
    <script type="text/javascript" src="{{ asset('wp-content/plugins/revslider/public/js/sr76266.js') }}" id="sr7-js"
        async="async" data-wp-strategy="async"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce/assets/js/jquery-blockui/jquery.blockUI.minf967.js') }}"
        id="wc-jquery-blockui-js" data-wp-strategy="defer"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce/assets/js/js-cookie/js.cookie.min5aa6.js') }}" id="wc-js-cookie-js"
        defer="defer" data-wp-strategy="defer"></script>


    <script type="text/javascript" src="{{ asset('wp-includes/js/underscore.min3ab8.js') }}" id="underscore-js"></script>

    <script type="text/javascript" src="{{ asset('wp-includes/js/wp-util.mind4d0.js') }}" id="wp-util-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/elementor/assets/lib/font-awesome/js/v4-shims.min4bf6.js') }}"
        id="font-awesome-4-shim-js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>

    <link rel='stylesheet' id='widget-image-css'
        href="{{ asset('wp-content/plugins/elementor/assets/css/widget-image.min4bf6.css') }}" type='text/css'
        media='all' />
    <link rel='stylesheet' id='sumoselect-css' href="{{ asset('wp-content/themes/maia/css/sumoselect8a54.css') }}"
        type='text/css' media='all' />
    <link
        href="http://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&amp;family=Lato:wght@400&amp;display=swap"
        rel="stylesheet" property="stylesheet" media="all" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <link rel='stylesheet' id='ion.range-slider-css'
        href="{{ asset('wp-content/plugins/woocommerce-products-filter/js/ion.range-slider/css/ion.rangeSlider22ef.css') }}"
        type='text/css' media='all' />

    <link rel='stylesheet' id='woof_color_html_items-css'
        href="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/color/css/html_types/color22ef.css') }}"
        type='text/css' media='all' />

    @stack('styles')
    @vite(['resources/js/app.js', 'resources/css/app.css', 'resources/css/modern.css'])

    <style>
        /* Forcer l'affichage du bouton "Adicionar" sur mobile */
        .add-cart {
            display: block !important;
            text-align: center;
            margin: 10px 0;
        }

        .add-cart a.button {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #F55F1E;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }

        .add-cart a.button .tb-icon {
            margin-left: 8px;
        }

        /* Optionnel : adapter sur mobile */
        @media (max-width: 768px) {
            .add-cart {
                margin: 15px 0;
            }
        }
    </style>

    <!-- Event snippet for Page vue conversion page -->
<script>
  gtag('event', 'conversion', {'send_to': 'AW-17798780713/RgASCMbum9obEKmuj6dC'});
</script>

    @stack('head')
</head>

<!-- Google tag (gtag.js) + Consent Mode v2 -->
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('consent', 'default', {
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
        analytics_storage: 'denied',
        wait_for_update: 500
    });
    try {
        if (localStorage.getItem('lv_cookie_consent') === 'granted') {
            gtag('consent', 'update', {
                ad_storage: 'granted',
                ad_user_data: 'granted',
                ad_personalization: 'granted',
                analytics_storage: 'granted'
            });
        }
    } catch (e) {}
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-MX4HS3ZTPP"></script>
<script>
    gtag('js', new Date());
    gtag('config', 'G-MX4HS3ZTPP');
    gtag('config', 'AW-17798780713');
</script>

<body>
    <main>

        @yield('content')

    </main>


    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/functions.min5152.js') }}" id="maia-script-js">
    </script>

    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/slick.min8a54.js') }}" id="slick-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/custom-slick.min5152.js') }}"
        id="maia-custom-slick-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/skip-link-fix.min5152.js') }}"
        id="maia-skip-link-fix-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/popper.mind57f.js') }}" id="popper-js">
    </script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/bootstrap.minc721.js') }}" id="bootstrap-js">
    </script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/jquery.autocomplete.min8a54.js') }}"
        id="jquery-autocomplete-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/jquery.magnific-popup.minf488.js') }}"
        id="jquery-magnific-popup-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/js/ion.range-slider/js/ion.rangeSlider.min22ef.js') }}"
        id="ion.range-slider-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/js/tooltip/js/tooltipster.bundle.min22ef.js') }}"
        id="woof_tooltip-js-js"></script>

    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/js/html_types/radio22ef.js') }}"
        id="woof_radio_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/js/html_types/checkbox22ef.js') }}"
        id="woof_checkbox_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/js/html_types/select22ef.js') }}"
        id="woof_select_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/js/html_types/mselect22ef.js') }}"
        id="woof_mselect_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/by_author/js/by_author22ef.js') }}"
        id="woof_by_author_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/by_backorder/js/by_backorder22ef.js') }}"
        id="woof_by_backorder_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/by_featured/js/by_featured22ef.js') }}"
        id="woof_by_featured_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/by_instock/js/by_instock22ef.js') }}"
        id="woof_by_instock_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/by_onsales/js/by_onsales22ef.js') }}"
        id="woof_by_onsales_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/by_sku/js/by_sku22ef.js') }}"
        id="woof_by_sku_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/by_text/assets/js/front22ef.js') }}"
        id="woof_by_text_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/color/js/html_types/color22ef.js') }}"
        id="woof_color_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/image/js/html_types/image22ef.js') }}"
        id="woof_image_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/label/js/html_types/label22ef.js') }}"
        id="woof_label_html_items-js"></script>

    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/select_hierarchy/js/html_types/select_hierarchy22ef.js') }}"
        id="woof_select_hierarchy_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/select_radio_check/js/html_types/select_radio_check22ef.js') }}"
        id="woof_select_radio_check_html_items-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/slider/js/html_types/slider22ef.js') }}"
        id="woof_slider_html_items-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/jquery.waypoints.mincce7.js') }}"
        id="waypoints-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/js/chosen/chosen.jquery22ef.js') }}"
        id="chosen-drop-down-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/jquery.mmenu.minfd3b.js') }}"
        id="jquery-mmenu-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/jquery.treeview.min2fca.js') }}"
        id="jquery-treeview-js"></script>
    <script type="text/javascript" src="{{ asset('wp-content/themes/maia/js/jquery.sumoselect.min5b75.js') }}"
        id="jquery-sumoselect-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/slideout/js/jquery.tabSlideOut22ef.js') }}"
        id="woof-slideout-js-js"></script>
    <script type="text/javascript"
        src="{{ asset('wp-content/plugins/woocommerce-products-filter/ext/slideout/js/slideout22ef.js') }}"
        id="woof-slideout-init-js"></script>

    <script>
        $(document).ready(function() {
            // Vérifier que jQuery est chargé
            if (typeof $ === 'undefined') {
                console.error('ERROR: jQuery n\'est pas chargé!');
                return;
            }

            // Vérifier le token CSRF
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            if (!csrfToken) {
                console.warn('WARNING: Token CSRF non trouvé');
            }

            // Configuration AJAX globale
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            // Initialiser le panier
            initializeCart();

            // ============ FONCTIONS DU MINI-PANIER ============

            // Fonction pour mettre à jour TOUS les mini-paniers
            function updateAllMiniCarts() {


                $.ajax({
                    url: "{{ route('cart.mini.html') }}",
                    type: 'GET',
                    dataType: 'json',
                    timeout: 5000,
                    beforeSend: function() {
                        // Afficher un indicateur de chargement pour desktop
                        $('#mini-cart-content').html(`
                        <div class="mini_cart_content">
                            <div class="mini_cart_inner">
                                <div class="text-center p-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <span class="ms-2">Carregando carrinho...</span>
                                </div>
                            </div>
                        </div>
                    `);

                        // Afficher un indicateur de chargement pour mobile
                        $('#cart-offcanvas-mobile .widget_shopping_cart_content').html(`
                        <div class="mini_cart_content">
                            <div class="mini_cart_inner">
                                <div class="text-center p-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <span class="ms-2">Carregando carrinho...</span>
                                </div>
                            </div>
                        </div>
                    `);
                    },
                    success: function(response) {
                        ;

                        if (response && response.success) {
                            // ============ MISE À JOUR DESKTOP ============
                            $('#mini-cart-content').html(`
                            <div class="mini_cart_content">
                                <div class="mini_cart_inner">
                                    ${response.desktop_html}
                                </div>
                            </div>
                        `);

                            // ============ MISE À JOUR MOBILE ============
                            $('#cart-offcanvas-mobile .widget_shopping_cart_content').html(`
                            <div class="mini_cart_content">
                                <div class="mini_cart_inner">
                                    ${response.mobile_html}
                                </div>
                            </div>
                        `);

                            // Mettre à jour TOUS les compteurs
                            var cartCountElements = $('.mini-cart-items');
                            if (cartCountElements.length) {
                                cartCountElements.each(function() {
                                    $(this).text(response.totalItems || 0);

                                    if (response.totalItems > 0) {
                                        $(this).addClass('has-items').show();
                                    } else {
                                        $(this).removeClass('has-items');
                                    }
                                });
                            }

                            // Ajouter les événements pour les boutons
                            bindAllMiniCartEvents();
                        } else {
                            // Erreur du serveur
                            var errorHtml = `
                            <div class="mini_cart_content">
                                <div class="mini_cart_inner">
                                    <div class="mcart-border">
                                        <div class="alert alert-danger m-2">
                                            Erro ao carregar o carrinho
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                            $('#mini-cart-content').html(errorHtml);
                            $('#cart-offcanvas-mobile .widget_shopping_cart_content').html(errorHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors du chargement du mini-panier:', error);

                        var errorHtml = `
                        <div class="mini_cart_content">
                            <div class="mini_cart_inner">
                                <div class="mcart-border">
                                    <div class="alert alert-warning m-2">
                                        Não foi possível carregar o carrinho. Tente novamente.
                                    </div>
                                    <button class="btn btn-sm btn-primary reload-mini-cart" onclick="location.reload()">
                                        <i class="tb-icon tb-icon-refresh"></i> Recarregar
                                    </button>
                                </div>
                            </div>
                        </div>`;

                        $('#mini-cart-content').html(errorHtml);
                        $('#cart-offcanvas-mobile .widget_shopping_cart_content').html(errorHtml);
                    }
                });
            }

            // Fonction pour lier les événements de TOUS les mini-paniers
            function bindAllMiniCartEvents() {


                // Bouton plus dans tous les mini-paniers
                $(document).off('click', '.mini-cart-plus').on('click', '.mini-cart-plus', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var productId = $(this).data('product-id');
                    var cartType = $(this).data('cart-type') || 'desktop';
                    var input = $(this).closest('.quantity').find(
                        'input.mini-cart-quantity[data-product-id="' + productId + '"]');
                    var currentVal = parseInt(input.val());
                    input.val(currentVal + 1);
                    updateQuantity(productId, currentVal + 1);
                });

                // Bouton moins dans tous les mini-paniers
                $(document).off('click', '.mini-cart-minus').on('click', '.mini-cart-minus', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var productId = $(this).data('product-id');
                    var cartType = $(this).data('cart-type') || 'desktop';
                    var input = $(this).closest('.quantity').find(
                        'input.mini-cart-quantity[data-product-id="' + productId + '"]');
                    var currentVal = parseInt(input.val());

                    if (currentVal > 1) {
                        input.val(currentVal - 1);
                        updateQuantity(productId, currentVal - 1);
                    }
                });

                // Changement direct dans l'input de tous les mini-paniers
                $(document).off('change', '.mini-cart-quantity').on('change', '.mini-cart-quantity', function(e) {
                    e.stopPropagation();
                    var productId = $(this).data('product-id');
                    var cartType = $(this).data('cart-type') || 'desktop';
                    var newQuantity = parseInt($(this).val());

                    if (newQuantity < 1) {
                        $(this).val(1);
                        newQuantity = 1;
                    }

                    updateQuantity(productId, newQuantity);
                });

                // Supprimer un article de tous les mini-paniers
                $(document).off('click', '.mini-cart-remove').on('click', '.mini-cart-remove', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (!confirm('Tem a certeza de que quer remover este artigo do carrinho?')) {
                        return;
                    }

                    var productId = $(this).data('product-id');
                    var cartType = $(this).data('cart-type') || 'desktop';
                    var itemElement = $(this).closest('.mini_cart_item');

                    itemElement.fadeOut(300, function() {
                        $.ajax({
                            url: "{{ route('cart.remove') }}",
                            type: 'POST',
                            data: {
                                product_id: productId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    // Mettre à jour tous les mini-paniers
                                    updateAllMiniCarts();
                                    // Mettre à jour le compteur
                                    updateCartCount(response);
                                    showNotification('Artigo removido do carrinho',
                                        'success');
                                }
                            },
                            error: function() {
                                showNotification('Erro de conexão', 'error');
                                itemElement.show();
                            }
                        });
                    });
                });
            }

            // Mettre à jour le mini-panier desktop quand on clique dessus
            $(document).on('click', '.dropdown-toggle.mini-cart:not(.v2)', function(e) {


                // Attendre un peu avant de mettre à jour pour éviter les conflits
                setTimeout(function() {
                    updateAllMiniCarts();
                }, 100);
            });

            // Mettre à jour le mini-panier mobile quand on clique dessus
            $(document).on('click', '.mini-cart.v2', function(e) {


                // Mettre à jour le contenu mobile avant d'ouvrir l'offcanvas
                updateAllMiniCarts();
            });

            // Mettre à jour quand l'offcanvas mobile s'ouvre
            $('#cart-offcanvas-mobile').on('show.bs.offcanvas', function() {

                updateAllMiniCarts();
            });

            // ============ FONCTIONS EXISTANTES MODIFIÉES ============

            // Fonction pour initialiser le panier
            function initializeCart() {

                $.ajax({
                    url: "{{ route('cart.content') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            // Mettre à jour le compteur
                            updateCartCount(response);
                            // Précharger tous les mini-paniers
                            updateAllMiniCarts();
                        }
                    },
                    error: function() {
                        console.log('Não foi possível carregar o carrinho');
                    }
                });
            }

            // Fonction pour mettre à jour TOUS les compteurs du panier
            function updateCartCount(response) {
                if (response && response.success) {
                    var cartCountElements = $('.mini-cart-items');
                    if (cartCountElements.length) {
                        cartCountElements.each(function() {
                            $(this).text(response.totalItems || 0);

                            if (response.totalItems > 0) {
                                $(this).addClass('has-items').show();
                            } else {
                                $(this).removeClass('has-items');
                            }
                        });
                    }
                }
            }

            // Fonction pour mettre à jour la quantité
            function updateQuantity(productId, quantity) {
                $.ajax({
                    url: "{{ route('cart.update') }}",
                    type: 'POST',
                    data: {
                        product_id: productId,
                        quantity: quantity
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Mettre à jour tous les compteurs
                            updateCartCount(response);
                            // Mettre à jour tous les mini-paniers
                            updateAllMiniCarts();
                            showNotification('Quantidade atualizada', 'success');

                            // Si on est sur la page du panier, recharger
                            if (window.location.pathname === '/carrinho') {
                                setTimeout(function() {
                                    window.location.reload();
                                }, 500);
                            }
                        } else {
                            showNotification('Erro ao atualizar a quantidade', 'error');
                            updateAllMiniCarts(); // Recharger pour resynchroniser
                        }
                    },
                    error: function() {
                        showNotification('Erro de conexão', 'error');
                        updateAllMiniCarts(); // Recharger pour resynchroniser
                    }
                });
            }

            // Gestion du clic sur "Ajouter au panier"
            /* $(document).on('click', '.ajax_add_to_cart', function (e) {
                 e.preventDefault();
                 e.stopPropagation();

                 var button = $(this);
                 var productId = button.data('product-id');

                 if (!productId) {
                     console.error('ERROR: Product ID manquant sur le bouton');
                     showNotification('Erro: ID do produto em falta', 'error');
                     return;
                 }

                 console.log('AJOUT AU PANIER - ID:', productId);

                 // Sauvegarder l'état original
                 var originalHtml = button.html();
                 var originalText = button.find('.title-cart').text();

                 // État de chargement
                 button.html('<span class="title-cart">A carregar...</span>');
                 button.prop('disabled', true).addClass('loading');

                 // Envoyer la requête
                 $.ajax({
                     url: "{{ route('cart.add') }}",
                     type: 'POST',
                     data: {
                         product_id: productId,
                         quantity: 1
                     },
                     dataType: 'json',
                     timeout: 10000,
                     success: function (response) {
                         console.log('REPONSE:', response);

                         if (response && response.success) {
                             // Succès
                             updateCartCount(response);
                             updateAllMiniCarts(); // Mettre à jour TOUS les mini-paniers
                             showNotification(response.message || 'Produto adicionado ao carrinho!', 'success');

                             // Animation de succès
                             button.html('<span class="title-cart">✓ Adicionado!</span>');
                             button.removeClass('loading').addClass('success');

                             // Restaurer après 2 secondes
                             setTimeout(function () {
                                 button.html(originalHtml);
                                 button.prop('disabled', false)
                                     .removeClass('loading success');
                             }, 2000);
                         } else {
                             // Erreur du serveur
                             var errorMsg = response && response.message
                                 ? response.message
                                 : 'Erro ao adicionar ao carrinho';

                             showNotification(errorMsg, 'error');
                             button.html(originalHtml);
                             button.prop('disabled', false).removeClass('loading');
                         }
                     },
                     error: function (xhr, status, error) {
                         console.error('AJAX ERROR:', status, error);

                         var errorMsg = 'Erro de conexão';
                         if (xhr.responseJSON && xhr.responseJSON.message) {
                             errorMsg = xhr.responseJSON.message;
                         } else if (xhr.status === 0) {
                             errorMsg = 'Sem ligação à Internet';
                         } else if (xhr.status === 500) {
                             errorMsg = 'Erro do servidor';
                         }

                         showNotification(errorMsg, 'error');
                         button.html(originalHtml);
                         button.prop('disabled', false).removeClass('loading');
                     }
                 });
             });*/

            // ============ FONCTIONS DE NOTIFICATION ============

            function showNotification(message, type = 'success') {
                // Supprimer les anciennes notifications
                $('.cart-notification').remove();

                // Créer la notification
                var notification = $('<div class="cart-notification"></div>');
                notification.text(message);
                notification.addClass(type);

                // Ajouter au body
                $('body').append(notification);

                // Styles de base
                notification.css({
                    'position': 'fixed',
                    'top': '20px',
                    'right': '20px',
                    'padding': '12px 24px',
                    'border-radius': '4px',
                    'color': 'white',
                    'font-size': '14px',
                    'font-weight': '500',
                    'z-index': '99999',
                    'box-shadow': '0 4px 12px rgba(0,0,0,0.15)',
                    'max-width': '400px',
                    'word-wrap': 'break-word'
                });

                // Couleurs selon le type
                if (type === 'success') {
                    notification.css({
                        'background': '#10b981',
                        'border-left': '4px solid #059669'
                    });
                } else {
                    notification.css({
                        'background': '#ef4444',
                        'border-left': '4px solid #dc2626'
                    });
                }

                // Animation
                notification.hide().fadeIn(300);

                // Supprimer après 4 secondes
                setTimeout(function() {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 4000);
            }

            // Alias pour compatibilité
            function showSuccess(message) {
                showNotification(message, 'success');
            }

            function showError(message) {
                showNotification(message, 'error');
            }

            // ============ GESTION DES ERREURS ============

            // Recharger le mini-panier en cas d'erreur
            $(document).on('click', '.reload-mini-cart', function(e) {
                e.preventDefault();
                updateAllMiniCarts();
            });

            // ============ INITIALISATION ============



            setTimeout(function() {
                updateAllMiniCarts();
            }, 1000);
        });
    </script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function formatPrice(price) {
                var num = typeof price === 'string' ? parseFloat(price) : price;
                if (isNaN(num)) num = 0;

                var parts = num.toFixed(3).split('.');
                var integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                return integerPart + ',' + parts[1];
            }

            function parseFormattedPrice(priceStr) {
                return parseFloat(priceStr.replace(/\s/g, '').replace(',', '.'));
            }

            function showNotification(message, type = 'success') {
                $('.cart-notification').remove();

                var notification = $('<div class="cart-notification ' + type + '"></div>');
                notification.text(message);
                $('body').append(notification);

                setTimeout(function() {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }

            // Fonction pour basculer entre les sections panier vide/rempli
            function toggleCartSections(isEmpty) {
                if (isEmpty) {
                    $('.empty-cart-section').show();
                    $('.filled-cart-section').hide();
                } else {
                    $('.empty-cart-section').hide();
                    $('.filled-cart-section').show();
                }
            }

            function updateCartDisplay(cartData) {
                if (!cartData.cart || Object.keys(cartData.cart).length === 0) {
                    toggleCartSections(true);
                    return;
                }

                $.each(cartData.cart, function(productId, item) {
                    var row = $('.cart-item-row[data-product-id="' + productId + '"]');
                    if (row.length) {
                        row.find('.quantity-input').val(item.quantity);

                        var itemTotal = parseFloat(item.price) * item.quantity;
                        row.find('.item-total').text(formatPrice(itemTotal) + ' €');
                        row.find('.item-total').data('total', itemTotal);
                    }
                });

                var totalPrice = parseFloat(cartData.totalPrice) || 0;
                $('#cart-total-price').text(formatPrice(totalPrice) + ' €');
                $('#cart-total-price').data('total', totalPrice);

                $('.mini-cart-items').text(cartData.totalItems || 0);

                toggleCartSections(false);
            }

            $(document).on('click', '.ajax_add_to_cart', function(e) {
                e.preventDefault();

                var button = $(this);
                var productId = button.data('product-id');

                if (!productId) {
                    showNotification('ID do produto inválido', 'error');
                    return;
                }

                // Quantité par défaut
                var quantity = 1;
                var qtyInput = $('.quantity-add');

                if (qtyInput.length) {
                    var val = parseInt(qtyInput.val(), 10);
                    if (!isNaN(val) && val > 0) {
                        quantity = val;
                    }
                }


                var originalHtml = button.html();

                // 🎭 Simulation loading
                button.html('<i class="tb-icon tb-icon-bag-2"></i>').addClass('loading');

                setTimeout(function() {
                    button.html(originalHtml).removeClass('loading');
                }, 500);

                $.ajax({
                    url: "{{ route('cart.add') }}",
                    type: 'POST',
                    data: {
                        product_id: productId,
                        quantity: quantity
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message);
                            updateCartDisplay(response);

                            // 🔄 Réinitialiser la quantité après ajout
                            if (qtyInput.length) {
                                qtyInput.val(1); // pas 0 → minimum = 1
                            }

                            if ($('.empty-cart-section').is(':visible')) {
                                toggleCartSections(false);
                                setTimeout(function() {
                                    window.location.reload();
                                }, 500);
                            }
                        } else {
                            showNotification(response.message ||
                                'Erro ao adicionar o produto ao carrinho', 'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification('Erro de conexão', 'error');
                        console.error('AJAX Error:', xhr.responseText);
                    }
                });
            });

            // Gestion des quantités
            $(document).on('click', '.quantity-plus', function() {
                var productId = $(this).data('product-id');
                var input = $(this).siblings('.quantity-input');
                var currentVal = parseInt(input.val());
                input.val(currentVal + 1);
                updateQuantity(productId, currentVal + 1);
            });

            $(document).on('click', '.quantity-minus', function() {
                var productId = $(this).data('product-id');
                var input = $(this).siblings('.quantity-input');
                var currentVal = parseInt(input.val());

                if (currentVal > 1) {
                    input.val(currentVal - 1);
                    updateQuantity(productId, currentVal - 1);
                }
            });

            $(document).on('change', '.quantity-input', function() {
                var productId = $(this).data('product-id');
                var newQuantity = parseInt($(this).val());

                if (newQuantity < 1) {
                    $(this).val(1);
                    newQuantity = 1;
                }

                updateQuantity(productId, newQuantity);
            });

            function updateQuantity(productId, quantity) {
                $.ajax({
                    url: "{{ route('cart.update') }}",
                    type: 'POST',
                    data: {
                        product_id: productId,
                        quantity: quantity
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            updateCartDisplay(response);
                            showNotification('Quantidade atualizada');
                        } else {
                            showNotification('Erro ao atualizar a quantidade', 'error');
                            window.location.reload();
                        }
                    },
                    error: function() {
                        showNotification('Erro de conexão', 'error');
                        window.location.reload();
                    }
                });
            }

            // Supprimer un article
            $(document).on('click', '.remove-item', function() {
                if (!confirm('Tem a certeza de que quer remover este artigo do carrinho?')) {
                    return;
                }

                var productId = $(this).data('product-id');
                var row = $(this).closest('.cart-item-row');

                row.fadeOut(300, function() {
                    $.ajax({
                        url: "{{ route('cart.remove') }}",
                        type: 'POST',
                        data: {
                            product_id: productId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                if (response.totalItems === 0) {
                                    toggleCartSections(true);
                                } else {
                                    row.remove();
                                    updateCartDisplay(response);
                                }
                                showNotification('Artigo removido do carrinho');
                            }
                        },
                        error: function() {
                            showNotification('Erro de conexão', 'error');
                            row.show();
                        }
                    });
                });
            });

            // Initialiser l'affichage
            function initializeCart() {
                $.ajax({
                    url: "{{ route('cart.content') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            updateCartDisplay(response);
                        }
                    },
                    error: function() {
                        console.log('Não foi possível carregar o carrinho');
                    }
                });
            }

            // Initialiser au chargement
            initializeCart();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des boutons wishlist
            document.querySelectorAll('.wishlist-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const productId = this.dataset.productId;
                    const productTitle = this.dataset.productTitle;
                    const productPrice = this.dataset.productPrice;
                    const productImage = this.dataset.productImage;
                    const productSlug = this.dataset.productSlug;
                    const isAdded = this.classList.contains('wishlist-added');

                    if (isAdded) {
                        // Retirer de la wishlist
                        removeFromWishlist(productId, this);
                    } else {
                        // Ajouter à la wishlist
                        addToWishlist(productId, productTitle, productPrice, productImage,
                            productSlug, this);
                    }
                });
            });

            function addToWishlist(productId, title, price, image, slug, button) {
                fetch('{{ route('wishlist.add') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            product_title: title,
                            product_price: price,
                            product_image: image,
                            product_slug: slug
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mettre à jour l'apparence du bouton
                            button.classList.add('wishlist-added');
                            button.querySelector('svg').setAttribute('fill', 'red');
                            button.querySelector('.yith-wcwl-add-to-wishlist-button__label').textContent =
                                'Na lista';

                            // Mettre à jour le compteur si présent
                            updateWishlistCount(data.count);

                            // Afficher une notification
                            showNotification('Produto adicionado à lista de desejos', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Ocorreu um erro', 'error');
                    });
            }

            function removeFromWishlist(productId, button) {
                fetch('{{ route('wishlist.remove') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            product_id: productId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mettre à jour l'apparence du bouton
                            button.classList.remove('wishlist-added');
                            button.querySelector('svg').setAttribute('fill', 'none');
                            button.querySelector('.yith-wcwl-add-to-wishlist-button__label').textContent =
                                'Adicionar à lista de desejos';

                            // Mettre à jour le compteur si présent
                            updateWishlistCount(data.count);

                            // Si on est sur la page wishlist, supprimer l'élément
                            if (window.location.pathname.includes('lista-de-desejos')) {
                                const row = document.querySelector(`[data-row-id="${productId}"]`);
                                if (row) {
                                    row.remove();
                                }

                                // Vérifier si la wishlist est vide
                                if (data.count === 0) {
                                    showEmptyWishlist();
                                }
                            }

                            showNotification('Produto removido da lista de desejos', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Ocorreu um erro', 'error');
                    });
            }

            function updateWishlistCount(count) {
                const countElement = document.querySelector('.wishlist-count');
                if (countElement) {
                    countElement.textContent = count;
                }
            }

            function showNotification(message, type) {
                // Créer une notification simple
                const notification = document.createElement('div');
                notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
                notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 15px;
            border-radius: 5px;
            color: white;
            background-color: ${type === 'success' ? '#28a745' : '#dc3545'};
            animation: slideIn 0.3s ease;
        `;
                notification.textContent = message;

                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }

            function showEmptyWishlist() {
                const tbody = document.querySelector('.wishlist-items-wrapper');
                if (tbody) {
                    tbody.innerHTML = `
                <tr class="no-products">
                    <td colspan="5" class="wishlist-empty">Nenhum produto adicionado à lista de desejos</td>
                </tr>
            `;
                }
            }

            // Ajouter les styles pour les animations
            const style = document.createElement('style');
            style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .wishlist-button.wishlist-added {
            opacity: 0.8;
        }
    `;
            document.head.appendChild(style);
        });
    </script>
    @stack('scripts')

    <div id="lv-cookie-banner" class="lv-cookie" hidden role="dialog" aria-labelledby="lv-cookie-title" aria-live="polite">
        <p id="lv-cookie-title" class="lv-cookie__text">
            Utilizamos cookies necessários à loja (carrinho e sessão) e, com o seu consentimento, cookies de análise e publicidade (Google Analytics e Google Ads).
            Consulte a <a href="{{ route('politica-de-privacidade') }}">política de privacidade</a>.
        </p>
        <div class="lv-cookie__actions">
            <button type="button" class="lv-cookie__btn lv-cookie__btn--ghost" data-cookie-consent="denied">Recusar</button>
            <button type="button" class="lv-cookie__btn" data-cookie-consent="granted">Aceitar</button>
        </div>
    </div>
    <script>
        (function () {
            var key = 'lv_cookie_consent';
            var banner = document.getElementById('lv-cookie-banner');
            if (!banner) return;
            var stored = null;
            try { stored = localStorage.getItem(key); } catch (e) {}
            function apply(value) {
                if (typeof gtag === 'function') {
                    var granted = value === 'granted';
                    gtag('consent', 'update', {
                        ad_storage: granted ? 'granted' : 'denied',
                        ad_user_data: granted ? 'granted' : 'denied',
                        ad_personalization: granted ? 'granted' : 'denied',
                        analytics_storage: granted ? 'granted' : 'denied'
                    });
                }
            }
            if (!stored) {
                banner.hidden = false;
            }
            banner.addEventListener('click', function (event) {
                var btn = event.target.closest('[data-cookie-consent]');
                if (!btn) return;
                var value = btn.getAttribute('data-cookie-consent');
                try { localStorage.setItem(key, value); } catch (e) {}
                apply(value);
                banner.hidden = true;
            });
        })();
    </script>

</body>

</html>
