@extends('layouts.app')

@section('title', 'Mapa do site')
@section('meta_description', 'Mapa do site Naturalenha: loja, categorias, produtos, contacto e páginas legais.')
@section('canonical', route('mapa-do-site'))

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div id="tbay-main-content">
        <section id="tbay-breadcrumb" class="tbay-breadcrumb breadcrumbs-text active-nav-right show-title">
            <div class="container">
                <div class="breadscrumb-inner">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}">Início</a></li>
                        <li class="active" aria-current="page">Mapa do site</li>
                    </ol>
                </div>
            </div>
        </section>
        <div class="title-not-breadcrumbs">
            <div class="container">
                <h1 class="page-title">Mapa do site</h1>
            </div>
        </div>

        <div class="lv-sitemap">
            <h2>Loja</h2>
            <ul>
                <li><a href="{{ route('home') }}">Início</a></li>
                <li><a href="{{ route('loja') }}">Todos os produtos</a></li>
                @foreach ($categories as $slug => $name)
                    <li><a href="{{ \App\Support\CategoryLabels::route($slug) }}">{{ $name }}</a></li>
                @endforeach
            </ul>

            <h2>Empresa</h2>
            <ul>
                <li><a href="{{ route('sobre-nos') }}">Sobre nós</a></li>
                <li><a href="{{ route('contacto') }}">Contacto</a></li>
                <li><a href="{{ route('avisos-legais') }}">Avisos legais</a></li>
                <li><a href="{{ route('politicaDeEntrega') }}">Política de entrega</a></li>
                <li><a href="{{ route('politicaDePagamento') }}">Política de pagamento</a></li>
                <li><a href="{{ route('politicaDeReembolso') }}">Política de reembolso</a></li>
                <li><a href="{{ route('condicoes-gerais-de-venda-cgv') }}">Condições gerais de venda</a></li>
                <li><a href="{{ route('termos-e-condicoes-gerais-de-utilizacao-tcg') }}">Termos de utilização</a></li>
                <li><a href="{{ route('politica-de-privacidade') }}">Política de privacidade</a></li>
            </ul>

            <h2>Produtos</h2>
            <ul>
                @foreach ($products as $product)
                    <li>
                        <a href="{{ route('product.show', ['slug' => $product['canonical_slug'] ?? $product['slug']]) }}">
                            {{ $product['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @include('layouts.partials.footer.public')
@endsection
