<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte General de Docentes</title>

    <style>
        @page {
            size: A4;
            margin: 30px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
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
            font-size: 14px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 3px 0;
            font-size: 10px;
            color: #1e3a8a;
        }
        .header .badge {
            display: inline-block;
            margin-top: 6px;
            font-size: 10px;
            font-weight: bold;
            background: #1e3a8a;
            color: white;
            padding: 4px 14px;
            border-radius: 6px;
        }

        /* ── RESUMEN GENERAL ── */
        .resumen {
            margin-bottom: 14px;
        }
        .resumen table {
            width: 100%;
            border-collapse: collapse;
        }
        .resumen td {
            padding: 7px 12px;
            font-size: 10px;
        }
        .resumen .cel-label {
            background: #1e3a8a;
            color: white;
            font-weight: bold;
            width: 50%;
            border-radius: 4px 0 0 4px;
        }
        .resumen .cel-valor {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 12px;
            border-radius: 0 4px 4px 0;
        }

        /* ── TITULO SECCIÓN ── */
        .titulo-seccion {
            font-size: 10px;
            font-weight: bold;
            color: white;
            background: #0f172a;
            padding: 5px 10px;
            border-radius: 5px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* ── TABLA DE PREGUNTAS ── */
        table.tabla-preguntas {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9px;
        }
        table.tabla-preguntas thead tr {
            background: #1e3a8a;
            color: white;
        }
        table.tabla-preguntas thead th {
            padding: 5px 6px;
            text-align: left;
            font-size: 9px;
        }
        table.tabla-preguntas thead th.center {
            text-align: center;
        }
        table.tabla-preguntas tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        table.tabla-preguntas tbody tr:nth-child(odd) {
            background: #ffffff;
        }
        table.tabla-preguntas tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        table.tabla-preguntas .td-area {
            background: #e0e7ff;
            color: #1e3a8a;
            font-weight: bold;
            font-size: 8.5px;
            text-align: center;
            vertical-align: middle;
            border-right: 2px solid #1e3a8a;
        }
        table.tabla-preguntas .td-pregunta {
            color: #0f172a;
        }
        table.tabla-preguntas .td-num {
            text-align: center;
            color: #475569;
        }
        table.tabla-preguntas .td-nota {
            text-align: center;
            font-weight: bold;
            color: #1e3a8a;
        }
        table.tabla-preguntas .tr-promedio-area td {
            background: #dbeafe;
            font-weight: bold;
            font-size: 9px;
            color: #1e3a8a;
            padding: 4px 6px;
            border-top: 1px solid #93c5fd;
        }

        /* ── SALTO DE PÁGINA ── */
        .page-break {
            page-break-after: always;
        }

        /* ── GRÁFICO ── */
        .titulo-grafico {
            font-size: 10px;
            font-weight: bold;
            color: white;
            background: #0f172a;
            padding: 5px 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .grafico {
            text-align: center;
        }
        .grafico img {
            width: 100%;
            max-width: 100%;
            height: auto;
        }
        .no-chart {
            text-align: center;
            color: #ef4444;
            font-size: 10px;
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

    {{-- ══════════════════════════════════════ --}}
    {{-- PÁGINA 1 — HEADER + RESUMEN + TABLA   --}}
    {{-- ══════════════════════════════════════ --}}

    {{-- HEADER --}}
    <div class="header">
        <h1>Universidad Nacional del Callao</h1>
        <h2>Encuesta de Satisfacción Docente</h2>
        <div class="badge">REPORTE GENERAL — DOCENTES</div>
    </div>

    {{-- RESUMEN GENERAL --}}
    <div class="resumen">
        <table>
            <tr>
                <td class="cel-label">PROMEDIO GENERAL</td>
                <td class="cel-valor">{{ number_format($promedioGeneral, 2) }} / 20</td>
            </tr>
            <tr>
                <td class="cel-label">TOTAL DE ENCUESTADOS</td>
                <td class="cel-valor">{{ number_format($totalEncuestados) }}</td>
            </tr>
        </table>
    </div>

    {{-- TABLA DE PREGUNTAS POR ÁREA --}}
    <div class="titulo-seccion">Detalle por Pregunta</div>

    <table class="tabla-preguntas">
        <thead>
            <tr>
                <th width="14%" class="center">Área</th>
                <th width="50%">Pregunta</th>
                <th width="5%" class="center">1</th>
                <th width="5%" class="center">2</th>
                <th width="5%" class="center">3</th>
                <th width="5%" class="center">4</th>
                <th width="11%" class="center">Nota / 20</th>
            </tr>
        </thead>

        <tbody>
            @foreach($areas as $nomArea => $infoArea)

                @foreach($infoArea['preguntas'] as $i => $p)
                <tr>
                    @if($i == 0)
                    <td class="td-area"
                        rowspan="{{ count($infoArea['preguntas']) + 1 }}">
                        {{ $nomArea }}
                    </td>
                    @endif

                    <td class="td-pregunta">{{ $p->pregunta }}</td>
                    <td class="td-num">{{ $p->n1 }}</td>
                    <td class="td-num">{{ $p->n2 }}</td>
                    <td class="td-num">{{ $p->n3 }}</td>
                    <td class="td-num">{{ $p->n4 }}</td>
                    <td class="td-nota">{{ number_format($p->nota_pregunta, 2) }}</td>
                </tr>
                @endforeach

                {{-- Fila de promedio del área --}}
                <tr class="tr-promedio-area">
                    <td colspan="5" style="text-align:right;">
                        Promedio del área:
                    </td>
                    <td class="td-nota">
                        {{ number_format($infoArea['promedioArea'], 2) }}
                    </td>
                </tr>

            @endforeach
        </tbody>
    </table>

    {{-- FOOTER PÁGINA 1 --}}
    <div class="footer">
        Universidad Nacional del Callao &mdash; Oficina de Tecnologías de la Información (OTI)
    </div>

    {{-- ══════════════════════════════════════ --}}
    {{-- SALTO DE PÁGINA                        --}}
    {{-- ══════════════════════════════════════ --}}
    <div class="page-break"></div>

    {{-- ══════════════════════════════════════ --}}
    {{-- PÁGINA 2 — GRÁFICO DE BARRAS           --}}
    {{-- ══════════════════════════════════════ --}}

    {{-- HEADER repetido --}}
    <div class="header">
        <h1>Universidad Nacional del Callao</h1>
        <h2>Encuesta de Satisfacción Docente</h2>
        <div class="badge">REPORTE GENERAL — DOCENTES</div>
    </div>

    {{-- GRÁFICO --}}
    <div class="titulo-grafico">Gráfico de Barras — Promedio por Pregunta</div>

    <div class="grafico">
        @if($graficoBarras)
            <img src="{{ $graficoBarras }}">
        @else
            <div class="no-chart">No se pudo cargar el gráfico.</div>
        @endif
    </div>

    {{-- FOOTER PÁGINA 2 --}}
    <div class="footer">
        Universidad Nacional del Callao &mdash; Oficina de Tecnologías de la Información (OTI)
    </div>

</body>
</html>