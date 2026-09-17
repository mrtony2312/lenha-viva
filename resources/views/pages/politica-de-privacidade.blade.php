@extends('layouts.app')

@section('title', __('Política de Privacidade'))
@section('meta_description', 'Política de privacidade da Naturalenha: tratamento de dados pessoais, cookies, Google Analytics/Ads e direitos do utilizador (RGPD).')
@section('canonical', route('politica-de-privacidade'))

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
                <h1 class="page-title">Política de Privacidade</h1>
            </div>
        </div>
        <section id="main-container" class="container">
            <div class="row ">
                <div id="main-content" class="main-page col-12">
                    <div id="main" class="site-main">

                        <p>A {{ config('company.legal_name') }} trata os dados pessoais no website <a href="{{ route('home') }}">{{ config('company.website') }}</a> e nas relações comerciais em conformidade com o Regulamento (UE) 2016/679 (RGPD), a Lei n.º 58/2019 e a Lei n.º 41/2004 (comunicações eletrónicas / cookies).</p>
                        <p>Última atualização: 29 de agosto de 2026. Não existem decisões automatizadas com efeitos jurídicos sobre as compras. A publicidade personalizada só ocorre se aceitar cookies de análise e publicidade.</p>

                        <h2>1. Responsável pelo tratamento</h2>
                        <p>
                            {{ config('company.legal_name') }} — {{ config('company.legal_form') }}<br>
                            NIF / NIPC: {{ config('company.nif') }} · IVA: {{ config('company.vat') }}<br>
                            Morada: {{ config('company.address_line') }}<br>
                            E-mail: <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a><br>
                            Telefone / WhatsApp: <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a>
                        </p>

                        <h2>2. Dados que recolhemos</h2>
                        <p>Recolhemos apenas o necessário para a loja, as encomendas e o contacto:</p>
                        <ul>
                            <li>identificação e contacto: nome, morada, e-mail, telefone;</li>
                            <li>dados da encomenda e de faturação, incluindo NIF quando o indicar;</li>
                            <li>mensagens enviadas por e-mail, telefone, WhatsApp ou formulário de contacto;</li>
                            <li>dados técnicos da visita: endereço IP, tipo de navegador, páginas visitadas, data e hora, e identificadores de cookies (análise e publicidade apenas com o seu consentimento).</li>
                        </ul>
                        <p>O fornecimento dos dados da encomenda é necessário para celebrar e executar o contrato. Sem esses dados não podemos processar a compra nem emitir fatura. O consentimento aplica-se aos cookies de análise/publicidade e a eventuais comunicações de marketing, não à encomenda em si.</p>

                        <h2>3. Finalidades e bases legais</h2>
                        <ul>
                            <li><strong>Execução do contrato</strong> (art. 6.º, n.º 1, alínea b), do RGPD): gestão da encomenda, pagamento por transferência, entrega, apoio ao cliente e devoluções.</li>
                            <li><strong>Obrigação legal</strong> (alínea c)): faturação, contabilidade e prazos legais de conservação (em regra 10 anos para documentos fiscais).</li>
                            <li><strong>Interesse legítimo</strong> (alínea f)): segurança do site, prevenção de fraude e defesa de direitos em litígio.</li>
                            <li><strong>Consentimento</strong> (alínea a) e Lei n.º 41/2004): cookies de análise e publicidade (Google Analytics e Google Ads) e, se for o caso, envio de comunicações comerciais. Pode retirar o consentimento a qualquer momento, sem afetar a licitude do tratamento anterior.</li>
                        </ul>

                        <h2>4. Destinatários</h2>
                        <p>Os dados são tratados pela {{ config('company.brand') }} e, quando necessário, por:</p>
                        <ul>
                            <li>prestador de alojamento do website;</li>
                            <li>transportadoras contratadas para a entrega;</li>
                            <li>Google Ireland Limited (Google Analytics e Google Ads), apenas se aceitar cookies de análise e publicidade.</li>
                        </ul>
                        <p>Não utilizamos prestadores de pagamento com cartão: o pagamento é feito por transferência bancária para a nossa conta. Não vendemos dados a terceiros para fins comerciais.</p>

                        <h2>5. Transferências para fora da União Europeia</h2>
                        <p>Se aceitar cookies de análise e publicidade, a Google pode tratar dados nos Estados Unidos (Google LLC). Essas transferências assentam no EU-US Data Privacy Framework e/ou nas cláusulas contratuais-tipo da Comissão Europeia. Sem o seu consentimento para esses cookies, não ativamos o armazenamento de análise nem de publicidade (Consent Mode).</p>
                        <p>Não transferimos os dados da encomenda para fora da UE/EEE para além do que resulte desses serviços, se os tiver aceite.</p>

                        <h2>6. Conservação</h2>
                        <ul>
                            <li>encomendas, faturas e dados fiscais: o tempo da relação comercial e, depois, o prazo legal (em regra 10 anos);</li>
                            <li>pedidos de contacto sem encomenda: até 3 anos após o último contacto;</li>
                            <li>exercício de direitos RGPD: o tempo necessário para responder (em regra até 1 mês);</li>
                            <li>cookies de análise/publicidade: conforme a configuração da Google e até retirar o consentimento ou limpar o armazenamento local (<code>lv_cookie_consent</code>).</li>
                        </ul>

                        <h2>7. Cookies</h2>
                        <p>Utilizamos:</p>
                        <ul>
                            <li><strong>Cookies estritamente necessários:</strong> sessão e carrinho, indispensáveis ao funcionamento da loja. Não exigem consentimento.</li>
                            <li><strong>Cookies de análise e publicidade:</strong> Google Analytics (<code>G-MX4HS3ZTPP</code>) e Google Ads (<code>AW-17798780713</code>), apenas se clicar em «Aceitar» no aviso de cookies. «Recusar» mantém-nos desativados.</li>
                        </ul>
                        <p>Não utilizamos cookies de funcionalidade autónomos (idioma ou região). Pode também bloquear cookies nas definições do navegador. O aviso reaparece se apagar os dados do site neste dispositivo.</p>

                        <h2>8. Os seus direitos</h2>
                        <p>Pode solicitar acesso, retificação, apagamento, limitação, portabilidade e oposição, e retirar o consentimento dos cookies. Para o exercício destes direitos:</p>
                        <p>
                            E-mail: <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a><br>
                            Telefone / WhatsApp: <a href="tel:{{ config('company.phone_tel') }}">{{ config('company.phone') }}</a>
                        </p>
                        <p>Só pediremos um comprovativo de identidade se for necessário confirmar que o pedido é feito pelo titular dos dados.</p>
                        <p>Pode apresentar reclamação à Comissão Nacional de Proteção de Dados (CNPD): <a href="{{ config('company.cnpd') }}" target="_blank" rel="noopener">{{ config('company.cnpd') }}</a>.</p>

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
