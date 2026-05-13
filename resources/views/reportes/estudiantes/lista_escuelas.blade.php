<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Escuelas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --navy: #0b1120;
            --navy-mid: #111827;
            --navy-card: #151f32;
            --navy-row: #1a2742;
            --navy-hover: #1e2f50;
            --gold: #f5a623;
            --gold-light: #ffd080;
            --gold-dim: rgba(245, 166, 35, 0.15);
            --gold-border: rgba(245, 166, 35, 0.3);
            --text-primary: #eef2ff;
            --text-secondary: #94a3b8;
            --text-muted: #4b6080;
            --border: rgba(255,255,255,0.06);
            --accent-blue: #3b82f6;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--navy);
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Background texture ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(59,130,246,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 90%, rgba(245,166,35,0.06) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .page-wrapper {
            position: relative;
            z-index: 1;
            padding: 2.5rem 1.5rem 4rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border);
            animation: fadeDown 0.6s ease both;
        }

        .header-left {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .eyebrow {
            font-family: 'Syne', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gold);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .eyebrow::before {
            content: '';
            display: inline-block;
            width: 24px;
            height: 2px;
            background: var(--gold);
        }

        .page-title {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 300;
        }

        /* ── Global PDF button ── */
        .btn-gold {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: var(--gold);
            color: #1a0f00;
            font-family: 'Syne', sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border-radius: 8px;
            border: none;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .btn-gold:hover {
            background: var(--gold-light);
            color: #1a0f00;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(245,166,35,0.35);
        }

        .btn-gold svg { width: 16px; height: 16px; }

        /* ── Stats bar ── */
        .stats-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            animation: fadeUp 0.6s 0.1s ease both;
        }

        .stat-chip {
            background: var(--navy-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.65rem 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .stat-chip .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--gold);
            line-height: 1;
        }

        .stat-chip .stat-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* ── Search bar ── */
        .search-wrap {
            position: relative;
            margin-bottom: 1.25rem;
            animation: fadeUp 0.6s 0.15s ease both;
        }

        .search-wrap svg {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 16px; height: 16px;
        }

        #searchInput {
            width: 100%;
            background: var(--navy-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        #searchInput::placeholder { color: var(--text-muted); }

        #searchInput:focus {
            border-color: var(--gold-border);
            box-shadow: 0 0 0 3px rgba(245,166,35,0.1);
        }

        /* ── Table card ── */
        .table-card {
            background: var(--navy-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            animation: fadeUp 0.6s 0.2s ease both;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table thead tr {
            background: rgba(245,166,35,0.08);
            border-bottom: 1px solid var(--gold-border);
        }

        .custom-table thead th {
            font-family: 'Syne', sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--gold);
            padding: 1rem 1.25rem;
            white-space: nowrap;
        }

        .custom-table thead th:first-child { width: 60px; text-align: center; }

        .custom-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.18s ease;
        }

        .custom-table tbody tr:last-child { border-bottom: none; }

        .custom-table tbody tr:hover { background: var(--navy-hover); }

        .custom-table td {
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            color: var(--text-primary);
            vertical-align: middle;
        }

        .custom-table td:first-child {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* ── Código badge ── */
        .code-badge {
            display: inline-block;
            background: var(--gold-dim);
            border: 1px solid var(--gold-border);
            color: var(--gold-light);
            font-family: 'Syne', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
        }

        /* ── School name ── */
        .school-name {
            font-weight: 500;
            color: var(--text-primary);
        }

        /* ── Action buttons ── */
        .actions-cell {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.85rem;
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.78rem;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid;
            transition: all 0.18s ease;
            white-space: nowrap;
        }

        .btn-action svg { width: 13px; height: 13px; flex-shrink: 0; }

        .btn-pdf {
            background: rgba(59,130,246,0.1);
            border-color: rgba(59,130,246,0.3);
            color: #93c5fd;
        }

        .btn-pdf:hover {
            background: rgba(59,130,246,0.2);
            border-color: rgba(59,130,246,0.6);
            color: #bfdbfe;
            transform: translateY(-1px);
        }

        .btn-chart {
            background: var(--gold-dim);
            border-color: var(--gold-border);
            color: var(--gold-light);
        }

        .btn-chart:hover {
            background: rgba(245,166,35,0.25);
            border-color: rgba(245,166,35,0.6);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* ── Animations ── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .custom-table tbody tr {
            animation: rowIn 0.4s ease both;
        }

        @keyframes rowIn {
            from { opacity: 0; transform: translateX(-10px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .custom-table tbody tr:nth-child(1)  { animation-delay: 0.22s; }
        .custom-table tbody tr:nth-child(2)  { animation-delay: 0.27s; }
        .custom-table tbody tr:nth-child(3)  { animation-delay: 0.32s; }
        .custom-table tbody tr:nth-child(4)  { animation-delay: 0.37s; }
        .custom-table tbody tr:nth-child(5)  { animation-delay: 0.42s; }
        .custom-table tbody tr:nth-child(6)  { animation-delay: 0.47s; }
        .custom-table tbody tr:nth-child(7)  { animation-delay: 0.52s; }
        .custom-table tbody tr:nth-child(8)  { animation-delay: 0.55s; }
        .custom-table tbody tr:nth-child(n+9) { animation-delay: 0.58s; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .page-title { font-size: 1.5rem; }
            .custom-table thead th:nth-child(2),
            .custom-table td:nth-child(2) { display: none; }
            .actions-cell { flex-direction: column; }
        }
    </style>
</head>

<body>
<div class="page-wrapper">

    {{-- ── HEADER ── --}}
    <header class="page-header">
        <div class="header-left">
            <span class="eyebrow">Sistema de Reportes de Encuestas</span>
            <h1 class="page-title">Listado de Escuelas</h1>
            <p class="page-subtitle">Gestión de reportes por unidad académica</p>
        </div>

        <a href="{{ url('/reportes/estudiantes/general/grafico') }}" class="btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Reporte General (Gráfico)
        </a>
    </header>

    {{-- ── STATS ── --}}
    <!-- <div class="stats-bar">
        <div class="stat-chip">
            <span class="stat-value">{{ count($escuelas) }}</span>
            <span class="stat-label">Escuelas</span>
        </div>
    </div> -->

    {{-- ── SEARCH ── --}}
    <div class="search-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
        </svg>
        <input type="text" id="searchInput" placeholder="Buscar por código o nombre de escuela…">
    </div>

    {{-- ── TABLE ── --}}
    <div class="table-card">
        <table class="custom-table" id="escuelasTable">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Código</th>
                    <th>Nombre de la Escuela</th>
                    <th>Reporte PDF</th>
                    <th>Reporte Gráfico</th>
                </tr>
            </thead>
            <tbody>
                @forelse($escuelas as $e)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><span class="code-badge">{{ $e->COD_ESCUELA }}</span></td>
                    <td><span class="school-name">{{ $e->NOM_ESCUELA }}</span></td>
                    <td>
                        <div class="actions-cell">
                            <a href="{{ url('reportes/estudiantes/escuela/'.$e->COD_ESCUELA) }}" class="btn-action btn-pdf">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                </svg>
                                Descargar PDF
                            </a>
                        </div>
                    </td>
                    <td>
                        <div class="actions-cell">
                            <a href="{{ url('/reportes/estudiantes/escuela/grafico/'.$e->COD_ESCUELA) }}" class="btn-action btn-chart">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                                </svg>
                                PDF Gráfico
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">No se encontraron escuelas registradas.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ── SEARCH SCRIPT ── --}}
<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#escuelasTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
    });
</script>
</body>
</html>