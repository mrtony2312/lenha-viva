@extends('layouts.app')

@section('title', __('Avisos legais'))
@section('meta_description', 'Avisos legais da Naturalenha, Unipessoal, Lda: identificação da empresa, NIF 508162599, morada em Palmela e dados de contacto.')
@section('canonical', route('avisos-legais'))

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
                <h1 class="page-title">Avisos legais</h1>
            </div>
        </div>
        <section id="main-container" class="container">
            <div class="row ">
                <div id="main-content" class="main-page col-12">
                    <div id="main" class="site-main">

                        <p>Informação societária e de identificação do prestador da sociedade da informação, nos termos do Decreto-Lei n.º 7/2004, de 7 de janeiro (comércio eletrónico), e da legislação comercial portuguesa aplicável.</p>

                        <h2>Identificação da empresa</h2>

                        <p>
                            <strong>{{ config('company.legal_name') }}</strong><br>
                            {{ config('company.legal_form') }}<br>
                            {{ config('company.activity') }}
                        </p>

                        <p>
                            <strong>Sede / morada:</strong> {{ config('company.address_line') }}<br>
                            <strong>E-mail:</strong> <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a><br>
                            <strong>Telefone / WhatsApp:</strong> <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a><br>
                            <strong>NIF / NIPC:</strong> {{ config('company.nif') }}<br>
                            <strong>IVA:</strong> {{ config('company.vat') }}<br>
                            <strong>Data de constituição:</strong> {{ config('company.incorporated_at') }}<br>
                            <strong>Capital social:</strong> {{ config('company.share_capital') }}<br>
                            <strong>CAE principal:</strong> {{ config('company.cae') }}
                        </p>

                        <p>O website <a href="{{ route('home') }}">{{ config('company.website') }}</a> é explorado pela {{ config('company.legal_name') }}.</p>

                        <h2>Contacto</h2>

                        <p>Para informações sobre produtos, encomendas ou reclamações:</p>
                        <p>
                            E-mail: <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a><br>
                            Telefone / WhatsApp: <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a><br>
                            Formulário: <a href="{{ route('contacto') }}">página de contacto</a>
                        </p>

                        <h2>Livro de reclamações</h2>
                        <p>Nos termos da legislação portuguesa, pode apresentar uma reclamação através do Livro de Reclamações Eletrónico:</p>
                        <p><a href="{{ config('company.livro_reclamacoes') }}" target="_blank" rel="noopener">{{ config('company.livro_reclamacoes') }}</a></p>

                        <h2>Resolução alternativa de litígios</h2>
                        <p>Em caso de litígio de consumo, o consumidor pode recorrer a uma entidade de Resolução Alternativa de Litígios (RAL), nos termos da Lei n.º 144/2015. A {{ config('company.legal_name') }} não está, neste momento, aderente a uma entidade de RAL específica. A lista das entidades de RAL disponíveis em Portugal encontra-se em:</p>
                        <p><a href="{{ config('company.ral_list') }}" target="_blank" rel="noopener">{{ config('company.ral_list') }}</a></p>
                        <p>Pode também utilizar a plataforma europeia de resolução de litígios em linha:</p>
                        <p><a href="{{ config('company.odr') }}" target="_blank" rel="noopener">{{ config('company.odr') }}</a></p>

                        <h2>Conteúdo do site</h2>
                        <p>O conteúdo destas páginas foi elaborado com o máximo cuidado. Sem prejuízo dos direitos legais do consumidor, a {{ config('company.brand') }} não garante que todas as informações estejam permanentemente isentas de imprecisões ou de desatualização pontual, e reserva-se o direito de as corrigir.</p>

                        <h2>Propriedade intelectual</h2>
                        <p>Todo o conteúdo deste site (textos, imagens, gráficos, logótipos e demais elementos) está protegido pelas leis de propriedade intelectual e pertence à {{ config('company.legal_name') }}, salvo indicação em contrário. Qualquer reprodução, modificação, publicação ou adaptação, total ou parcial, exige autorização prévia por escrito.</p>

                        <h2>Hiperligações</h2>
                        <p>O site pode conter hiperligações para sites de terceiros. A {{ config('company.brand') }} não controla esses sites e não assume responsabilidade pelo respetivo conteúdo ou políticas de privacidade.</p>

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
