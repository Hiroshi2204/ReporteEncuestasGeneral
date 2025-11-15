<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte - {{ $docente }}</title>

    <!-- Bootstrap reducido compatible con PDF -->
    <style>
        /* GRID MINIMAL */
        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .col {
            flex: 1;
        }

        @page {
            size: A4;
            margin: 140px 35px 110px 35px;
            /* espacio optimizado */
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
        }

        /* ==========================================
           ENCABEZADO PROFESIONAL
        ========================================== */
        header {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 110px;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;

            border-bottom: 2px solid #003366;
        }

        .logo {
            width: 80px;
        }

        .titulo-uni {
            font-size: 22px;
            font-weight: bold;
            color: #003366;
            text-align: center;
            line-height: 1.1;
        }

        /* ==========================================
           FOOTER PROFESIONAL PEGADO ABAJO
        ========================================== */
        footer {
            position: fixed;
            bottom: -105px;
            left: 0;
            right: 0;
            height: 90px;

            border-top: 2px solid #003366;
            text-align: center;
            padding-top: 12px;
            font-size: 11px;
            color: #003366;
        }

        /* ==========================================
            TABLAS
        ========================================== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            background: #003366;
            color: #fff;
            padding: 5px;
            font-size: 10px;
        }

        td {
            border: 1px solid #cdd3dd;
            padding: 4px;
        }

        tr:nth-child(even) {
            background: #eef4ff;
        }

        .area-cell {
            background: #d9e4f7;
            font-weight: bold;
            text-align: left;
        }

        .resumen-horizontal td {
            background: #f1f5ff;
            padding: 6px;
            font-weight: bold;
        }

        .resumen-final {
            background: #c7d8f7;
            padding: 8px;
            text-align: center;
            font-size: 13px;
            margin-top: 15px;
            font-weight: bold;
        }

        /* ==========================================
           AJUSTE AUTOMÁTICO PRIMERA HOJA
        ========================================== */
        .compact td {
            padding: 3px !important;
            font-size: 10px !important;
        }

        .compact th {
            padding: 4px !important;
            font-size: 9px !important;
        }

        /* ==========================================
           EVITAR CORTES ENTRE CURSOS
        ========================================== */
        .curso-wrapper {
            page-break-inside: avoid;
            margin-bottom: 28px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <!-- ENCABEZADO -->
    <header>
        <img class="logo"
            src="https://portalapi.tuni.pe/ApiDatos/api/imagen?img=027\LOGO\logos%20licenciadas_86.png">

        <div class="titulo-uni">
            UNIVERSIDAD NACIONAL DEL CALLAO <br>
        </div>
        
    </header>

    <!-- FOOTER -->
    <footer>
        LEYENDA: 1 = DEFICIENTE | 2 = INSUFICIENTE | 3 = REGULAR | 4 = BUENO | 5 = EXCELENTE
    </footer>

    <main>

        <h4 style="text-align: center;">Encuesta Estudiantil 2025-B PREGRADO</h4>

        <h4 style="margin-bottom:10px;">
            Docente: <strong>{{ $docente }}</strong> &nbsp; |  &nbsp;
            Escuela: <strong>{{ $escuela }}</strong>
        </h4>

        @foreach($cursos as $curso => $turnos)
        @foreach($turnos as $turno => $data)

        <div class="curso-wrapper">

            <h5 style="margin: 5px 0 10px 0;">
                Curso: <strong>{{ $curso }}</strong> &nbsp; |
                Sección: <strong>{{ $turno }}</strong>
            </h5>

            {{-- Ajuste solo para la primera hoja --}}
            @php
            $compact = ($loop->first && $loop->parent->first) ? 'compact' : '';
            @endphp

            <table class="{{ $compact }}">
                <tr>
                    <td colspan="8" style="background:#eef2fb; font-weight:bold; text-align:left;">
                        TOTAL DE ENCUESTADOS: {{ $data['totalEncuestados'] }}
                    </td>
                </tr>

                <thead>
                    <tr>
                        <th>Área</th>
                        <th>Pregunta</th>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>Nota</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($data['areas'] as $area => $infoArea)
                    @foreach($infoArea['preguntas'] as $i => $p)
                    <tr>
                        @if($i == 0)
                        <td class="area-cell" rowspan="{{ count($infoArea['preguntas']) }}">
                            {{ $area }}
                        </td>
                        @endif

                        <td style="text-align:left;">{{ $p->pregunta }}</td>
                        <td>{{ $p->n1 }}</td>
                        <td>{{ $p->n2 }}</td>
                        <td>{{ $p->n3 }}</td>
                        <td>{{ $p->n4 }}</td>
                        <td>{{ $p->n5 }}</td>
                        <td><strong>{{ number_format($p->nota_item_20, 2) }}</strong></td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>

            <table class="resumen-horizontal">
                <tr>
                    @foreach($data['areas'] as $area => $infoArea)
                    <td>{{ $area }}<br>{{ number_format($infoArea['promedio'], 2) }}</td>
                    @endforeach
                </tr>
            </table>

            <div class="resumen-final">
                PROMEDIO GENERAL DEL CURSO: {{ number_format($data['promedioFinal'], 2) }}
            </div>

        </div>

        @if(! ($loop->last && $loop->parent->last))
        <div class="page-break"></div>
        @endif

        @endforeach
        @endforeach

    </main>

</body>

</html>