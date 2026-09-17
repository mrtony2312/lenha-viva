@extends('layouts.app')

@section('title', __('Condições gerais de venda CGV'))
@section('meta_description', 'Condições gerais de venda da Naturalenha: encomendas, pagamentos, entregas em Portugal, livre resolução de 14 dias e garantia legal.')
@section('canonical', route('condicoes-gerais-de-venda-cgv'))

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
                <h1 class="page-title">Condições gerais de venda CGV</h1>
            </div>
        </div>
        <section id="main-container" class="container">
            <div class="row ">
                <div id="main-content" class="main-page col-12">
                    <div id="main" class="site-main">

                        <h2>1. Preâmbulo</h2>
                        <p>As presentes Condições Gerais de Venda (CGV) regem as vendas efetuadas no site <a href="{{ route('home') }}">{{ config('company.website') }}</a>, explorado pela {{ config('company.legal_name') }}, e definem os direitos e obrigações das partes na venda online de lenha, pellets de madeira, madeira densificada e equipamentos de aquecimento a consumidores em Portugal.</p>
                        <p>Ao confirmar a encomenda, o cliente declara ter lido e aceite estas CGV.</p>

                        <h2>2. Identificação do vendedor</h2>
                        <p>
                            {{ config('company.legal_name') }} — {{ config('company.legal_form') }}<br>
                            NIF / NIPC: {{ config('company.nif') }} · IVA: {{ config('company.vat') }}<br>
                            Morada: {{ config('company.address_line') }}<br>
                            E-mail: <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a><br>
                            Telefone / WhatsApp: <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a>
                        </p>

                        <h2>3. Produtos</h2>
                        <p>Os produtos são descritos no catálogo com a maior precisão possível. As fotografias são ilustrativas e podem diferir ligeiramente da realidade (lote, ecrã ou embalagem). As características relevantes (dimensões, peso, potência, quantidade) constam da ficha de produto.</p>

                        <h2>4. Preços</h2>
                        <p>Os preços apresentados estão em euros, com IVA incluído. O envio é gratuito em Portugal Continental. Os envios para os Açores e a Madeira são feitos sob consulta e o respetivo custo é comunicado antes da confirmação do pagamento, conforme a <a href="{{ route('politicaDeEntrega') }}">política de entrega</a>.</p>
                        <p>A {{ config('company.brand') }} pode alterar os preços a qualquer momento; aplica-se o preço em vigor no momento em que a encomenda é submetida.</p>

                        <h2>5. Encomenda</h2>
                        <p>O cliente seleciona os produtos, indica a morada de entrega em Portugal e confirma o processo de compra. A encomenda fica registada após a submissão. Só é preparada depois da receção do pagamento integral por transferência bancária.</p>
                        <p>A {{ config('company.brand') }} pode recusar ou cancelar uma encomenda em caso de erro manifesto de preço, falta de stock, suspeita de fraude ou morada de entrega fora das zonas servidas.</p>

                        <h2>6. Pagamento</h2>
                        <p>O método de pagamento aceite é a transferência bancária. Os dados bancários são enviados por e-mail após a encomenda. Não pedimos dados de cartão. Pormenores na <a href="{{ route('politicaDePagamento') }}">política de pagamento</a>.</p>

                        <h2>7. Entrega</h2>
                        <p>As entregas são efetuadas em Portugal. Portugal Continental: envio gratuito, prazo habitual de 3 a 5 dias úteis após a confirmação do pagamento. Açores e Madeira: sob consulta. Os prazos são indicativos. Em caso de atraso superior a 30 dias após a data prevista, o consumidor pode resolver o contrato e obter o reembolso das quantias pagas, nos termos da lei.</p>
                        <p>A entrega de paletes é feita a pé de camião. Consulte a <a href="{{ route('politicaDeEntrega') }}">política de entrega</a>.</p>

                        <h2>8. Direito de livre resolução</h2>
                        <p>Nos termos do Decreto-Lei n.º 24/2014, o consumidor tem 14 dias a contar da receção dos bens para resolver o contrato, sem necessidade de justificação. Na livre resolução, os custos de devolução dos bens são da responsabilidade do consumidor, salvo se o produto estiver com defeito ou não corresponder à encomenda. O procedimento, as exclusões (bens personalizados, pellets ou lenha abertos/utilizados, equipamentos instalados ou usados) e o modelo de declaração constam da <a href="{{ route('politicaDeReembolso') }}">política de reembolso</a>.</p>

                        <h2>9. Garantia legal de conformidade</h2>
                        <p>Os bens de consumo beneficiam da garantia legal de conformidade prevista no Decreto-Lei n.º 84/2021 (em regra, 3 anos a contar da entrega, para bens novos). Em caso de falta de conformidade, o consumidor tem direito à reposição da conformidade (reparação ou substituição), à redução do preço ou à resolução do contrato, nos termos da lei.</p>

                        <h2>10. Responsabilidade</h2>
                        <p>A {{ config('company.brand') }} não responde por danos decorrentes de utilização indevida do produto ou em desconformidade com as instruções. Os atrasos causados por força maior (condições meteorológicas extremas, greves de transportadores, restrições de acesso) são comunicados ao cliente.</p>

                        <h2>11. Apoio ao cliente e reclamações</h2>
                        <p>Contacto: <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a> · <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a> · <a href="{{ route('contacto') }}">formulário de contacto</a>.</p>
                        <p>Livro de Reclamações Eletrónico: <a href="{{ config('company.livro_reclamacoes') }}" target="_blank" rel="noopener">{{ config('company.livro_reclamacoes') }}</a></p>

                        <h2>12. Lei aplicável e litígios</h2>
                        <p>Estas CGV são regidas pela lei portuguesa. Em caso de litígio de consumo, o consumidor pode recorrer a uma entidade de RAL nos termos da Lei n.º 144/2015. A {{ config('company.legal_name') }} não está, neste momento, aderente a uma entidade de RAL específica. Lista das entidades: <a href="{{ config('company.ral_list') }}" target="_blank" rel="noopener">consumidor.gov.pt</a>. Plataforma europeia de RLL: <a href="{{ config('company.odr') }}" target="_blank" rel="noopener">{{ config('company.odr') }}</a>. Subsidiariamente, são competentes os tribunais portugueses, sem prejuízo das regras de competência em matéria de consumidores.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('layouts.partials.footer.public')
@endsection

@push('scripts')
@endpush
