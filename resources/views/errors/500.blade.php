@extends('layouts.app')

@section('title', __('500'))
@section('meta_robots', 'noindex, follow')

@section('content')
    @include('layouts.partials.navbar.public')
    <div id="tbay-main-content" class="mm-page mm-slideout">
        <section id="main-container" class=" container inner page-404">
            <div id="main-content" class="main-page">
                <div class="row">
                    <div class="maia-img-404 col-md-6">
                        <img src="{{ asset('wp-content/themes/maia/images/img-500.jpg') }}" alt="Img 500">
                    </div>
                    <section class="error-404 col-md-6">

                        <h1 class="title-404">OPS!</h1>
                        <h2 class="subtitle-404">Erro 500: Erro interno do servidor</h2>

                        <div class="maia-content-404">
                            <p class="sub-title">Lamentamos, ocorreu um erro inesperado. Por
                                favor, volte à <a href="{{ route('home') }}" class="back">página inicial</a> ou
                                tente novamente mais tarde.</p>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </div>

    @include('layouts.partials.footer.public')

@endsection

