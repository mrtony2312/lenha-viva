@extends('layouts.app')

@section('title', __('Política de pagamento'))
@section('meta_description', 'Política de pagamento Naturalenha: transferência bancária, confirmação da encomenda, IVA incluído e ligação segura SSL.')
@section('canonical', route('politicaDePagamento'))

@push('styles')
@endpush

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div id="tbay-main-content">
        <section id="tbay-breadcrumb" class="tbay-breadcrumb  breadcrumbs-text active-nav-right show-title">
            <div class="container">
                <div class="breadscrumb-inner">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}" class="active">Início</a> </li>
                        <li class="active">Página</li>
                    </ol>
                </div>
            </div>
        </section>
        <div class="title-not-breadcrumbs">
            <div class="container">
                <h1 class="page-title">Política de pagamento</h1>
            </div>
        </div>
        <section id="main-container" class="container">
            <div class="row ">
                <div id="main-content" class="main-page col-12">
                    <div id="main" class="site-main">

                        <p>A {{ config('company.legal_name') }} aceita o pagamento das encomendas efetuadas em <a href="{{ route('home') }}">{{ config('company.website') }}</a> por <strong>transferência bancária</strong>. Os preços apresentados no site estão em euros, com IVA incluído.</p>

                        <h2>1. Método de pagamento</h2>
                        <p>O único método de pagamento disponível no checkout é a transferência bancária. Não pedimos nem guardamos dados de cartão de crédito ou débito.</p>
                        <p>Depois de confirmar a encomenda, recebe por e-mail os dados bancários (titular, IBAN e valor) e o número da encomenda. Indique o seu nome e o número da encomenda na descrição da transferência.</p>
                        <p>Quaisquer comissões cobradas pelo banco do cliente são da responsabilidade do cliente. A encomenda só é preparada após a confirmação da receção do valor integral.</p>

                        <h2>2. Momento em que o contrato se considera celebrado</h2>
                        <p>A encomenda fica registada quando a submete no site. O contrato de compra considera-se concluído quando recebemos o pagamento integral. Se o pagamento não for recebido num prazo razoável, a encomenda pode ser cancelada.</p>

                        <h2>3. Segurança da ligação</h2>
                        <p>O site {{ config('company.website') }} utiliza uma ligação HTTPS com certificado SSL. Os dados que envia (morada, contacto e dados da encomenda) são transmitidos de forma cifrada. Um cadeado na barra de endereço do browser indica que a ligação é segura.</p>

                        <h2>4. Alojamento</h2>
                        <p>O website está alojado numa infraestrutura profissional, com atualizações regulares de segurança.</p>

                        <h2>5. Faturação</h2>
                        <p>A fatura é emitida em nome da {{ config('company.legal_name') }}, NIF {{ config('company.nif') }} (IVA {{ config('company.vat') }}). Se necessitar de fatura com NIF de empresa, indique-o nas notas da encomenda ou contacte-nos.</p>

                        <p>Dúvidas: <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a> · <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a></p>

                        <figure class="wp-block-image size-large is-resized lv-policy__logo">
                            <img loading="lazy" decoding="async" width="342" height="160"
                                src="{{ asset(config('company.logo')) }}"
                                alt="{{ config('company.legal_name') }}" style="width:280px;height:auto" />
                        </figure>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('layouts.partials.footer.public')
@endsection

@push('scripts')
@endpush
