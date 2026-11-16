<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PITAMEX · Registro de productores y rifa · {{ $raffle->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <meta name="description" content="Complete su registro de productor y participe en la rifa de PITAMEX.">
    <meta name="theme-color" content="#173A2C">
    <link rel="icon" href="{{ $config['logo'] }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <style>
        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: #14211B;
            background: #F7F7F2;
            -webkit-font-smoothing: antialiased
        }

        a {
            color: #C8195F
        }

        a:hover {
            color: #9E1249
        }

        button {
            font: inherit;
            cursor: pointer
        }

        input:focus,
        select:focus,
        textarea:focus,
        button:focus-visible {
            outline: 2px solid #C8195F;
            outline-offset: 1px
        }

        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px
        }

        .demo {
            background: #F2C230;
            color: #3A2E0B;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            padding: 8px 16px
        }

        header {
            background: #fff;
            border-bottom: 1px solid #E6EAE3
        }

        header .wrap {
            padding-top: 12px;
            padding-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px
        }

        header img {
            height: 64px;
            width: auto;
            display: block
        }

        .hlinks {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 18px;
            font-size: 14.5px;
            font-weight: 600
        }

        .hlinks span {
            color: #56645C
        }

        .btn-pink {
            background: #C8195F;
            color: #fff !important;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 10px;
            border: 0;
            font-weight: 700
        }

        .hero {
            background: #173A2C
        }

        .hero .wrap {
            padding-top: 64px;
            padding-bottom: 140px;
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            align-items: flex-end
        }

        .hero-l {
            flex: 999 1 520px;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 18px
        }

        .logo-box {
            align-self: flex-start;
            background: #fff;
            border-radius: 16px;
            padding: 10px 14px;
            display: inline-flex
        }

        .logo-box img {
            height: 84px;
            width: auto;
            display: block
        }

        .tag {
            align-self: flex-start;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #173A2C;
            background: #A5C84A;
            padding: 8px 14px;
            border-radius: 999px
        }

        .hero h1 {
            margin: 0;
            font-size: 52px;
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -.03em;
            color: #fff
        }

        .hero p {
            margin: 0;
            font-size: 18px;
            line-height: 1.6;
            color: #D3E3D8;
            max-width: 620px
        }

        .prizes {
            flex: 1 1 380px;
            min-width: 0;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px
        }

        .prize {
            background: #20493A;
            border-radius: 16px;
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px
        }

        .prize b {
            font-size: 12.5px;
            font-weight: 800
        }

        .prize div {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.3;
            color: #fff
        }

        #registro {
            max-width: 860px;
            margin: -96px auto 0;
            padding: 0 16px 64px;
            scroll-margin-top: 16px
        }

        .card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(20, 33, 27, .14);
            overflow: hidden
        }

        .top {
            padding: 24px 28px 20px;
            border-bottom: 1px solid #EEF1EC;
            display: flex;
            flex-direction: column;
            gap: 14px
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            flex-wrap: wrap
        }

        .eyebrow {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #C8195F
        }

        .muted {
            font-size: 13px;
            font-weight: 600;
            color: #6A776F
        }

        .bar {
            height: 8px;
            background: #EEF1EC;
            border-radius: 999px;
            overflow: hidden
        }

        .bar div {
            height: 100%;
            background: #C8195F;
            border-radius: 999px;
            transition: width .3s
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px
        }

        .chip {
            font-size: 13px;
            font-weight: 700;
            min-height: 36px;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1.5px solid #E1E5DE;
            background: #fff;
            color: #56645C
        }

        .chip.on {
            border-color: #173A2C;
            background: #173A2C;
            color: #fff
        }

        .chip.ok {
            border-color: #CFE0D5;
            background: #E6EFE8;
            color: #24563F
        }

        .body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 26px
        }

        .body h2 {
            margin: 0;
            font-size: 28px;
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -.01em;
            color: #173A2C
        }

        .body .intro {
            margin: 6px 0 0;
            font-size: 15px;
            line-height: 1.55;
            color: #56645C
        }

        .q {
            display: flex;
            flex-direction: column;
            gap: 10px
        }

        .q.err .lbl {
            color: #B3123F
        }

        .qhead {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px
        }

        .lbl {
            font-size: 15.5px;
            font-weight: 700;
            color: #14211B
        }

        .badge {
            font-size: 12px;
            font-weight: 600;
            color: #56645C;
            background: #F1F3EF;
            padding: 3px 8px;
            border-radius: 999px
        }

        .badge.expo {
            font-weight: 700;
            color: #24563F;
            background: #E6EFE8
        }

        .req {
            font-size: 12.5px;
            font-weight: 700;
            color: #B3123F
        }

        .field {
            font: inherit;
            font-size: 16px;
            padding: 13px 14px;
            border: 1px solid #CBD3CC;
            border-radius: 10px;
            color: #14211B;
            background: #fff;
            width: 100%
        }

        textarea.field {
            resize: vertical
        }

        .pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px
        }

        .pill {
            font-size: 14.5px;
            font-weight: 600;
            min-height: 44px;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1.5px solid #CBD3CC;
            background: #fff;
            color: #34463C
        }

        .pill.on {
            font-weight: 700;
            border-color: #173A2C;
            background: #173A2C;
            color: #fff
        }

        .cards {
            display: flex;
            flex-direction: column;
            gap: 8px
        }

        .opt {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-height: 48px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1.5px solid #D9DED6;
            background: #fff
        }

        .opt b {
            font-size: 15px;
            font-weight: 700;
            color: #14211B
        }

        .opt span {
            font-size: 13.5px;
            line-height: 1.45;
            color: #56645C
        }

        .opt.on {
            border: 2px solid #173A2C;
            background: #F1F6F2
        }

        .opt.on b {
            color: #173A2C
        }

        .hint {
            font-size: 13px;
            color: #6A776F
        }

        .loc {
            align-self: flex-start;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            font-weight: 700;
            min-height: 48px;
            padding: 12px 18px;
            border-radius: 10px;
            border: 1.5px solid #173A2C;
            background: #fff;
            color: #173A2C
        }

        .loc.done {
            border: 0;
            background: #E6EFE8;
            color: #24563F;
            cursor: default
        }

        .priv {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 13.5px;
            line-height: 1.5;
            color: #56645C
        }

        .priv input {
            width: 18px;
            height: 18px;
            margin-top: 1px;
            accent-color: #173A2C;
            flex: none
        }

        .foot {
            padding: 20px 28px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border-top: 1px solid #EEF1EC;
            flex-wrap: wrap
        }

        .back {
            font-size: 16px;
            font-weight: 700;
            min-height: 52px;
            padding: 12px 22px;
            border-radius: 12px;
            border: 1.5px solid #CBD3CC;
            background: #fff;
            color: #34463C
        }

        .next {
            font-size: 16px;
            font-weight: 700;
            min-height: 52px;
            padding: 12px 26px;
            border-radius: 12px;
            border: 0;
            background: #C8195F;
            color: #fff
        }

        .next:hover {
            background: #9E1249
        }

        .alert {
            background: #FBE9F0;
            color: #9E1249;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 14px;
            border-radius: 10px
        }

        .next:disabled {
            opacity: .6;
            cursor: wait
        }

        .rule {
            margin: 0;
            font-size: 14px;
            line-height: 1.55;
            color: #A9C2B2;
            max-width: 620px
        }

        .svc {
            max-width: 1200px;
            margin: 0 auto;
            padding: 8px 24px 88px;
            display: flex;
            flex-direction: column;
            gap: 22px;
            scroll-margin-top: 16px
        }

        .svc-head {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 760px
        }

        .svc-head h2 {
            margin: 0;
            font-size: 36px;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -.02em;
            color: #173A2C
        }

        .svc-head p {
            margin: 0;
            font-size: 16px;
            line-height: 1.6;
            color: #56645C
        }

        .svc-list {
            display: flex;
            flex-direction: column;
            gap: 10px
        }

        .svc-item {
            background: #fff;
            border: 1px solid #E1E5DE;
            border-radius: 16px;
            overflow: hidden;
            transition: box-shadow .2s
        }

        .svc-item[open] {
            box-shadow: 0 12px 32px rgba(20, 33, 27, .08);
            border-color: #CFE0D5
        }

        .svc-item summary {
            list-style: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 20px
        }

        .svc-item summary::-webkit-details-marker {
            display: none
        }

        .svc-n {
            flex: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #FBE9F0;
            color: #C8195F;
            font-weight: 800;
            font-size: 17px;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .svc-t {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px
        }

        .svc-t b {
            font-size: 16.5px;
            color: #14211B
        }

        .svc-t small {
            font-size: 13.5px;
            color: #6A776F
        }

        .svc-p {
            flex: none;
            text-align: right;
            font-weight: 800;
            font-size: 16px;
            color: #173A2C;
            display: flex;
            flex-direction: column
        }

        .svc-p small {
            font-size: 12px;
            font-weight: 600;
            color: #6A776F
        }

        .svc-chev {
            flex: none;
            width: 10px;
            height: 10px;
            border-right: 2px solid #56645C;
            border-bottom: 2px solid #56645C;
            transform: rotate(45deg);
            margin: 0 4px 4px 4px;
            transition: transform .2s
        }

        .svc-item[open] .svc-chev {
            transform: rotate(225deg)
        }

        .svc-body {
            padding: 0 20px 22px 76px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            font-size: 15px;
            line-height: 1.6;
            color: #34463C
        }

        .svc-body p {
            margin: 0
        }

        .svc-body ul {
            margin: 0;
            padding-left: 18px;
            display: flex;
            flex-direction: column;
            gap: 4px
        }

        .svc-body .svc-small {
            font-size: 13.5px;
            color: #6A776F
        }

        .svc-note {
            background: #FDF5DA;
            color: #3A2E0B;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14.5px
        }

        .svc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px
        }

        .svc-grid>div {
            background: #F5F7F3;
            border-radius: 12px;
            padding: 14px 16px
        }

        .svc-grid h4 {
            margin: 0 0 6px;
            font-size: 14px;
            color: #C8195F;
            text-transform: uppercase;
            letter-spacing: .06em
        }

        .svc-grid ul {
            font-size: 14px
        }

        .svc-fig {
            margin: 4px 0 0
        }

        .svc-fig img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 12px;
            border: 1px solid #E1E5DE
        }

        .svc-fig figcaption {
            font-size: 13px;
            color: #6A776F;
            margin-top: 6px
        }

        .svc-wa {
            align-self: flex-start;
            background: #173A2C;
            color: #fff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            padding: 12px 18px;
            border-radius: 10px;
            margin-top: 4px
        }

        .svc-wa:hover {
            background: #24563F
        }

        @media (max-width:640px) {
            .svc-head h2 {
                font-size: 28px
            }

            .svc-item summary {
                gap: 12px;
                padding: 16px
            }

            .svc-n {
                width: 34px;
                height: 34px;
                font-size: 15px
            }

            .svc-p {
                display: none
            }

            .svc-body {
                padding: 0 16px 20px
            }
        }

        .done {
            padding: 40px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            text-align: center
        }

        .check {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            background: #E6EFE8;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .ticket {
            width: 100%;
            max-width: 420px;
            border: 2px dashed #C8195F;
            border-radius: 20px;
            padding: 26px 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: #FDF4F8
        }

        .ticket .num {
            font-size: 64px;
            font-weight: 800;
            letter-spacing: .06em;
            color: #173A2C;
            line-height: 1.05
        }

        .internal {
            width: 100%;
            max-width: 520px;
            text-align: left;
            border: 1px solid #E1E5DE;
            border-radius: 14px;
            padding: 4px 16px;
            background: #FAFBF8
        }

        .internal summary {
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 700;
            color: #56645C;
            padding: 10px 0
        }

        .kpis {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding: 4px 0 14px
        }

        .kpi {
            flex: 1 1 140px;
            background: #173A2C;
            border-radius: 12px;
            padding: 12px 14px;
            color: #fff
        }

        .kpi small {
            display: block;
            font-size: 12px;
            color: #A9C2B2
        }

        .kpi b {
            font-size: 26px
        }

        .info {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 24px 80px;
            display: flex;
            flex-direction: column;
            gap: 24px
        }

        .info h2 {
            margin: 0;
            font-size: 36px;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -.02em;
            color: #173A2C
        }

        .g4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px
        }

        .g5 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px
        }

        .ic {
            background: #fff;
            border: 1px solid #E1E5DE;
            border-radius: 16px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 4px
        }

        .ic small {
            font-size: 13px;
            font-weight: 700;
            color: #6A776F
        }

        .ic div {
            font-size: 15.5px;
            font-weight: 600;
            line-height: 1.45
        }

        .dc {
            background: #173A2C;
            border-radius: 16px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 4px
        }

        .dc small {
            font-size: 13px;
            color: #A9C2B2
        }

        .dc div {
            font-size: 24px;
            font-weight: 800;
            color: #fff
        }

        .note {
            margin: 0;
            font-size: 13.5px;
            line-height: 1.6;
            color: #6A776F;
            max-width: 860px
        }

        footer {
            background: #10291F
        }

        footer .wrap {
            padding-top: 36px;
            padding-bottom: 36px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
            font-size: 14px;
            line-height: 1.6;
            color: #A9C2B2
        }

        footer .logo-box img {
            height: 64px
        }

        @media (max-width:640px) {
            .hero .wrap {
                padding-top: 36px
            }

            .hero h1 {
                font-size: 36px
            }

            .prizes {
                grid-template-columns: 1fr
            }

            .top,
            .body {
                padding-left: 20px;
                padding-right: 20px
            }

            .foot {
                padding: 16px 20px 22px
            }

            .next {
                flex: 1
            }

            .ticket .num {
                font-size: 52px
            }

            .info h2 {
                font-size: 28px
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="wrap">
            <img src="{{ $config['logo'] }}" alt="PITAMEX · Pitahaya de México">
            <div class="hlinks"><span>Expo Agroalimentaria Guanajuato 2026</span><a href="#servicios"
                    style="color:#173A2C;text-decoration:none">Servicios</a><a href="#registro"
                    class="btn-pink">Registrarme</a></div>
        </div>
    </header>

    <section class="hero">
        <div class="wrap">
            <div class="hero-l">
                <div class="logo-box"><img src="{{ $config['logo'] }}" alt="PITAMEX · Pitahaya de México"></div>
                <div class="tag">Gracias por visitarnos en la Expo</div>
                <h1>Complete su registro y participe en la rifa</h1>
                <p>Conocer su predio y su proyecto nos permite proponerle las alternativas más convenientes. Son unos 5
                    minutos y puede hacerlo desde el celular.</p>
                <p class="rule">Sorteo con el premio mayor de la Lotería
                    Nacional{{ $config['drawDate'] ? ' del ' . $config['drawDate'] : '' }}. Gana el número más cercano
                    @if (count($config['prizes']) > 1)
                        ; 2º y 3er lugar, los siguientes más cercanos
                    @endif. Si hay empate, gana el número menor. Un boleto por persona.</p>
            </div>
            @if (count($config['prizes']))
                <div class="prizes">
                    @php
                        $prizeColors = ['#F2A9C6', '#F2C230', '#A5C84A'];
                        $prizeLabels = ['1er lugar', '2º lugar', '3er lugar'];
                    @endphp
                    @foreach ($config['prizes'] as $i => $prize)
                        <div class="prize"><b
                                style="color:{{ $prizeColors[$i % 3] }}">{{ $prizeLabels[$i] ?? $i + 1 . 'º lugar' }}</b>
                            <div>{{ $prize }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section id="registro">
        <div class="card" id="app"></div>
    </section>

    <section class="info">
        <div style="display:flex;flex-direction:column;gap:10px;max-width:760px">
            <div class="eyebrow" style="letter-spacing:.12em">Información de referencia</div>
            <h2>¿Es la pitahaya una alternativa para su proyecto?</h2>
        </div>
        <div class="g4">
            <div class="ic"><small>Altitud y clima</small>
                <div>Menos de 2,000 msnm · templado a semicálido</div>
            </div>
            <div class="ic"><small>Temperatura</small>
                <div>Máximas cerca de 30 °C · mínimas de 1 a 5 °C</div>
            </div>
            <div class="ic"><small>Agua y riego</small>
                <div>Pozo profundo · goteo con fertirrigación · hasta 40,000 L/semana/ha</div>
            </div>
            <div class="ic"><small>Suelo y densidad</small>
                <div>Franco o franco-arcilloso, buen drenaje · ~5,600 plantas/ha</div>
            </div>
        </div>
        <div class="g5">
            <div class="dc"><small>Inversión inicial</small>
                <div>$700,000/ha</div>
            </div>
            <div class="dc"><small>Etapa comercial</small>
                <div>~3 años</div>
            </div>
            <div class="dc"><small>Vida productiva</small>
                <div>25–30 años</div>
            </div>
            <div class="dc"><small>Producción promedio</small>
                <div>25 t/ha</div>
            </div>
            <div class="dc"><small>Margen preliminar</small>
                <div style="color:#F2C230">$875,000/año</div>
            </div>
        </div>
        <p class="note">Valores de referencia por hectárea: 25,000 kg × $50 = $1,250,000 de ingreso; 25,000 kg × $15 =
            $375,000 de costo; diferencia de $875,000 (temporada 2026), antes de otros gastos, impuestos y recuperación
            de la inversión. Son ilustrativos y varían según variedad, calidad, temporada y mercado. Cada proyecto se
            evalúa individualmente (PROPEP, análisis financiero o prueba piloto).</p>
    </section>

    <section class="svc" id="servicios">
        <div class="svc-head">
            <div class="eyebrow" style="letter-spacing:.12em">Conozca más</div>
            <h2>Servicios PITAMEX para su proyecto</h2>
            <p>Si le interesa avanzar, estos son los programas con los que lo acompañamos, desde conocer la huerta hasta
                vender su fruta con nosotros. Toque cada uno para ver el detalle.</p>
        </div>

        <div class="svc-list">

            <details class="svc-item">
                <summary><span class="svc-n">1</span><span class="svc-t"><b>Visita a huertos
                            PITAMEX</b><small>Recorrido de 1 hora por nuestra huerta</small></span><span
                        class="svc-p">$2,000<small>2 personas</small></span><span class="svc-chev"
                        aria-hidden="true"></span></summary>
                <div class="svc-body">
                    <p>Un recorrido por nuestra huerta para conocer de cerca la planta de pitahaya y su proceso de
                        desarrollo: desde el cultivo hasta el empaque.</p>
                    <ul>
                        <li><b>Duración:</b> 1 hora.</li>
                        <li><b>Inversión:</b> $2,000 por 2 personas, con previa reservación y sujeto a disponibilidad.
                        </li>
                        <li><b>Requisitos por persona:</b> INE, constancia de situación fiscal, comprobante de domicilio
                            y comprobante de pago de la visita.</li>
                        <li>Se brinda información básica sobre la huerta y su proceso.</li>
                    </ul>
                    <a class="svc-wa"
                        href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20me%20interesa%20agendar%20una%20visita%20a%20la%20huerta."
                        target="_blank" rel="noopener">Agendar visita por WhatsApp</a>
                </div>
            </details>

            <details class="svc-item">
                <summary><span class="svc-n">2</span><span class="svc-t"><b>Asistencia técnica para su
                            cultivo</b><small>Asesoría de 0 a 10 años de edad de la planta</small></span><span
                        class="svc-p">$3,000<small>mensual</small></span><span class="svc-chev"
                        aria-hidden="true"></span></summary>
                <div class="svc-body">
                    <p>Un paquete personalizado según la etapa fenológica, las necesidades y las condiciones de cada
                        predio, para que tenga un cultivo de calidad.</p>
                    <ul>
                        <li><b>Incluye:</b> plan de manejo, control de plagas y enfermedades, nutrición, polinización y
                            cosecha.</li>
                        <li>Análisis de savia bimestral para revisar la nutrición de la planta.</li>
                        <li>Valoración de dos técnicos especializados cada 15 días vía remota y atención de dudas por
                            WhatsApp.</li>
                        <li><b>Asesoría mensual vía remota:</b> $3,000.</li>
                        <li><b>Asesoría presencial:</b> $3,000 por visita, sin viáticos. Aplica hasta 3 horas de
                            Moroleón (350 km a la redonda); más lejos, sujeto a revisión.</li>
                    </ul>
                    <a class="svc-wa"
                        href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20me%20interesa%20la%20asistencia%20t%C3%A9cnica%20para%20mi%20cultivo."
                        target="_blank" rel="noopener">Pedir información por WhatsApp</a>
                </div>
            </details>

            <details class="svc-item">
                <summary><span class="svc-n">3</span><span class="svc-t"><b>Programa de valoración de huertos
                            nuevos</b><small>Certificación del proyecto · ¿es apto su predio?</small></span><span
                        class="svc-p">desde $1,500<small>remota</small></span><span class="svc-chev"
                        aria-hidden="true"></span></summary>
                <div class="svc-body">
                    <p>Un técnico especializado valora su predio y emite un dictamen <b>positivo o negativo</b> sobre su
                        potencial para el cultivo de pitahaya.</p>
                    <ul>
                        <li><b>Incluye:</b> evaluación de posición geográfica y logística, condiciones climáticas y de
                            suelo, volumen de agua disponible, plagas, patógenos y hospederos de la región, y situación
                            fiscal y legal de la empresa.</li>
                        <li><b>Valoración de predio vía remota:</b> $1,500.</li>
                        <li><b>Certificación en sitio:</b> $3,000.</li>
                    </ul>
                    <a class="svc-wa"
                        href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20quiero%20valorar%20mi%20predio%20para%20pitahaya."
                        target="_blank" rel="noopener">Valorar mi predio</a>
                </div>
            </details>

            <details class="svc-item">
                <summary><span class="svc-n">4</span><span class="svc-t"><b>Proyección para el establecimiento de
                            huerto nuevo</b><small>PROPEP · más de 10 años de experiencia</small></span><span
                        class="svc-p">$15,000<small>por predio</small></span><span class="svc-chev"
                        aria-hidden="true"></span></summary>
                <div class="svc-body">
                    <p>Una propuesta para que invierta con un plan de acción claro:</p>
                    <ul>
                        <li>Prevención de riesgos del proyecto.</li>
                        <li>Posición geográfica y logística, clima y suelo, agua disponible, plagas y patógenos de la
                            región.</li>
                        <li>Mercado al que podrá aspirar.</li>
                        <li>Recomendación de tutor, marco y densidad de plantación, y de las variedades a implementar.
                        </li>
                    </ul>
                    <div class="svc-note"><b>Beneficio:</b> se le bonifica el 50&nbsp;% de esta inversión en productos
                        PITAMEX al comprar las plantas de su primera hectárea (5,600 plantas).</div>
                    <ul class="svc-small">
                        <li>No incluye gastos de viaje. Aplica hasta 3 horas de Moroleón (350 km a la redonda); más
                            lejos, sujeto a revisión.</li>
                        <li>El costo es por predio. Predios de más de 50 hectáreas se cotizan aparte.</li>
                    </ul>
                    <a class="svc-wa"
                        href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20me%20interesa%20la%20proyecci%C3%B3n%20para%20establecer%20un%20huerto%20nuevo."
                        target="_blank" rel="noopener">Pedir mi proyección</a>
                </div>
            </details>

            <details class="svc-item">
                <summary><span class="svc-n">5</span><span class="svc-t"><b>Corrida financiera
                            personalizada</b><small>Proyección financiera de su huerto nuevo</small></span><span
                        class="svc-p">Cotizar<small>&nbsp;</small></span><span class="svc-chev"
                        aria-hidden="true"></span></summary>
                <div class="svc-body">
                    <p>Un análisis financiero con la proyección de inversión, costos e ingresos de su huerto, hecho con
                        los datos de su predio, para que decida con números claros.</p>
                    <a class="svc-wa"
                        href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20me%20interesa%20una%20corrida%20financiera%20para%20mi%20huerto."
                        target="_blank" rel="noopener">Solicitar corrida financiera</a>
                </div>
            </details>

            <details class="svc-item">
                <summary><span class="svc-n">6</span><span class="svc-t"><b>Integración para proyectos
                            externos</b><small>¿Ya produce pitahaya? Venda su fruta con PITAMEX</small></span><span
                        class="svc-p">Reglas de<small>recepción</small></span><span class="svc-chev"
                        aria-hidden="true"></span></summary>
                <div class="svc-body">
                    <p>Si ya tiene huerta, puede entregar su fruta a PITAMEX en consignación. Estas son las reglas
                        principales de recepción:</p>
                    <div class="svc-grid">
                        <div>
                            <h4>Antes de entregar</h4>
                            <ul>
                                <li>Agende cita: no se recibe fruta sin cita.</li>
                                <li>Lunes, miércoles y viernes, 7:30 a.m. a 12:30 p.m.</li>
                                <li>Envíe peso aproximado y fotos de las cajas listas.</li>
                                <li>Identificación, mapa de carga, análisis de agua y bitácora de plaguicidas.</li>
                            </ul>
                        </div>
                        <div>
                            <h4>Empaque y transporte</h4>
                            <ul>
                                <li>Caja rígida, máximo 15 kg.</li>
                                <li>Cada caja con lote, fecha de cosecha, talla y productor.</li>
                                <li>Transporte limpio y protegido. Nunca a granel.</li>
                                <li>Fruta que cae al suelo se desecha.</li>
                            </ul>
                        </div>
                        <div>
                            <h4>La fruta debe cumplir</h4>
                            <ul>
                                <li>Sana, limpia y firme; color al 3/4.</li>
                                <li>Grados Brix entre 14° y 22°.</li>
                                <li>Cierre polínico cerrado; pedúnculo cortado con tijera (10 mm a 1 cm).</li>
                                <li>Peso desde 320 g nacional y 500 g exportación.</li>
                                <li>Vida de anaquel mínima de 3 semanas.</li>
                            </ul>
                        </div>
                        <div>
                            <h4>Inspección y pago</h4>
                            <ul>
                                <li>Muestreo del 7&nbsp;% de la carga; prueba destructiva hasta del 2&nbsp;%.</li>
                                <li>Dictamen: aceptada, parcial o rechazada.</li>
                                <li>Precio spot de mercado (SNIIM y USDA); comisión PITAMEX del 20&nbsp;%.</li>
                                <li>Pago de 2 a 8 semanas, una vez cobrado al cliente.</li>
                            </ul>
                        </div>
                    </div>
                    <figure class="svc-fig">
                        <a href="{{ asset('images/raffles/mini-manual-recepcion.jpg') }}" target="_blank"
                            rel="noopener"><img src="{{ asset('images/raffles/mini-manual-recepcion.jpg') }}"
                                onerror="this.closest('figure').remove()"
                                alt="Mini manual de recepción de fruta PITAMEX" loading="lazy"></a>
                        <figcaption>Mini manual de recepción de fruta · toque la imagen para verla en grande
                        </figcaption>
                    </figure>
                    <a class="svc-wa"
                        href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20ya%20produzco%20pitahaya%20y%20me%20interesa%20entregar%20mi%20fruta."
                        target="_blank" rel="noopener">Quiero vender mi fruta con PITAMEX</a>
                </div>
            </details>

        </div>
    </section>

    <footer>
        <div class="wrap">
            <div class="logo-box" style="padding:8px 12px"><img src="{{ $config['logo'] }}"
                    alt="PITAMEX · Pitahaya de México"></div>
            <div>
                <div style="font-weight:700;color:#fff">PITAMEX · Pitahaya Consultores de México SPR de RL</div>
                <div>Uriangato y Acámbaro, Guanajuato</div>
            </div>
            <div>
                <div>WhatsApp +52 445 106 3437</div>
                <div>pitamex@pitahayaorg.com · <a href="https://pitamex.org" style="color:#A5C84A">pitamex.org</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        var ESTADOS = ['Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas', 'Chihuahua',
            'Ciudad de México', 'Coahuila', 'Colima', 'Durango', 'Estado de México', 'Guanajuato', 'Guerrero',
            'Hidalgo', 'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla', 'Querétaro',
            'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz',
            'Yucatán', 'Zacatecas'
        ];
        var SN = ['Sí', 'No'];
        var STEPS = [{
                chip: 'Datos',
                title: 'Sus datos',
                intro: 'Para darle seguimiento y enviarle su número de la rifa.',
                qs: [{
                        k: 'nombre',
                        l: 'Nombre completo',
                        t: 'text',
                        expo: true
                    },
                    {
                        k: 'telefono',
                        l: 'Teléfono (WhatsApp)',
                        t: 'text',
                        it: 'tel',
                        expo: true
                    },
                    {
                        k: 'correo',
                        l: 'Correo electrónico',
                        t: 'text',
                        it: 'email',
                        opt: true,
                        expo: true,
                        ph: 'Si no tiene, déjelo vacío'
                    },
                    {
                        k: 'estado',
                        l: 'Estado',
                        t: 'select',
                        expo: true
                    },
                    {
                        k: 'municipio',
                        l: 'Municipio',
                        t: 'text',
                        expo: true
                    },
                    {
                        k: 'conocio',
                        l: '¿Cómo conoció PITAMEX?',
                        t: 'one',
                        o: ['Expo', 'Facebook', 'Instagram', 'Referido', 'Página web', 'Otro']
                    }
                ]
            },
            {
                chip: 'Predio',
                title: 'Su predio',
                intro: 'Superficie, tenencia y tipo de terreno.',
                qs: [{
                        k: 'superficie',
                        l: 'Superficie disponible',
                        t: 'one',
                        expo: true,
                        o: ['Más de 10 ha', '5 a 10 ha', '3 a 5 ha', '1 a 3 ha', 'Menos de 1 ha']
                    },
                    {
                        k: 'propia',
                        l: '¿La tierra es propia?',
                        t: 'one',
                        o: ['Sí', 'No', 'Parcialmente']
                    },
                    {
                        k: 'propiedad',
                        l: 'Tipo de propiedad',
                        t: 'one',
                        opt: true,
                        o: ['Privada', 'Ejidal', 'Arrendada', 'Comodato', 'Sociedad', 'Familiar']
                    },
                    {
                        k: 'terreno',
                        l: 'Tipo de terreno',
                        t: 'one',
                        opt: true,
                        o: ['Plano', 'Ladera', 'Mixto', 'Lo desconozco']
                    },
                    {
                        k: 'predios',
                        l: 'Si tiene 2 o más predios, describa las características de los adicionales',
                        t: 'area',
                        opt: true,
                        ph: 'Superficie, agua, suelo, ubicación…'
                    }
                ]
            },
            {
                chip: 'Clima',
                title: 'Clima y condiciones',
                intro: 'Es el factor más importante para elegir la variedad adecuada.',
                qs: [{
                        k: 'clima',
                        l: 'Tipo de clima',
                        t: 'one',
                        expo: true,
                        o: ['Templado seco', 'Templado subhúmedo', 'Semiárido', 'Tropical húmedo', 'Muy húmedo']
                    },
                    {
                        k: 'tmax',
                        l: 'Temperatura máxima aproximada',
                        t: 'one',
                        expo: true,
                        o: ['20–25 °C', '25–30 °C', '30–35 °C', 'Más de 35 °C']
                    },
                    {
                        k: 'tmin',
                        l: 'Temperatura mínima aproximada',
                        t: 'one',
                        expo: true,
                        o: ['0 °C o menos', '1–5 °C', '5–10 °C', 'Más de 10 °C']
                    },
                    {
                        k: 'heladas',
                        l: '¿Se presentan heladas?',
                        t: 'one',
                        o: ['Nunca', 'Ocasionalmente', 'Frecuentemente']
                    },
                    {
                        k: 'altitud',
                        l: 'Altitud aproximada',
                        t: 'one',
                        o: ['0–500 msnm', '500–1,000 msnm', '1,000–1,500 msnm', '1,500–2,000 msnm',
                            'Más de 2,000 msnm'
                        ]
                    },
                    {
                        k: 'precipitacion',
                        l: 'Precipitación aproximada',
                        t: 'one',
                        o: ['Baja', 'Media', 'Alta', 'Muy alta']
                    },
                    {
                        k: 'precip_mm',
                        l: 'Precipitación pluvial anual (mm), si la conoce',
                        t: 'text',
                        it: 'text',
                        opt: true,
                        ph: 'Ej. 650'
                    }
                ]
            },
            {
                chip: 'Agua y suelo',
                title: 'Agua, infraestructura y suelo',
                intro: 'La disponibilidad de agua define la viabilidad del proyecto.',
                qs: [{
                        k: 'fuente',
                        l: 'Fuente principal de agua',
                        t: 'one',
                        o: ['Pozo', 'Presa', 'Río', 'Temporal', 'Municipal', 'Otro']
                    },
                    {
                        k: 'disponibilidad',
                        l: 'Disponibilidad de agua',
                        t: 'one',
                        expo: true,
                        o: ['Abundante', 'Moderada', 'Limitada']
                    },
                    {
                        k: 'agua_max',
                        l: 'Cantidad máxima de agua disponible hoy en el predio',
                        t: 'text',
                        it: 'text',
                        opt: true,
                        ph: 'Ej. 30,000 L por semana o 5 L/s'
                    },
                    {
                        k: 'riego',
                        l: '¿Cuenta con riego?',
                        t: 'one',
                        o: SN
                    },
                    {
                        k: 'tipo_riego',
                        l: 'Tipo de riego',
                        t: 'one',
                        opt: true,
                        showIf: ['riego', 'Sí'],
                        o: ['Cintilla', 'Manguera con goteros', 'Aspersión', 'Pivote', 'Rodado', 'Otro']
                    },
                    {
                        k: 'electricidad',
                        l: '¿Cuenta con electricidad?',
                        t: 'one',
                        opt: true,
                        o: SN
                    },
                    {
                        k: 'conagua',
                        l: '¿Cuenta con documentación vigente de CONAGUA?',
                        t: 'one',
                        opt: true,
                        o: SN
                    },
                    {
                        k: 'conoce_suelo',
                        l: '¿Conoce el tipo de suelo?',
                        t: 'one',
                        opt: true,
                        expo: true,
                        o: SN
                    },
                    {
                        k: 'tipo_suelo',
                        l: 'Tipo de suelo',
                        t: 'one',
                        opt: true,
                        expo: true,
                        o: ['Arenoso', 'Franco', 'Arcilloso', 'Pedregoso', 'Mixto', 'Lo desconozco']
                    },
                    {
                        k: 'analisis',
                        l: '¿Cuenta con análisis de suelo?',
                        t: 'one',
                        opt: true,
                        o: SN
                    },
                    {
                        k: 'ph',
                        l: 'pH aproximado',
                        t: 'one',
                        opt: true,
                        o: ['Menos de 5.5', '5.5–6.5', '6.5–7.5', '7.5–8.5', 'Más de 8.5', 'Lo desconozco']
                    },
                    {
                        k: 'drenaje',
                        l: '¿Problemas de drenaje?',
                        t: 'one',
                        opt: true,
                        o: ['Ninguno', 'Moderado', 'Severo']
                    }
                ]
            },
            {
                chip: 'Capacidad',
                title: 'Experiencia y capacidad',
                intro: 'Nos ayuda a proponerle el acompañamiento adecuado.',
                qs: [{
                        k: 'experiencia',
                        l: 'Experiencia agrícola',
                        t: 'one',
                        expo: true,
                        o: ['Ninguna', 'Básica', 'Media', 'Avanzada']
                    },
                    {
                        k: 'cultivos',
                        l: 'Cultivos actuales',
                        t: 'text',
                        it: 'text',
                        opt: true,
                        expo: true,
                        ph: 'Ej. maíz, aguacate, sorgo'
                    },
                    {
                        k: 'asesor',
                        l: '¿Cuenta con asesor técnico?',
                        t: 'one',
                        opt: true,
                        o: SN
                    },
                    {
                        k: 'personal',
                        l: 'Personal agrícola fijo',
                        t: 'one',
                        opt: true,
                        o: ['1 a 5', '6 a 30', '31 a 100', 'Más de 100']
                    },
                    {
                        k: 'certificacion',
                        l: '¿Cuenta con alguna certificación (Primus, Global G.A.P.)?',
                        t: 'one',
                        opt: true,
                        expo: true,
                        o: SN
                    },
                    {
                        k: 'inversion',
                        l: 'Rango de inversión disponible',
                        t: 'one',
                        o: ['Menos de $200 mil', '$200 mil – $700 mil', '$700 mil – $2 millones',
                            'Más de $2 millones'
                        ]
                    },
                    {
                        k: 'financiamiento',
                        l: '¿El proyecto dependería de financiamiento?',
                        t: 'one',
                        opt: true,
                        o: ['Sí', 'Parcialmente', 'No']
                    },
                    {
                        k: 'infraestructura',
                        l: 'Infraestructura disponible',
                        t: 'multi',
                        opt: true,
                        o: ['Bodega', 'Tractor', 'Cercado', 'Riego', 'Ninguna']
                    },
                    {
                        k: 'factura',
                        l: '¿Factura actividad agrícola?',
                        t: 'one',
                        opt: true,
                        o: SN
                    },
                    {
                        k: 'regimen',
                        l: 'Régimen fiscal',
                        t: 'one',
                        opt: true,
                        o: ['Persona física', 'Persona moral', 'Ejidal', 'Otro']
                    },
                    {
                        k: 'acta',
                        l: '¿Cuenta con acta constitutiva?',
                        t: 'one',
                        opt: true,
                        o: ['Sí', 'No', 'No aplica']
                    },
                    {
                        k: 'regularizada',
                        l: '¿La propiedad está regularizada?',
                        t: 'one',
                        opt: true,
                        o: ['Sí', 'No', 'Parcialmente']
                    }
                ]
            },
            {
                chip: 'Visión',
                title: 'Su visión y su proyecto',
                intro: 'Lo que busca y en qué etapa está.',
                qs: [{
                        k: 'busca',
                        l: '¿Qué busca principalmente?',
                        t: 'one',
                        expo: true,
                        o: ['Rentabilidad', 'Exportar', 'Proyecto familiar', 'Diversificar', 'Aprender']
                    },
                    {
                        k: 'comercializar',
                        l: '¿Le interesaría comercializar con PITAMEX?',
                        t: 'one',
                        o: ['Sí', 'No', 'Tal vez']
                    },
                    {
                        k: 'limpia',
                        l: '¿Le interesa la producción limpia o libre de pesticidas?',
                        t: 'one',
                        opt: true,
                        expo: true,
                        o: SN
                    },
                    {
                        k: 'inocuidad',
                        l: '¿Le interesaría producir con inocuidad?',
                        t: 'one',
                        opt: true,
                        o: ['Sí', 'No', 'Quiero más información']
                    },
                    {
                        k: 'valora',
                        l: '¿Qué valora más?',
                        t: 'one',
                        o: ['Precio', 'Calidad', 'Mercado seguro', 'Asesoría', 'Exportación']
                    },
                    {
                        k: 'etapa',
                        l: '¿En qué etapa está su proyecto?',
                        t: 'cards',
                        o: [{
                                l: 'Inicial',
                                d: 'Apenas lo estoy planeando.'
                            },
                            {
                                l: 'En proceso',
                                d: 'Ya tengo cerca, postes o sistema de riego, pero faltan cosas.'
                            },
                            {
                                l: 'Avanzado',
                                d: 'Ya tengo todo, solo busco esquejes.'
                            }
                        ]
                    },
                    {
                        k: 'decision',
                        l: 'Nivel de decisión',
                        t: 'one',
                        opt: true,
                        o: ['Evaluando', '70% seguro', '100% seguro']
                    },
                    {
                        k: 'iniciar',
                        l: 'Tiempo estimado para iniciar',
                        t: 'one',
                        opt: true,
                        o: ['Sin fecha', 'En un futuro', 'Próximo ciclo', 'Inmediatamente']
                    },
                    {
                        k: 'prioridad',
                        l: '¿Cuál sería su prioridad al desarrollar un proyecto de pitahaya?',
                        t: 'cards',
                        opt: true,
                        o: [{
                                l: 'Rentabilidad',
                                d: 'Obtener buenos rendimientos y recuperar la inversión.'
                            },
                            {
                                l: 'Patrimonio a largo plazo',
                                d: 'Construir un negocio agrícola sólido y duradero.'
                            },
                            {
                                l: 'Comercialización segura',
                                d: 'Contar con mercados confiables para vender mi producción.'
                            },
                            {
                                l: 'Innovación y productividad',
                                d: 'Desarrollar un cultivo tecnificado, eficiente y competitivo.'
                            },
                            {
                                l: 'Sostenibilidad y calidad',
                                d: 'Producir responsablemente, cuidando los recursos y ofreciendo fruta de calidad.'
                            }
                        ]
                    },
                    {
                        k: 'comentarios',
                        l: 'Comentarios, dudas o preguntas',
                        t: 'area',
                        opt: true,
                        ph: 'Escríbanos lo que quiera saber'
                    }
                ]
            }
        ];
        var SCORING = {
            "pesos": {
                "Máximo": 5,
                "Muy alto": 4,
                "Alto": 3,
                "Medio": 2
            },
            "preguntas": {
                "superficie": {
                    "peso": "Muy alto",
                    "valores": {
                        "Más de 10 ha": 1,
                        "5 a 10 ha": 0.9,
                        "3 a 5 ha": 0.75,
                        "1 a 3 ha": 0.5,
                        "Menos de 1 ha": 0.3
                    },
                    "tipo": null
                },
                "propia": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "Parcialmente": 0.6,
                        "No": 0.3
                    },
                    "tipo": null
                },
                "propiedad": {
                    "peso": "Medio",
                    "valores": {
                        "Privada": 1,
                        "Familiar": 0.8,
                        "Sociedad": 0.8,
                        "Ejidal": 0.6,
                        "Arrendada": 0.5,
                        "Comodato": 0.4
                    },
                    "tipo": null
                },
                "ubicacion": {
                    "peso": "Muy alto",
                    "valores": {
                        "compartida": 1
                    },
                    "tipo": null
                },
                "terreno": {
                    "peso": "Medio",
                    "valores": {
                        "Plano": 1,
                        "Mixto": 0.7,
                        "Ladera": 0.5,
                        "Lo desconozco": 0.4
                    },
                    "tipo": null
                },
                "clima": {
                    "peso": "Máximo",
                    "valores": {
                        "Templado subhúmedo": 1,
                        "Templado seco": 0.85,
                        "Semiárido": 0.7,
                        "Tropical húmedo": 0.6,
                        "Muy húmedo": 0.3
                    },
                    "tipo": null
                },
                "tmax": {
                    "peso": "Muy alto",
                    "valores": {
                        "25–30 °C": 1,
                        "30–35 °C": 0.8,
                        "20–25 °C": 0.7,
                        "Más de 35 °C": 0.4
                    },
                    "tipo": null
                },
                "tmin": {
                    "peso": "Máximo",
                    "valores": {
                        "5–10 °C": 1,
                        "Más de 10 °C": 0.9,
                        "1–5 °C": 0.85,
                        "0 °C o menos": 0.2
                    },
                    "tipo": null
                },
                "heladas": {
                    "peso": "Máximo",
                    "valores": {
                        "Nunca": 1,
                        "Ocasionalmente": 0.5,
                        "Frecuentemente": 0.1
                    },
                    "tipo": null
                },
                "altitud": {
                    "peso": "Muy alto",
                    "valores": {
                        "0–500 msnm": 1,
                        "500–1,000 msnm": 1,
                        "1,000–1,500 msnm": 1,
                        "1,500–2,000 msnm": 0.85,
                        "Más de 2,000 msnm": 0.3
                    },
                    "tipo": null
                },
                "precipitacion": {
                    "peso": "Muy alto",
                    "valores": {
                        "Media": 1,
                        "Baja": 0.8,
                        "Alta": 0.6,
                        "Muy alta": 0.3
                    },
                    "tipo": null
                },
                "fuente": {
                    "peso": "Muy alto",
                    "valores": {
                        "Pozo": 1,
                        "Presa": 0.8,
                        "Río": 0.7,
                        "Municipal": 0.5,
                        "Otro": 0.5,
                        "Temporal": 0.2
                    },
                    "tipo": null
                },
                "disponibilidad": {
                    "peso": "Muy alto",
                    "valores": {
                        "Abundante": 1,
                        "Moderada": 0.7,
                        "Limitada": 0.3
                    },
                    "tipo": null
                },
                "riego": {
                    "peso": "Alto",
                    "valores": {
                        "Sí": 1,
                        "No": 0.3
                    },
                    "tipo": null
                },
                "tipo_riego": {
                    "peso": "Medio",
                    "valores": {
                        "Manguera con goteros": 1,
                        "Cintilla": 0.9,
                        "Aspersión": 0.5,
                        "Pivote": 0.5,
                        "Otro": 0.5,
                        "Rodado": 0.3
                    },
                    "tipo": null
                },
                "electricidad": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "No": 0.3
                    },
                    "tipo": null
                },
                "conagua": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "No": 0.4
                    },
                    "tipo": null
                },
                "conoce_suelo": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "No": 0.5
                    },
                    "tipo": null
                },
                "tipo_suelo": {
                    "peso": "Muy alto",
                    "valores": {
                        "Franco": 1,
                        "Mixto": 0.7,
                        "Arcilloso": 0.6,
                        "Arenoso": 0.6,
                        "Lo desconozco": 0.5,
                        "Pedregoso": 0.4
                    },
                    "tipo": null
                },
                "analisis": {
                    "peso": "Alto",
                    "valores": {
                        "Sí": 1,
                        "No": 0.4
                    },
                    "tipo": null
                },
                "ph": {
                    "peso": "Alto",
                    "valores": {
                        "6.5–7.5": 1,
                        "5.5–6.5": 0.8,
                        "7.5–8.5": 0.7,
                        "Lo desconozco": 0.5,
                        "Menos de 5.5": 0.4,
                        "Más de 8.5": 0.3
                    },
                    "tipo": null
                },
                "drenaje": {
                    "peso": "Muy alto",
                    "valores": {
                        "Ninguno": 1,
                        "Moderado": 0.5,
                        "Severo": 0.1
                    },
                    "tipo": null
                },
                "experiencia": {
                    "peso": "Alto",
                    "valores": {
                        "Avanzada": 1,
                        "Media": 0.8,
                        "Básica": 0.5,
                        "Ninguna": 0.3
                    },
                    "tipo": null
                },
                "asesor": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "No": 0.6
                    },
                    "tipo": null
                },
                "personal": {
                    "peso": "Medio",
                    "valores": {
                        "1 a 5": 0.6,
                        "6 a 30": 0.9,
                        "31 a 100": 1,
                        "Más de 100": 1
                    },
                    "tipo": null
                },
                "certificacion": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "No": 0.5
                    },
                    "tipo": null
                },
                "inversion": {
                    "peso": "Muy alto",
                    "valores": {
                        "Más de $2 millones": 1,
                        "$700 mil – $2 millones": 0.9,
                        "$200 mil – $700 mil": 0.5,
                        "Menos de $200 mil": 0.2
                    },
                    "tipo": null
                },
                "financiamiento": {
                    "peso": "Alto",
                    "valores": {
                        "No": 1,
                        "Parcialmente": 0.7,
                        "Sí": 0.4
                    },
                    "tipo": null
                },
                "infraestructura": {
                    "peso": "Alto",
                    "valores": null,
                    "tipo": "multi"
                },
                "busca": {
                    "peso": "Alto",
                    "valores": {
                        "Rentabilidad": 1,
                        "Exportar": 1,
                        "Proyecto familiar": 0.8,
                        "Diversificar": 0.8,
                        "Aprender": 0.6
                    },
                    "tipo": null
                },
                "comercializar": {
                    "peso": "Muy alto",
                    "valores": {
                        "Sí": 1,
                        "Tal vez": 0.6,
                        "No": 0.2
                    },
                    "tipo": null
                },
                "limpia": {
                    "peso": "Muy alto",
                    "valores": {
                        "Sí": 1,
                        "No": 0.3
                    },
                    "tipo": null
                },
                "inocuidad": {
                    "peso": "Alto",
                    "valores": {
                        "Sí": 1,
                        "Quiero más información": 0.7,
                        "No": 0.2
                    },
                    "tipo": null
                },
                "valora": {
                    "peso": "Muy alto",
                    "valores": {
                        "Calidad": 1,
                        "Mercado seguro": 1,
                        "Asesoría": 0.9,
                        "Exportación": 0.9,
                        "Precio": 0.5
                    },
                    "tipo": null
                },
                "factura": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "No": 0.4
                    },
                    "tipo": null
                },
                "regimen": {
                    "peso": "Medio",
                    "valores": {
                        "Persona moral": 1,
                        "Persona física": 0.9,
                        "Ejidal": 0.7,
                        "Otro": 0.6
                    },
                    "tipo": null
                },
                "acta": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "No aplica": 0.8,
                        "No": 0.5
                    },
                    "tipo": null
                },
                "regularizada": {
                    "peso": "Medio",
                    "valores": {
                        "Sí": 1,
                        "Parcialmente": 0.5,
                        "No": 0.2
                    },
                    "tipo": null
                }
            }
        };

        // Datos que manda Laravel (RaffleInvitationController@show)
        var CFG = @json($config);
        var FECHA_SORTEO = CFG.drawDate || 'fecha por confirmar';
        var CSRF = document.querySelector('meta[name="csrf-token"]').content;
        // Respaldo local por si se cae la conexión (en el link abierto también guarda el token del registro)
        var KEY = CFG.open ? 'pitamex-rifa-open-' + CFG.code : 'pitamex-rifa-' + CFG.token;

        function load() {
            try {
                return JSON.parse(localStorage.getItem(KEY)) || null;
            } catch (e) {
                return null;
            }
        }

        function save() {
            try {
                localStorage.setItem(KEY, JSON.stringify(S));
            } catch (e) {}
        }

        // Normaliza textos para comparar preguntas de la Expo con las del registro
        function norm(s) {
            return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/^\s*\d+\s*[.\-)]+\s*/, '').replace(/[^a-z0-9 ]/g, ' ').replace(/\s+/g, ' ').trim();
        }

        // Prellenado: datos de contacto + respuestas de la Expo que coincidan con alguna pregunta
        var EXPO_KEYS = [];

        function prellenar() {
            var a = Object.assign({
                infraestructura: []
            }, CFG.prefill || {});
            Object.keys(CFG.prefill || {}).forEach(function(k) {
                EXPO_KEYS.push(k);
            });
            var expo = CFG.expoAnswers || {};
            var expoKeys = Object.keys(expo).map(function(k) {
                return {
                    k: k,
                    n: norm(k)
                };
            });
            STEPS.forEach(function(st) {
                st.qs.forEach(function(q) {
                    if (!vacio(a[q.k])) {
                        return;
                    }
                    var ql = norm(q.l);
                    var hit = expoKeys.find(function(e) {
                        return e.n && ql && (e.n === ql || (e.n.length >= 6 && ql.indexOf(e.n) >=
                            0) || (ql.length >= 6 && e.n.indexOf(ql) >= 0));
                    });
                    if (!hit) {
                        return;
                    }
                    var v = String(expo[hit.k]).trim();
                    if (q.t === 'one') {
                        var o = q.o.find(function(x) {
                            return norm(x) === norm(v);
                        });
                        if (!o) {
                            return;
                        }
                        v = o;
                    } else if (q.t === 'select') {
                        var e2 = ESTADOS.find(function(x) {
                            return norm(x) === norm(v);
                        });
                        if (!e2) {
                            return;
                        }
                        v = e2;
                    } else if (q.t !== 'text' && q.t !== 'area') {
                        return;
                    }
                    a[q.k] = v;
                    EXPO_KEYS.push(q.k);
                });
            });
            return a;
        }

        var S;
        var localOpen = CFG.open ? load() : null;
        if (localOpen && localOpen.token) {
            // Link abierto: este dispositivo ya había empezado (o terminado) un registro
            S = localOpen;
            CFG.token = S.token;
            CFG.progressUrl = S.progressUrl;
            CFG.confirmUrl = S.confirmUrl;
        } else if (CFG.number) {
            S = {
                step: 5,
                done: true,
                numero: CFG.number,
                a: Object.assign({
                    infraestructura: []
                }, CFG.saved || {})
            };
        } else {
            var local = load();
            var base = CFG.saved ? Object.assign({
                infraestructura: []
            }, CFG.saved) : prellenar();
            S = {
                step: CFG.step || 0,
                done: false,
                numero: null,
                a: base
            };
            // Si en este dispositivo había más avance sin guardar en el servidor, se recupera
            if (local && !local.done && local.step >= S.step) {
                S.step = local.step;
                S.a = Object.assign(S.a, local.a);
            }
        }
        var errores = [];
        var enviando = false;
        var errorServidor = '';

        function post(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(body)
            }).then(function(r) {
                return r.json().catch(function() {
                    return {};
                }).then(function(j) {
                    return {
                        ok: r.ok,
                        status: r.status,
                        json: j
                    };
                });
            });
        }

        // Solo las respuestas de preguntas visibles (ej. sin "tipo de riego" si no tiene riego)
        function respuestas() {
            var out = {};
            STEPS.forEach(function(st) {
                visibles(st).forEach(function(q) {
                    if (!vacio(S.a[q.k])) {
                        out[q.k] = S.a[q.k];
                    }
                });
            });
            out._priv = !!S.a._priv;
            return out;
        }

        function guardarAvance() {
            if (!CFG.token) {
                return;
            }
            post(CFG.progressUrl, {
                answers: respuestas(),
                step: S.step
            }).catch(function() {});
        }

        function mensajeError(r, porDefecto) {
            var errs = r.json && r.json.errors ? Object.values(r.json.errors) : [];
            return (errs[0] && errs[0][0]) || (r.json && r.json.message) || porDefecto;
        }

        // Link abierto: el registro se crea en el servidor al terminar el paso 1
        function asegurarRegistro() {
            if (CFG.token) {
                return Promise.resolve();
            }
            return post(CFG.startUrl, {
                answers: respuestas(),
                step: Math.max(S.step, 1)
            }).then(function(r) {
                if (!r.ok || !r.json.token) {
                    throw new Error(mensajeError(r, 'No se pudo guardar su registro. Intente de nuevo.'));
                }
                CFG.token = S.token = r.json.token;
                CFG.progressUrl = S.progressUrl = r.json.progressUrl;
                CFG.confirmUrl = S.confirmUrl = r.json.confirmUrl;
                save();
            }, function() {
                throw new Error(
                'Sin conexión. Revise su internet e intente de nuevo; sus respuestas se conservan.');
            });
        }
        var app = document.getElementById('app');

        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c];
            });
        }

        function visibles(st) {
            return st.qs.filter(function(q) {
                return !q.showIf || S.a[q.showIf[0]] === q.showIf[1];
            });
        }

        function vacio(v) {
            return v == null || v === '' || (Array.isArray(v) && !v.length);
        }

        function render() {
            if (S.done) {
                return renderDone();
            }
            var st = STEPS[S.step],
                last = S.step === STEPS.length - 1,
                pct = Math.round((S.step + 1) / STEPS.length * 100);
            var h = '<div class="top"><div class="row"><div class="eyebrow">Paso ' + (S.step + 1) +
                ' de 6</div><div class="muted">' + pct + '% completo</div></div>' +
                '<div class="bar"><div style="width:' + pct + '%"></div></div><div class="chips">' +
                STEPS.map(function(s, i) {
                    return '<button type="button" class="chip ' + (i === S.step ? 'on' : i < S.step ? 'ok' : '') +
                        '" data-go="' + i + '">' + (i + 1) + ' · ' + s.chip + '</button>';
                }).join('') +
                '</div></div><div class="body"><div><h2>' + st.title + '</h2><p class="intro">' + st.intro + '</p></div>';
            if (errores.length) {
                h += '<div class="alert" role="alert">Falta contestar ' + errores.length + (errores.length === 1 ?
                    ' pregunta' : ' preguntas') + ' marcada' + (errores.length === 1 ? '' : 's') + ' en rojo.</div>';
            }
            if (errorServidor) {
                h += '<div class="alert" role="alert">' + esc(errorServidor) + '</div>';
            }
            visibles(st).forEach(function(q) {
                h += renderQ(q);
            });
            if (last) {
                h += '<label class="priv"><input type="checkbox" id="priv"' + (S.a._priv ? ' checked' : '') +
                    '><span>Acepto el <a href="' + esc(CFG.privacyUrl) +
                    '" target="_blank" rel="noopener">aviso de privacidad</a> y que PITAMEX me contacte para dar seguimiento a mi proyecto.</span></label>';
            }
            h += '</div><div class="foot">' + (S.step > 0 ?
                    '<button type="button" class="back" data-act="back">← Atrás</button>' :
                    '<span class="muted" style="font-weight:500">Se guarda su avance en cada paso</span>') +
                '<button type="button" class="next" data-act="next"' + (enviando ? ' disabled' : '') + '>' + (enviando ?
                    'Enviando…' : last ? 'Enviar y obtener mi número' : 'Siguiente →') + '</button></div>';
            app.innerHTML = h;
        }

        function renderQ(q) {
            var v = S.a[q.k],
                err = errores.indexOf(q.k) >= 0,
                id = 'q_' + q.k;
            var h = '<div class="q' + (err ? ' err' : '') + '" id="w_' + q.k +
                '"><div class="qhead"><label class="lbl" for="' + id + '">' + q.l + '</label>' +
                (q.opt ? '<span class="badge">Opcional</span>' : '') + (EXPO_KEYS.indexOf(q.k) >= 0 ?
                    '<span class="badge expo">De la Expo · confírmelo</span>' : '') +
                (err ? '<span class="req">Requerido</span>' : '') + '</div>';
            if (q.t === 'text') {
                h += '<input class="field" id="' + id + '" data-k="' + q.k + '" type="' + (q.it || 'text') + '" value="' +
                    esc(v) + '" placeholder="' + esc(q.ph || '') + '"' + (q.it === 'tel' ?
                        ' inputmode="tel" autocomplete="tel"' : '') + (q.it === 'email' ? ' autocomplete="email"' : '') + (q
                        .k === 'nombre' ? ' autocomplete="name"' : '') + '>';
            } else if (q.t === 'area') {
                h += '<textarea class="field" id="' + id + '" data-k="' + q.k + '" rows="3" placeholder="' + esc(q.ph ||
                    '') + '">' + esc(v) + '</textarea>';
            } else if (q.t === 'select') {
                h += '<select class="field" id="' + id + '" data-k="' + q.k +
                    '"><option value="">Seleccione su estado</option>' + ESTADOS.map(function(e) {
                        return '<option' + (v === e ? ' selected' : '') + '>' + e + '</option>';
                    }).join('') + '</select>';
            } else if (q.t === 'loc') {
                h += v && v.lat ? '<div class="loc done">✓ Ubicación compartida</div>' :
                    '<button type="button" class="loc" data-loc="' + q.k +
                    '"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#173A2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>Compartir ubicación del predio</button><div class="hint" id="lochint"></div>';
            } else if (q.t === 'cards') {
                h += '<div class="cards">' + q.o.map(function(o) {
                    return '<button type="button" class="opt' + (v === o.l ? ' on' : '') + '" aria-pressed="' + (
                            v === o.l) + '" data-pick="' + q.k + '" data-v="' + esc(o.l) + '"><b>' + o.l +
                        '</b><span>' + o.d + '</span></button>';
                }).join('') + '</div>';
            } else {
                var multi = q.t === 'multi';
                h += '<div class="pills">' + q.o.map(function(o) {
                    var on = multi ? (v || []).indexOf(o) >= 0 : v === o;
                    return '<button type="button" class="pill' + (on ? ' on' : '') + '" aria-pressed="' + on +
                        '" data-' + (multi ? 'multi' : 'pick') + '="' + q.k + '" data-v="' + esc(o) + '">' + o +
                        '</button>';
                }).join('') + '</div>';
                if (multi) {
                    h += '<div class="hint">Puede elegir varias</div>';
                }
            }
            return h + '</div>';
        }

        function validar() {
            errores = visibles(STEPS[S.step]).filter(function(q) {
                return !q.opt && vacio(S.a[q.k]);
            }).map(function(q) {
                return q.k;
            });
            if (S.step === 0 && !errores.length) {
                var tel = (S.a.telefono || '').replace(/\D/g, '');
                if (tel.length < 10) {
                    errores.push('telefono');
                }
            }
            return !errores.length;
        }

        function renderDone() {
            var nombre = (S.a.nombre || '').trim().split(' ')[0];
            var canal = S.a.correo ? 'También se lo enviamos por correo.' : 'Guárdelo: es su boleto para el sorteo.';
            app.innerHTML = '<div class="done"><img src="' + esc(CFG.logo) +
                '" alt="PITAMEX" style="height:96px;width:auto">' +
                '<div class="check"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#173A2C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg></div>' +
                '<div style="font-size:26px;font-weight:800;line-height:1.2;color:#173A2C">¡Gracias' + (nombre ? ', ' + esc(
                    nombre) : '') + '! Su registro está completo</div>' +
                '<div class="ticket"><img src="' + esc(CFG.logo) +
                '" alt="" style="height:52px;width:auto;margin:0 auto 4px"><div class="eyebrow" style="color:#9E1249;letter-spacing:.12em">Su número para la rifa</div><div class="num">' +
                esc(S.numero) + '</div><div style="font-size:14px;color:#56645C">Sorteo con la Lotería Nacional · ' + esc(
                    FECHA_SORTEO) + '</div></div>' +
                '<div style="font-size:15px;line-height:1.6;color:#56645C;max-width:480px">' + canal +
                ' Un asesor de PITAMEX lo contactará para programar una evaluación inicial de su proyecto.</div>' +
                '<div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center">' + (CFG.open ?
                    '<button type="button" class="next" style="min-height:48px;font-size:15px" data-act="otro">Registrar a otra persona</button>' :
                    '') +
                '<a href="https://pitamex.org" style="background:#173A2C;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 20px;border-radius:10px">Conocer más de PITAMEX</a></div></div>';
        }

        function irArriba() {
            document.getElementById('registro').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        app.addEventListener('input', function(e) {
            var k = e.target.dataset.k;
            if (k) {
                S.a[k] = e.target.value;
                save();
            }
            if (e.target.id === 'priv') {
                S.a._priv = e.target.checked;
                save();
            }
        });
        app.addEventListener('change', function(e) {
            var k = e.target.dataset.k;
            if (k) {
                S.a[k] = e.target.value;
                save();
            }
            if (e.target.id === 'priv') {
                S.a._priv = e.target.checked;
                save();
            }
        });
        app.addEventListener('click', function(e) {
            var b = e.target.closest('button');
            if (!b) {
                return;
            }
            var d = b.dataset;
            if (d.go) {
                if (+d.go > S.step && CFG.open && !CFG.token) {
                    return;
                }
                if (+d.go > S.step && !validar()) {
                    render();
                    return;
                }
                S.step = +d.go;
                errores = [];
                save();
                guardarAvance();
                render();
                irArriba();
                return;
            }
            if (d.pick) {
                S.a[d.pick] = d.v;
                errores = errores.filter(function(x) {
                    return x !== d.pick;
                });
                save();
                render();
                return;
            }
            if (d.multi) {
                var arr = (S.a[d.multi] || []).slice(),
                    i = arr.indexOf(d.v);
                if (d.v === 'Ninguna') {
                    arr = i >= 0 ? [] : ['Ninguna'];
                } else {
                    arr = arr.filter(function(x) {
                        return x !== 'Ninguna';
                    });
                    i = arr.indexOf(d.v);
                    if (i >= 0) {
                        arr.splice(i, 1);
                    } else {
                        arr.push(d.v);
                    }
                }
                S.a[d.multi] = arr;
                save();
                render();
                return;
            }
            if (d.loc) {
                var hint = document.getElementById('lochint');
                if (!navigator.geolocation) {
                    hint.textContent = 'Su navegador no permite compartir ubicación. Puede continuar sin ella.';
                    return;
                }
                hint.textContent = 'Obteniendo ubicación…';
                navigator.geolocation.getCurrentPosition(function(p) {
                        S.a[d.loc] = {
                            lat: p.coords.latitude,
                            lng: p.coords.longitude
                        };
                        save();
                        render();
                    },
                    function() {
                        hint.textContent =
                            'No se pudo obtener la ubicación. Es opcional: puede continuar sin ella.';
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    });
                return;
            }
            if (d.act === 'otro') {
                try {
                    localStorage.removeItem(KEY);
                } catch (e) {}
                location.reload();
                return;
            }
            if (d.act === 'back') {
                S.step = Math.max(0, S.step - 1);
                errores = [];
                save();
                render();
                irArriba();
                return;
            }
            if (d.act === 'next') {
                if (enviando) {
                    return;
                }
                errorServidor = '';
                if (!validar()) {
                    render();
                    var w = document.getElementById('w_' + errores[0]);
                    if (w) {
                        w.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                    return;
                }
                if (S.step === STEPS.length - 1) {
                    if (!S.a._priv) {
                        app.querySelector('.priv').style.color = '#B3123F';
                        app.querySelector('.priv').scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return;
                    }
                    enviando = true;
                    render();
                    asegurarRegistro().then(function() {
                        return post(CFG.confirmUrl, {
                            answers: respuestas()
                        }).then(function(r) {
                            enviando = false;
                            if (r.ok && r.json.number) {
                                S.numero = r.json.number;
                                S.done = true;
                                errores = [];
                                save();
                                render();
                                irArriba();
                                return;
                            }
                            errorServidor = mensajeError(r,
                                'No se pudo enviar su registro. Intente de nuevo.');
                            render();
                        }, function() {
                            enviando = false;
                            errorServidor =
                                'Sin conexión. Revise su internet e intente de nuevo; sus respuestas se conservan.';
                            render();
                        });
                    }).catch(function(e) {
                        enviando = false;
                        errorServidor = e.message;
                        render();
                    });
                    return;
                }
                // Link abierto: al terminar el paso 1 se crea el registro (y se valida que el teléfono no tenga boleto)
                if (CFG.open && !CFG.token) {
                    enviando = true;
                    render();
                    asegurarRegistro().then(function() {
                        enviando = false;
                        S.step++;
                        errores = [];
                        save();
                        render();
                        irArriba();
                    }).catch(function(e) {
                        enviando = false;
                        errorServidor = e.message;
                        render();
                    });
                    return;
                }
                S.step++;
                errores = [];
                save();
                guardarAvance();
                render();
                irArriba();
            }
        });

        render();
    </script>
</body>

</html>
