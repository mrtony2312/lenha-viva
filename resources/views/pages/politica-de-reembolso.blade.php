@extends('layouts.app')

@section('title', __('Política de reembolso'))
@section('meta_description', 'Política de devoluções e reembolsos da Naturalenha: 14 dias de livre resolução, prazos, custos e produtos excluídos.')
@section('canonical', route('politicaDeReembolso'))

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
                <h1 class="page-title">Política de reembolso</h1>
            </div>
        </div>
        <section id="main-container" class="container">
            <div class="row ">
                <div id="main-content" class="main-page col-12">
                    <div id="main" class="site-main">

                        <p>Esta política descreve como a {{ config('company.legal_name') }} trata devoluções e reembolsos das compras feitas em <a href="{{ route('home') }}">{{ config('company.website') }}</a>. Aplica-se a consumidores em Portugal e complementa as <a href="{{ route('condicoes-gerais-de-venda-cgv') }}">Condições gerais de venda</a>.</p>
                        <p><strong>{{ config('company.sales_territory_note') }}</strong></p>

                        <h2>1. Direito de livre resolução (14 dias)</h2>
                        <p>Nos termos do Decreto-Lei n.º 24/2014, o consumidor dispõe de <strong>14 dias</strong> a contar da receção dos produtos para resolver o contrato, sem indicação de motivo e sem incorrer em custos para além dos previstos na lei (em regra, os custos de devolução).</p>
                        <p>Para exercer este direito, contacte-nos dentro desse prazo por e-mail para <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a> ou por telefone para <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a>, indicando o número da encomenda e a intenção de resolver o contrato. De seguida indicamos o endereço e o procedimento de devolução.</p>

                        <h2>2. Produtos excluídos da livre resolução</h2>
                        <p>O direito de livre resolução não se aplica, nomeadamente, quando:</p>
                        <ul>
                            <li>os bens foram feitos segundo especificações do consumidor ou claramente personalizados;</li>
                            <li>os bens se deterioram ou caducam rapidamente, ou foram desprotegidos após a entrega de modo a não poderem ser revendidos em condições de higiene ou segurança (por exemplo, sacos de pellets ou lenha abertos, molhados ou já utilizados);</li>
                            <li>os equipamentos de aquecimento foram instalados, ligados à chaminé ou usados.</li>
                        </ul>
                        <p>Equipamentos por abrir, na embalagem original e sem sinais de uso, podem ser devolvidos no prazo de 14 dias.</p>

                        <h2>3. Produto com defeito ou diferente da encomenda</h2>
                        <p>Se o produto chegar danificado, incompleto ou não corresponder à encomenda, pedimos que nos contacte no prazo de <strong>48 horas</strong> após a entrega (ou após a data prevista, se não chegar), com o número da encomenda e fotografias, para tratarmos o incidente de transporte com a maior rapidez. Este prazo de aviso <strong>não limita</strong> a garantia legal de conformidade (Decreto-Lei n.º 84/2021, em regra 3 anos). Nestes casos, os custos de recolha ou reenvio são da nossa responsabilidade.</p>

                        <h2>4. Estado dos produtos devolvidos</h2>
                        <p>Os produtos sujeitos a livre resolução devem ser devolvidos completos, em bom estado e, sempre que possível, na embalagem original. A {{ config('company.brand') }} pode responsabilizar o consumidor pela depreciação do bem se o manuseamento ultrapassar o necessário para verificar a natureza, as características e o funcionamento.</p>

                        <h2>5. Custos de devolução</h2>
                        <p>Na livre resolução, os custos de devolução (incluindo paletes ou volumes pesados) são da responsabilidade do cliente, salvo se o produto estiver com defeito ou não corresponder à encomenda. Paletes de lenha, pellets ou equipamentos pesados exigem transporte adequado; não recuse a palete no cais sem nos contactar primeiro.</p>

                        <h2>6. Prazo e forma de reembolso</h2>
                        <p>O reembolso é efetuado <strong>no prazo de 14 dias</strong> a contar da data em que fomos informados da decisão de resolução, podendo reter o reembolso até à receção dos bens ou até o consumidor apresentar prova de expedição, nos termos do Decreto-Lei n.º 24/2014.</p>
                        <p>O reembolso inclui o preço pago pelos bens. O envio inicial em Portugal Continental é gratuito, pelo que não há portes de ida a reembolsar. O reembolso é feito por transferência bancária para a conta de origem. Não cobramos taxa de reembolso.</p>

                        <h2>7. Como iniciar uma devolução</h2>
                        <ol>
                            <li>Escreva para <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a> ou ligue para <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a>.</li>
                            <li>Indique o número da encomenda, os produtos e o motivo (livre resolução, defeito ou erro).</li>
                            <li>Anexe fotografias se o produto estiver danificado.</li>
                            <li>Aguarde as instruções de devolução antes de expedir a mercadoria.</li>
                        </ol>

                        <h2>8. Morada de devolução</h2>
                        <p>Salvo indicação em contrário nas instruções que lhe enviarmos, as devoluções devem ser enviadas para:</p>
                        <p>
                            {{ config('company.legal_name') }}<br>
                            {{ config('company.address_line') }}<br>
                            E-mail: <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a>
                        </p>
                        <p>Não envie a mercadoria sem confirmação prévia: paletes e equipamentos pesados exigem transporte adequado.</p>

                        <h2>9. Modelo de declaração de livre resolução</h2>
                        <p>Pode copiar o texto seguinte para o e-mail (Decreto-Lei n.º 24/2014):</p>
                        <p>
                            «À {{ config('company.legal_name') }}, {{ config('company.address_line') }}, {{ config('company.email') }}.<br>
                            Eu, abaixo-assinado, comunico que resolvo o meu contrato de compra dos seguintes bens: [produtos].<br>
                            Encomendados em: [data] / recebidos em: [data].<br>
                            Número da encomenda: [número].<br>
                            Nome e morada do consumidor: [dados].<br>
                            Data: [data].»
                        </p>

                        <p>Também pode apresentar reclamação no <a href="{{ config('company.livro_reclamacoes') }}" target="_blank" rel="noopener">Livro de Reclamações Eletrónico</a>.</p>

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
