@extends('layouts.app')

@php
    $fmtFrom = function ($from) {
        if (! $from) {
            return null;
        }
        $value = (float) $from;
        $formatted = abs($value - round($value)) < 0.01
            ? number_format($value, 0, ',', ' ')
            : number_format($value, 2, ',', ' ');

        return 'desde '.$formatted.' €';
    };

    $heroImage = 'wp-content/uploads/2025/10/lenha-paletes-hero.jpg';
    $heroPanelImage = 'wp-content/uploads/2025/10/lenha-palete-stack.jpg';
    $bannerImage = 'wp-content/uploads/2025/10/765424359870807621.jpg';
    $weekImage = 'wp-content/uploads/2025/10/lenha-pellets-escolha.jpg';
    $marqueeItems = collect($topbarMessages ?? [])->pluck('text')->filter()->values();
@endphp

@section('title', config('seo.home.title'))
@section('meta_description', config('seo.home.description'))
@section('canonical', route('home'))
@section('og_image', asset($heroImage))

@push('styles')
    @vite(['resources/css/home.css'])
@endpush

@section('content')
    @include('layouts.partials.navbar.public')

    <div class="lv-home">
        <section class="lv-home__intro" aria-label="Destaques">
            <div class="lv-home__full">
                <article class="lv-home-hero">
                    <div class="lv-home-hero__media">
                        <picture>
                            <source
                                type="image/webp"
                                srcset="{{ asset('wp-content/uploads/2025/10/lenha-paletes-hero.webp') }} 735w,
                                        {{ asset('wp-content/uploads/2025/10/lenha-paletes-hero-2x.webp') }} 1470w"
                                sizes="(min-width: 900px) 50vw, 100vw"
                            >
                            <img
                                src="{{ asset($heroImage) }}"
                                srcset="{{ asset($heroImage) }} 735w,
                                        {{ asset('wp-content/uploads/2025/10/lenha-paletes-hero-2x.jpg') }} 1470w"
                                sizes="(min-width: 900px) 50vw, 100vw"
                                alt="Paletes de lenha seca Naturalenha prontas para entrega"
                                width="735"
                                height="766"
                                fetchpriority="high"
                                decoding="async"
                            >
                        </picture>
                    </div>
                    <div class="lv-home-hero__content">
                        <img
                            class="lv-home-hero__content-bg"
                            src="{{ asset($heroPanelImage) }}"
                            alt=""
                            width="736"
                            height="981"
                            decoding="async"
                        >
                        <p class="lv-home-hero__kicker">Aquecimento ao domicílio</p>
                        <h1 class="lv-home-hero__title">Pellets, lenha e calor em casa</h1>
                        <p class="lv-home-hero__desc">
                            Expedimos a partir de Palmela, com envio grátis em Portugal Continental.
                        </p>
                        <a href="{{ route('loja') }}" class="lv-home-btn lv-home-btn--solid">Ver a loja</a>
                    </div>
                </article>

                @if ($collections->isNotEmpty())
                    <div class="lv-home-collections">
                        @foreach ($collections as $collection)
                            <article class="lv-home-cls">
                                <a href="{{ $collection['url'] }}" class="lv-home-cls__link">
                                    <div class="lv-home-cls__media">
                                        <img
                                            src="{{ asset($collection['image']) }}"
                                            alt=""
                                            width="604"
                                            height="342"
                                            loading="lazy"
                                            decoding="async"
                                            @if (!empty($collection['image_position']))
                                                style="object-position: {{ $collection['image_position'] }}"
                                            @endif
                                        >
                                    </div>
                                    <div class="lv-home-cls__body">
                                        @if ($fmtFrom($collection['from']))
                                            <p class="lv-home-cls__badge">{{ $fmtFrom($collection['from']) }}</p>
                                        @endif
                                        <h2 class="lv-home-cls__title">{{ $collection['title'] }}</h2>
                                        <p class="lv-home-cls__desc">{{ $collection['desc'] }}</p>
                                        <span class="lv-home-btn lv-home-btn--ghost">
                                            Comprar
                                            <svg width="12" height="12" viewBox="0 0 12 12" aria-hidden="true" focusable="false">
                                                <path d="M2 10 10 2M4 2h6v6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        @if ($homeCategories->isNotEmpty())
            <section class="lv-home__section" aria-labelledby="lv-home-cats-title">
                <div class="lv-container">
                    <header class="lv-home-heading lv-home-heading--center">
                        <h2 id="lv-home-cats-title" class="lv-home-heading__title">Comprar por categoria</h2>
                        <p class="lv-home-heading__desc">Pellets, lenha, fogões e caldeiras para aquecer a sua casa.</p>
                    </header>
                    <div class="lv-home-cats">
                        @foreach ($homeCategories as $category)
                            <a href="{{ $category['url'] }}" class="lv-home-cat">
                                <span class="lv-home-cat__image">
                                    <img
                                        src="{{ asset($category['image']) }}"
                                        alt=""
                                        width="180"
                                        height="180"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </span>
                                <span class="lv-home-cat__name">{{ $category['label'] }}</span>
                                <span class="lv-home-cat__count">
                                    {{ $category['count'] }} {{ $category['count'] === 1 ? 'produto' : 'produtos' }}
                                </span>
                            </a>
                        @endforeach
                        <a href="{{ route('loja') }}" class="lv-home-cat lv-home-cat--all">
                            <span class="lv-home-cat__image lv-home-cat__image--all" aria-hidden="true">
                                <span>Loja</span>
                            </span>
                            <span class="lv-home-cat__name">Ver tudo</span>
                            <span class="lv-home-cat__count">Catálogo completo</span>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        @if ($dealProducts->isNotEmpty())
            <section class="lv-home__section lv-home__section--tight" aria-labelledby="lv-home-deals-title">
                <div class="lv-container">
                    <header class="lv-home-heading lv-home-heading--row">
                        <h2 id="lv-home-deals-title" class="lv-home-heading__title">Promoções em destaque</h2>
                        <a href="{{ route('loja') }}" class="lv-home-heading__link">Ver a loja</a>
                    </header>
                    <div class="lv-product-grid">
                        @foreach ($dealProducts as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="lv-home__banner-wrap" aria-labelledby="lv-home-banner-title">
            <div class="lv-home__full">
                <article class="lv-home-banner">
                    <div class="lv-home-banner__media">
                        <img
                            src="{{ asset($bannerImage) }}"
                            alt="Armazém de lenha Naturalenha em Palmela"
                            width="1840"
                            height="499"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                    <div class="lv-home-banner__content">
                        <p class="lv-home-banner__kicker">Portugal Continental</p>
                        <h2 id="lv-home-banner-title" class="lv-home-banner__title">Envio grátis até à sua porta</h2>
                        <p class="lv-home-banner__desc">
                            Pellets, lenha e equipamentos entregues a pé de camião, a partir de Palmela.
                        </p>
                        <a href="{{ route('politicaDeEntrega') }}" class="lv-home-btn lv-home-btn--solid">Política de entrega</a>
                    </div>
                </article>
                <div class="lv-home-marquee" aria-hidden="true">
                    <div class="lv-home-marquee__track">
                        @for ($i = 0; $i < 2; $i++)
                            @foreach ($marqueeItems as $item)
                                <span class="lv-home-marquee__item">{{ $item }}</span>
                                <span class="lv-home-marquee__dot"></span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        @if ($promoProducts->isNotEmpty())
            <section class="lv-home__section" aria-labelledby="lv-home-week-title">
                <div class="lv-container">
                    <header class="lv-home-heading lv-home-heading--center">
                        <h2 id="lv-home-week-title" class="lv-home-heading__title">Escolha da semana</h2>
                        <p class="lv-home-heading__desc">Fogões, caldeiras e pellets com o preço atual da loja.</p>
                    </header>
                    <div class="lv-home-week">
                        <article class="lv-home-week__promo">
                            <img
                                src="{{ asset($weekImage) }}"
                                alt="Paletes de lenha e sacos de pellets prontos para envio"
                                width="736"
                                height="552"
                                loading="lazy"
                                decoding="async"
                            >
                            <div class="lv-home-week__promo-body">
                                <p class="lv-home-week__kicker">A partir de Palmela</p>
                                <p class="lv-home-week__promo-title">Aquecimento com envio grátis</p>
                                <a href="{{ route('loja') }}" class="lv-home-btn lv-home-btn--solid">Ver promoções</a>
                            </div>
                        </article>
                        <div class="lv-home-week__grid">
                            @foreach ($promoProducts as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section class="lv-home__trust" aria-label="Vantagens Naturalenha">
            <div class="lv-container">
                <ul class="lv-home-trust">
                    <li class="lv-home-trust__item">
                        <span class="lv-home-trust__icon" aria-hidden="true">
                            <i class="tb-icon tb-icon-free-delivery"></i>
                        </span>
                        <div>
                            <p class="lv-home-trust__title">Envio grátis</p>
                            <p class="lv-home-trust__text">Portugal Continental, a pé de camião.</p>
                        </div>
                    </li>
                    <li class="lv-home-trust__item">
                        <span class="lv-home-trust__icon" aria-hidden="true">
                            <i class="tb-icon tb-icon-flexible-payment"></i>
                        </span>
                        <div>
                            <p class="lv-home-trust__title">Pagamento seguro</p>
                            <p class="lv-home-trust__text">Transferência bancária confirmada.</p>
                        </div>
                    </li>
                    <li class="lv-home-trust__item">
                        <span class="lv-home-trust__icon" aria-hidden="true">
                            <i class="tb-icon tb-icon-support-24"></i>
                        </span>
                        <div>
                            <p class="lv-home-trust__title">14 dias para devolver</p>
                            <p class="lv-home-trust__text">Nos termos da lei, produtos não instalados.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </div>

    @include('layouts.partials.footer.public')
@endsection

@push('scripts')
    @include('section.modeldetail')
@endpush
