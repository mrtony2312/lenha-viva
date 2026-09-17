@extends('layouts.app')

@section('title', 'Política de entrega — Portugal')
@section('meta_description', 'Envio grátis para todo o Portugal Continental. Prazos, paletes a pé de camião e incidências. Política de entrega Naturalenha.')
@section('canonical', route('politicaDeEntrega'))

@section('content')
    @include('layouts.partials.navbar.public-show')

    <div id="tbay-main-content" class="lv-policy">
        <section id="tbay-breadcrumb" class="tbay-breadcrumb breadcrumbs-text active-nav-right show-title">
            <div class="container">
                <div class="breadscrumb-inner">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}">Início</a></li>
                        <li class="active" aria-current="page">Política de entrega</li>
                    </ol>
                </div>
            </div>
        </section>

        <header class="lv-policy__hero">
            <div class="lv-container">
                <p class="lv-policy__kicker">Portugal</p>
                <h1 class="lv-policy__title">Política de entrega</h1>
                <p class="lv-policy__lead">
                    A {{ config('company.brand') }} entrega pellets, lenha e equipamentos de aquecimento ao domicílio em
                    Portugal. O envio é gratuito em
                    Portugal Continental, depois de confirmado o pagamento por
                    transferência bancária. Expedimos a partir do nosso armazém em Portugal.
                    Os Açores e a Madeira são servidos sob consulta.
                </p>
                <ul class="lv-policy__highlights">
                    <li>
                        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                        Envio grátis para todo o Portugal Continental
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="7" width="13" height="10" rx="1" />
                            <path d="M16 10h3l2 3v4h-5" />
                            <circle cx="7.5" cy="18.5" r="1.5" />
                            <circle cx="18.5" cy="18.5" r="1.5" />
                        </svg>
                        Paletes a pé de camião
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                        Prazos em dias úteis
                    </li>
                </ul>
                <nav class="lv-policy__toc" aria-label="Conteúdo desta página">
                    <a href="#zonas-entrega">Zonas e prazos</a>
                    <a href="#processamento">Processamento</a>
                    <a href="#paletes">Paletes</a>
                    <a href="#seguimento">Seguimento</a>
                    <a href="#danos">Incidências</a>
                </nav>
            </div>
        </header>

        <div class="lv-container lv-policy__body">
            <section class="lv-policy__countries" aria-labelledby="zonas-entrega">
                <h2 id="zonas-entrega" class="lv-policy__section-title">Zonas e prazos</h2>
                <p class="lv-policy__section-intro">
                    Os prazos contam a partir da confirmação da encomenda (receção do pagamento)
                    e são indicados em dias úteis (segunda a sexta-feira, excluindo feriados locais).
                    São orientativos: o transportador, o volume de encomendas da época ou o acesso à
                    morada de entrega podem prolongá-los.
                </p>

                <div class="lv-policy__table-wrap">
                    <table class="lv-policy-table">
                        <caption class="sr-only">Comparação da entrega entre Portugal Continental e as Regiões Autónomas</caption>
                        <thead>
                            <tr>
                                <th scope="col">Conceito</th>
                                <th scope="col">Portugal Continental</th>
                                <th scope="col">Açores e Madeira</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">Zona com envio grátis</th>
                                <td data-label="Portugal Continental">Todo o continente</td>
                                <td data-label="Açores e Madeira">Não abrangida</td>
                            </tr>
                            <tr>
                                <th scope="row">Prazo habitual</th>
                                <td data-label="Portugal Continental">3 a 5 dias úteis</td>
                                <td data-label="Açores e Madeira">7 a 12 dias úteis</td>
                            </tr>
                            <tr>
                                <th scope="row">Custo de envio</th>
                                <td data-label="Portugal Continental">Gratuito</td>
                                <td data-label="Açores e Madeira">Sob consulta</td>
                            </tr>
                            <tr>
                                <th scope="row">Zonas fora da entrega padrão</th>
                                <td data-label="Portugal Continental">Zonas de acesso restrito: contacte-nos</td>
                                <td data-label="Açores e Madeira">Todas as ilhas: contacte-nos</td>
                            </tr>
                            <tr>
                                <th scope="row">Entrega a pé de camião</th>
                                <td data-label="Portugal Continental">Sim</td>
                                <td data-label="Açores e Madeira">Sim</td>
                            </tr>
                            <tr>
                                <th scope="row">Formalidades aduaneiras</th>
                                <td data-label="Portugal Continental">Não (envio nacional)</td>
                                <td data-label="Açores e Madeira">Não (território nacional)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="lv-policy__grid">
                    <article class="lv-card lv-policy-country" aria-labelledby="pais-continente">
                        <div class="lv-policy-country__flag" aria-hidden="true">
                            <svg viewBox="0 0 28 20" width="28" height="20" focusable="false">
                                <rect width="11" height="20" fill="#006600" />
                                <rect x="11" width="17" height="20" fill="#ff0000" />
                                <circle cx="11" cy="10" r="3.2" fill="#ffc400" />
                            </svg>
                        </div>
                        <h3 id="pais-continente" class="lv-card__title">Portugal Continental</h3>
                        <p class="lv-card__desc">
                            Entrega ao domicílio em todo o <strong>Portugal Continental</strong>,
                            sem custos de envio. Abrange todos os distritos, do Norte ao Algarve
                            (Porto, Braga, Viseu, Coimbra, Lisboa, Faro, etc.).
                        </p>
                        <p class="lv-card__desc">
                            O nosso armazém está em Portugal, pelo que o prazo habitual é de
                            <strong>3 a 5 dias úteis</strong> após a confirmação do pagamento.
                            Em zonas de acesso restrito, contacte-nos antes de encomendar.
                        </p>
                    </article>

                    <article class="lv-card lv-policy-country" aria-labelledby="pais-pt">
                        <div class="lv-policy-country__flag" aria-hidden="true">
                            <svg viewBox="0 0 28 20" width="28" height="20" focusable="false">
                                <rect width="11" height="20" fill="#006600" />
                                <rect x="11" width="17" height="20" fill="#ff0000" />
                                <circle cx="11" cy="10" r="3.2" fill="#ffc400" />
                            </svg>
                        </div>
                        <h3 id="pais-pt" class="lv-card__title">Açores e Madeira</h3>
                        <p class="lv-card__desc">
                            As Regiões Autónomas dos <strong>Açores e da Madeira</strong> não estão
                            abrangidas pelo envio gratuito automático, uma vez que a expedição é
                            feita por via marítima.
                        </p>
                        <p class="lv-card__desc">
                            Contacte-nos antes de encomendar: confirmamos a disponibilidade, o custo
                            e um prazo habitual de 7 a 12 dias úteis.
                        </p>
                    </article>
                </div>
            </section>

            <section class="lv-card" aria-labelledby="processamento">
                <h2 id="processamento" class="lv-card__title">
                    <span class="lv-step-num" aria-hidden="true">1</span>
                    Processamento da encomenda
                </h2>
                <p class="lv-card__desc">
                    Preparamos a encomenda no prazo de 24 horas (1 dia útil) após a
                    confirmação do pagamento. Até essa altura, o prazo de transporte não começa a contar.
                </p>
                <ol class="lv-policy__steps">
                    <li>
                        <strong>Encomenda no site</strong>
                        Conclui a finalização da compra com a morada de entrega em Portugal.
                    </li>
                    <li>
                        <strong>Pagamento por transferência</strong>
                        A confirmação ocorre quando recebemos o valor. Indique o seu nome
                        e o número da encomenda na descrição da transferência.
                    </li>
                    <li>
                        <strong>Preparação</strong>
                        Encomendas confirmadas <strong>antes das 17:00</strong> (hora de Portugal
                        Continental) são processadas no mesmo dia útil. Depois das 17:00, aos sábados,
                        domingos ou feriados: no dia útil seguinte.
                    </li>
                    <li>
                        <strong>Saída para transporte</strong>
                        Recebe o número de seguimento por e-mail quando a palete ou o volume sai do armazém.
                    </li>
                </ol>
            </section>

            <section class="lv-card" aria-labelledby="custos">
                <h2 id="custos" class="lv-card__title">
                    <span class="lv-step-num" aria-hidden="true">2</span>
                    Custos de envio
                </h2>
                <p>
                    Em <a href="{{ route('home') }}">{{ config('company.website') }}</a>, a entrega é
                    <strong>gratuita</strong> em Portugal Continental para
                    todos os produtos do catálogo (pellets, lenha, madeira densificada, recuperadores e caldeiras).
                </p>
                <p>
                    Indique uma morada completa e correta: rua, número, código postal,
                    localidade e distrito. Um erro na morada pode atrasar
                    ou impedir a entrega. Se o acesso for difícil, indique-o nas notas da encomenda.
                </p>
            </section>

            <section class="lv-card" aria-labelledby="paletes">
                <h2 id="paletes" class="lv-card__title">
                    <span class="lv-step-num" aria-hidden="true">3</span>
                    Entrega de paletes e produtos volumosos
                </h2>
                <p>
                    Pellets, lenha e madeira densificada são normalmente enviados em palete.
                    Caldeiras e recuperadores são volumes pesados. Em Portugal, a entrega
                    é feita a <strong>pé de camião</strong> / à entrada acessível,
                    salvo acordo prévio.
                </p>
                <ul class="lv-policy__list">
                    <li>O camião tem de conseguir acesso (via praticável, sem restrição de altura ou de peso não comunicada).</li>
                    <li>Tem de estar uma pessoa maior de idade na morada de entrega no período horário acordado.</li>
                    <li>Não subimos paletes a andares nem as deixamos no interior da habitação, salvo acordo expresso.</li>
                    <li>Centro histórico, condomínio fechado ou propriedade rústica: avise-nos antes; pode ser necessário um ponto de descarga alternativo.</li>
                </ul>
            </section>

            <section class="lv-card" aria-labelledby="seguimento">
                <h2 id="seguimento" class="lv-card__title">
                    <span class="lv-step-num" aria-hidden="true">4</span>
                    Seguimento
                </h2>
                <p>
                    Quando a encomenda sair do armazém, enviamos-lhe um e-mail com o número
                    de seguimento. Guarde essa mensagem até ter recebido a mercadoria.
                    O serviço cobre todo o território nacional e a ligação de seguimento funciona em qualquer região.
                </p>
            </section>

            <section class="lv-card" aria-labelledby="danos">
                <h2 id="danos" class="lv-card__title">
                    <span class="lv-step-num" aria-hidden="true">5</span>
                    Encomenda danificada ou não recebida
                </h2>
                <p>Se o envio chegar danificado ou não chegar ao destino:</p>
                <ol class="lv-policy__list lv-policy__list--ordered">
                    <li>Registe as reservas na guia de transporte do transportador se o dano for visível.</li>
                    <li>
                        Escreva-nos para
                        <a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a>
                        no prazo de <strong>48 horas</strong> a contar da entrega (ou da data prevista, se não chegar), para tratarmos o incidente de transporte. Este prazo de aviso <strong>não limita</strong> a garantia legal de conformidade.
                    </li>
                    <li>Anexe fotografias da embalagem, da palete e dos produtos afetados.</li>
                </ol>
                <p>Trataremos do reenvio ou do reembolso, conforme o caso. Mais detalhes na
                    <a href="{{ route('politicaDeReembolso') }}">política de reembolso</a>.
                </p>
            </section>

            <aside class="lv-policy__cta" aria-label="Contacto para envios em Portugal">
                <p>Precisa de um envio para o continente, Açores ou Madeira?</p>
                <a class="lv-btn lv-btn--primary" href="{{ route('contacto') }}">Contactar a Naturalenha</a>
                <a class="lv-btn lv-btn--ghost" href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a>
            </aside>

            <p class="lv-policy__logo">
                <img loading="lazy" decoding="async" width="342" height="160"
                    src="{{ asset(config('company.logo')) }}"
                    alt="Naturalenha, Unipessoal, Lda">
            </p>
        </div>
    </div>

    @include('layouts.partials.footer.public')
@endsection
