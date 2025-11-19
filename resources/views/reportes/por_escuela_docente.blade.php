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
    }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        color: #333;
    }

    /* HEADER FIJO — NO TOCADO */
    header {
        position: fixed;
        top: -125px;
        left: 0;
        right: 0;
        height: 200px;
        text-align: center;
        padding-top: 0px;
        color: #002b55;
    }

    header h1,
    header .linea-compacta {
        margin: 0;
        padding: 0;
        line-height: 1.05;
    }

    header .linea-compacta {
        margin-top: 1px;
        font-size: 14px;
    }

    header .separado {
        margin-top: 8px;
        font-size: 14px;
        font-weight: bold;
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

    /* FOOTER FIJO */
    footer {
        position: fixed;
        bottom: -60px;
        left: 0;
        right: 0;
        height: 70px;
        border-top: 2px solid #003366;
        text-align: center;
        padding-top: 10px;
        font-size: 11px;
        color: #003366;
    }

    /* TABLAS */
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

    /* Compactar primera hoja */
    .compact td {
        padding: 3px !important;
        font-size: 10px !important;
    }

    .compact th {
        padding: 4px !important;
        font-size: 9px !important;
    }

    /* Evitar cortes */
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
        <h3>UNIVERSIDAD NACIONAL DEL CALLAO</h3>
        <h2 class="linea-compacta">
            <strong>{{ $facultad }}</strong>
        </h2>
        <h2 class="linea-compacta">
            Escuela Profesional de <strong>{{ $escuela }}</strong>
        </h2>
        <!-- Bloque separado -->
        <h2 class="separado">ENCUESTA ESTUDIANTIL 2025-B</h2>
    </header>

    <!-- FOOTER -->
    <footer>
        <strong>LEYENDA:</strong> 1 = DEFICIENTE | 2 = INSUFICIENTE | 3 = REGULAR | 4 = BUENO | 5 = EXCELENTE
    </footer>

    <main>

        @foreach($cursos as $curso => $turnos)
        @foreach($turnos as $turno => $data)

        <div class="curso-wrapper">

            <h5>Docente: <strong>{{ $docente }}</strong></h5>
            <h6 style="margin: 5px 0 10px 0;">
                Curso: <strong>{{ $curso }}</strong> &nbsp; |
                Sección: <strong>{{ $turno }}</strong>
            </h6>

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