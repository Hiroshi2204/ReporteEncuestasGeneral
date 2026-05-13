<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes — Sistema Académico</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:       #0b1120;
            --navy-card:  #111827;
            --navy-hover: #151f32;
            --gold:       #f5a623;
            --gold-light: #ffd080;
            --gold-dim:   rgba(245,166,35,0.12);
            --gold-border:rgba(245,166,35,0.3);
            --blue:       #3b82f6;
            --blue-dim:   rgba(59,130,246,0.12);
            --blue-border:rgba(59,130,246,0.3);
            --teal:       #14b8a6;
            --teal-dim:   rgba(20,184,166,0.12);
            --teal-border:rgba(20,184,166,0.3);
            --text-primary:   #eef2ff;
            --text-secondary: #94a3b8;
            --text-muted:     #3d5270;
            --border:         rgba(255,255,255,0.06);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--navy);
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* ── Ambient background ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 70% 55% at 15%  20%, rgba(59,130,246,0.09) 0%, transparent 60%),
                radial-gradient(ellipse 55% 45% at 85%  80%, rgba(245,166,35,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 50%  50%, rgba(20,184,166,0.04) 0%, transparent 55%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── Grid dots ── */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        .page-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 900px;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3rem;
        }

        /* ── Top brand / eyebrow ── */
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            animation: fadeDown 0.7s ease both;
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            background: var(--gold-dim);
            border: 1px solid var(--gold-border);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo svg {
            width: 28px;
            height: 28px;
            color: var(--gold);
        }

        .brand-eyebrow {
            font-family: 'Syne', sans-serif;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        /* ── Main heading ── */
        .heading-block {
            text-align: center;
            animation: fadeDown 0.7s 0.08s ease both;
        }

        .main-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.05;
            color: var(--text-primary);
        }

        .main-title span {
            color: var(--gold);
        }

        .main-subtitle {
            margin-top: 0.75rem;
            font-size: 1rem;
            font-weight: 300;
            color: var(--text-secondary);
            max-width: 440px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        /* ── Divider line ── */
        .divider {
            width: 48px;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), transparent);
            border-radius: 99px;
            margin: 0.5rem auto 0;
        }

        /* ── Cards grid ── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            width: 100%;
        }

        @media (max-width: 600px) {
            .cards-grid { grid-template-columns: 1fr; }
        }

        /* ── Report card ── */
        .report-card {
            position: relative;
            background: var(--navy-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem 1.75rem 1.75rem;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            overflow: hidden;
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
            cursor: pointer;
        }

        .report-card::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .report-card:hover {
            transform: translateY(-6px);
        }

        /* Docentes — gold */
        .card-docentes {
            animation: fadeUp 0.6s 0.25s ease both;
        }

        .card-docentes::before {
            background: radial-gradient(ellipse 80% 60% at 50% 0%, var(--gold-dim), transparent);
        }

        .card-docentes:hover {
            border-color: var(--gold-border);
            box-shadow: 0 20px 60px rgba(245,166,35,0.15);
        }

        .card-docentes::before { opacity: 1; }

        /* Estudiantes — teal */
        .card-estudiantes {
            animation: fadeUp 0.6s 0.35s ease both;
        }

        .card-estudiantes::before {
            background: radial-gradient(ellipse 80% 60% at 50% 0%, var(--teal-dim), transparent);
        }

        .card-estudiantes:hover {
            border-color: var(--teal-border);
            box-shadow: 0 20px 60px rgba(20,184,166,0.15);
        }

        .card-estudiantes::before { opacity: 1; }

        /* ── Card icon ── */
        .card-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-icon svg {
            width: 26px;
            height: 26px;
        }

        .icon-gold {
            background: var(--gold-dim);
            border: 1px solid var(--gold-border);
            color: var(--gold);
        }

        .icon-teal {
            background: var(--teal-dim);
            border: 1px solid var(--teal-border);
            color: var(--teal);
        }

        /* ── Card body ── */
        .card-body { display: flex; flex-direction: column; gap: 0.4rem; }

        .card-label {
            font-family: 'Syne', sans-serif;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .label-gold  { color: var(--gold); }
        .label-teal  { color: var(--teal); }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            line-height: 1.1;
        }

        .card-desc {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 300;
            line-height: 1.55;
            margin-top: 0.2rem;
        }

        /* ── Card features list ── */
        .card-features {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .feature-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-gold { background: var(--gold); }
        .dot-teal { background: var(--teal); }

        /* ── Card CTA ── */
        .card-cta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
        }

        .cta-text {
            font-family: 'Syne', sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .cta-gold { color: var(--gold); }
        .cta-teal { color: var(--teal); }

        .cta-arrow {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }

        .arrow-gold {
            background: var(--gold-dim);
            border: 1px solid var(--gold-border);
            color: var(--gold);
        }

        .arrow-teal {
            background: var(--teal-dim);
            border: 1px solid var(--teal-border);
            color: var(--teal);
        }

        .cta-arrow svg { width: 16px; height: 16px; }

        .report-card:hover .cta-arrow {
            transform: translateX(4px);
        }

        /* ── Corner decoration ── */
        .card-corner {
            position: absolute;
            top: -30px;
            right: -30px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            opacity: 0.06;
        }

        .corner-gold { background: var(--gold); }
        .corner-teal { background: var(--teal); }

        /* ── Footer note ── */
        .footer-note {
            font-size: 0.78rem;
            color: var(--text-muted);
            text-align: center;
            animation: fadeUp 0.6s 0.5s ease both;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-note::before,
        .footer-note::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
            max-width: 80px;
        }

        /* ── Animations ── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>
<div class="page-wrapper">

    {{-- ── BRAND ── --}}
    <div class="brand">
        <div class="brand-logo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>
            </svg>
        </div>
        <span class="brand-eyebrow">Sistema Académico</span>
    </div>

    {{-- ── HEADING ── --}}
    <div class="heading-block">
        <h1 class="main-title">Sistema de <span>Reportes de las Encuestas</span></h1>
        <div class="divider"></div>
        <p class="main-subtitle">Selecciona el módulo del que deseas generar o descargar reportes en PDF.</p>
    </div>

    {{-- ── CARDS ── --}}
    <div class="cards-grid">

        {{-- Docentes --}}
        <a href="{{ url('/reportes/docentes') }}" class="report-card card-docentes">
            <div class="card-corner corner-gold"></div>

            <div class="card-icon icon-gold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                </svg>
            </div>

            <div class="card-body">
                <span class="card-label label-gold">Módulo 01</span>
                <h2 class="card-title">Docentes</h2>
                <p class="card-desc">Resultados de las encuestas por escuela académica, resumen general y distribución gráfica del personal docente.</p>
            </div>

            <div class="card-features">
                <div class="feature-item">
                    <span class="feature-dot dot-gold"></span>
                    Reporte por escuela en PDF
                </div>
                <div class="feature-item">
                    <span class="feature-dot dot-gold"></span>
                    PDF con gráfico por escuela
                </div>
                <div class="feature-item">
                    <span class="feature-dot dot-gold"></span>
                    Reporte general con gráfico
                </div>
            </div>

            <div class="card-cta">
                <span class="cta-text cta-gold">Ver reportes</span>
                <div class="cta-arrow arrow-gold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Estudiantes --}}
        <a href="{{ url('/reportes/estudiantes') }}" class="report-card card-estudiantes">
            <div class="card-corner corner-teal"></div>

            <div class="card-icon icon-teal">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </div>

            <div class="card-body">
                <span class="card-label label-teal">Módulo 02</span>
                <h2 class="card-title">Estudiantes</h2>
                <p class="card-desc">Resultados de las encuestas por escuela académica, resumen general y distribución gráfica del alumnado registrado.</p>
            </div>

            <div class="card-features">
                <div class="feature-item">
                    <span class="feature-dot dot-teal"></span>
                    Reporte por escuela en PDF
                </div>
                <div class="feature-item">
                    <span class="feature-dot dot-teal"></span>
                    PDF con gráfico por escuela
                </div>
                <div class="feature-item">
                    <span class="feature-dot dot-teal"></span>
                    Reporte general con gráfico
                </div>
            </div>

            <div class="card-cta">
                <span class="cta-text cta-teal">Ver reportes</span>
                <div class="cta-arrow arrow-teal">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </div>
            </div>
        </a>

    </div>

    {{-- ── FOOTER NOTE ── --}}
    <p class="footer-note">Los reportes se generan en formato PDF descargable</p>

</div>
</body>
</html>