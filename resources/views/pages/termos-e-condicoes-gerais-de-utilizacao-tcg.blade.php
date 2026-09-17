@extends('layouts.app')

@section('title', __('Termos e condições gerais de utilização TCG'))
@section('meta_description', 'Termos de utilização do site Naturalenha: acesso, propriedade intelectual, dados pessoais e lei aplicável.')
@section('canonical', route('termos-e-condicoes-gerais-de-utilizacao-tcg'))

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
                <h1 class="page-title">Termos e condições gerais de utilização TCG</h1>
            </div>
        </div>
        <section id="main-container" class="container">
            <div class="row ">
                <div id="main-content" class="main-page col-12">
                    <div id="main" class="site-main">

                        <h2>1. Finalidade</h2>
                        <p>Os presentes Termos Gerais de Utilização (TCG) definem as regras de acesso e utilização do website <a href="{{ route('home') }}">{{ config('company.website') }}</a>, explorado pela {{ config('company.legal_name') }}, e os direitos e obrigações dos utilizadores. As compras estão sujeitas às <a href="{{ route('condicoes-gerais-de-venda-cgv') }}">Condições gerais de venda</a>.</p>

                        <h2>2. Identificação</h2>
                        <p>
                            {{ config('company.legal_name') }} — {{ config('company.legal_form') }}<br>
                            NIF / NIPC: {{ config('company.nif') }} · IVA: {{ config('company.vat') }}<br>
                            Morada: {{ config('company.address_line') }}<br>
                            E-mail: <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a><br>
                            Telefone / WhatsApp: <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a>
                        </p>
                        <p>Informação societária completa: <a href="{{ route('avisos-legais') }}">avisos legais</a>.</p>

                        <h2>3. Acesso ao site</h2>
                        <p>O site é acessível gratuitamente a qualquer utilizador com ligação à Internet. O utilizador é responsável pelo seu equipamento e pela ligação. A {{ config('company.brand') }} pode suspender, limitar ou interromper o acesso por motivos técnicos, de manutenção ou de segurança.</p>

                        <h2>4. Utilização</h2>
                        <p>O utilizador compromete-se a usar o site de acordo com a lei portuguesa e estes TCG. É proibido, nomeadamente:</p>
                        <ul>
                            <li>utilizar o site para fins ilícitos ou fraudulentos;</li>
                            <li>prejudicar o funcionamento do site;</li>
                            <li>violar direitos de propriedade intelectual ou a imagem da {{ config('company.brand') }}.</li>
                        </ul>

                        <h2>5. Produtos e serviços</h2>
                        <p>O site apresenta lenha, pellets de madeira e equipamentos de aquecimento para venda a consumidores em Portugal. Preços, entrega, pagamento, livre resolução e garantia constam das CGV e das políticas de <a href="{{ route('politicaDeEntrega') }}">entrega</a>, <a href="{{ route('politicaDePagamento') }}">pagamento</a> e <a href="{{ route('politicaDeReembolso') }}">reembolso</a>.</p>

                        <h2>6. Propriedade intelectual</h2>
                        <p>O conteúdo do site (textos, imagens, logótipos, gráficos e demais elementos) pertence à {{ config('company.legal_name') }}, salvo indicação em contrário. Qualquer reprodução, distribuição ou modificação, mesmo parcial, exige autorização prévia por escrito.</p>

                        <h2>7. Dados pessoais</h2>
                        <p>Os dados recolhidos são tratados em conformidade com o RGPD e a Lei n.º 58/2019. Consulte a <a href="{{ route('politica-de-privacidade') }}">Política de Privacidade</a>.</p>

                        <h2>8. Responsabilidade</h2>
                        <p>A {{ config('company.brand') }} procura manter o site exato e atualizado. Sem prejuízo dos direitos legais do consumidor, a empresa não responde por:</p>
                        <ul>
                            <li>interrupções, indisponibilidades ou avarias do site;</li>
                            <li>danos decorrentes da utilização do site em desconformidade com estes TCG;</li>
                            <li>conteúdo de sites de terceiros ligados a partir deste website.</li>
                        </ul>

                        <h2>9. Hiperligações</h2>
                        <p>O site pode conter ligações para sites de terceiros. A {{ config('company.brand') }} não controla esses sites e não assume responsabilidade pelo respetivo conteúdo ou políticas.</p>

                        <h2>10. Alterações</h2>
                        <p>Podemos alterar estes TCG para os adequar a evoluções legais, técnicas ou funcionais. A versão aplicável é a publicada no site no momento da navegação. Para as compras, prevalece a versão das CGV aceite no momento da encomenda.</p>

                        <h2>11. Lei aplicável e litígios</h2>
                        <p>Estes TCG regem-se pela lei portuguesa. Em caso de litígio de consumo, o utilizador pode recorrer a uma entidade de Resolução Alternativa de Litígios (Lei n.º 144/2015). A {{ config('company.legal_name') }} não está, neste momento, aderente a uma entidade de RAL específica. A lista das entidades de RAL em Portugal está disponível em <a href="{{ config('company.ral_list') }}" target="_blank" rel="noopener">consumidor.gov.pt</a>. Pode também utilizar a plataforma europeia de RLL: <a href="{{ config('company.odr') }}" target="_blank" rel="noopener">{{ config('company.odr') }}</a>.</p>
                        <p>Livro de Reclamações Eletrónico: <a href="{{ config('company.livro_reclamacoes') }}" target="_blank" rel="noopener">{{ config('company.livro_reclamacoes') }}</a>.</p>

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
