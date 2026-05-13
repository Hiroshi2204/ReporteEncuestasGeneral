<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Gráfico - {{ $docente }}</title>

    <style>
        @page {
            size: A4;
            margin: 30px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
        }

        /* ── HEADER ── */
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0;
            font-size: 15px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 3px 0;
            font-size: 11px;
            color: #1e3a8a;
        }
        .header .badge {
            display: inline-block;
            margin-top: 6px;
            font-size: 11px;
            font-weight: bold;
            background: #1e3a8a;
            color: white;
            padding: 4px 14px;
            border-radius: 6px;
        }

        /* ── CARD INFO ── */
        .card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 16px;
            background: #f8fafc;
        }
        .info {
            margin-bottom: 5px;
            font-size: 11px;
        }
        .info strong {
            color: #1e3a8a;
        }
        .promedio {
            margin-top: 10px;
            background: #0f172a;
            color: white;
            padding: 8px;
            text-align: center;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* ── GRAFICO ── */
        .titulo-grafico {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .grafico {
            text-align: center;
        }
        .grafico img {
            width: 100%;          /* ocupa el ancho disponible */
            max-width: 100%;
            height: auto;
        }
        .no-chart {
            text-align: center;
            color: #ef4444;
            font-size: 11px;
            padding: 20px;
            border: 1px dashed #fca5a5;
            border-radius: 6px;
        }

        /* ── FOOTER ── */
        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>Universidad Nacional del Callao</h1>
        <h2>{{ $facultad }}</h2>
        <h2>Escuela Profesional de {{ $escuela }}</h2>
        <div class="badge">REPORTE GRÁFICO DE ENCUESTA DE SATISFACCIÓN</div>
    </div>

    {{-- INFO DOCENTE --}}
    <div class="card">
        <div class="info"><strong>Generado por:</strong> {{ $docente }}</div>
        <!-- <div class="info"><strong>Curso:</strong>   {{ $curso }}</div>
        <div class="info"><strong>Turno:</strong>   {{ $turno }}</div> -->
        <div class="promedio">
            PROMEDIO GENERAL: {{ number_format($promedioGeneral, 2) }} / 20
        </div>
    </div>

    {{-- GRÁFICO BARRAS --}}
    <div class="titulo-grafico">Promedio por pregunta</div>

    <div class="grafico">
        @if($graficoBarras)
            <img src="{{ $graficoBarras }}">
        @else
            <div class="no-chart">No se pudo cargar el gráfico.</div>
        @endif
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Universidad Nacional del Callao &mdash; Oficina de Tecnologías de la Información (OTI)
    </div>

</body>
</html>