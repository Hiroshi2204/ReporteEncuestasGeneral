<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte - {{ $docente }}</title>

    <style>
        /* =========================
           PÁGINA
        ========================== */
        @page {
            size: A4;
            margin: 82px 18px 45px 18px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7.3px;
            color: #1e293b;
            line-height: 1.05;
            margin: 0;
            padding: 0;
        }

        /* =========================
           HEADER
        ========================== */
        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 75px;
            border-bottom: 2px solid #0f172a;
            background: #ffffff;
            padding-bottom: 4px;
        }

        .titulo {
            text-align: center;
            color: #0f172a;
        }

        .titulo h1 {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .titulo h2 {
            margin: 1px 0;
            font-size: 8px;
            color: #1e3a8a;
        }

        .titulo h3 {
            margin-top: 3px;
            font-size: 7px;
            background: #1e3a8a;
            color: white;
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
        }

        /* =========================
           FOOTER
        ========================== */
        footer {
            position: fixed;
            bottom: -38px;
            left: 0;
            right: 0;
            height: 32px;
            border-top: 1px solid #1e3a8a;
            text-align: center;
            font-size: 6.2px;
            padding-top: 4px;
            color: #475569;
            background: white;
        }

        .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
        }

        /* =========================
           CONTENIDO
        ========================== */
        main {
            margin-top: 2px;
        }

        .contenedor {
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        /* =========================
           DOCENTE
        ========================== */
        .docente-bar {
            background: #1e3a8a;
            color: white;
            padding: 4px 8px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 4px 4px 0 0;
        }

        .info {
            background: #eff6ff;
            padding: 4px 8px;
            border-bottom: 1px solid #bfdbfe;
            font-size: 7px;
        }

        .info strong {
            color: #1e3a8a;
        }

        /* =========================
           TABLA
        ========================== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #1e3a8a;
            color: white;
            padding: 3px 2px;
            font-size: 6.5px;
            border: 1px solid #93c5fd;
            text-align: center;
        }

        tbody td {
            border: 1px solid #dbeafe;
            padding: 2px 3px;
            font-size: 6.5px;
            text-align: center;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background: #f8fbff;
        }

        .area {
            background: #dbeafe;
            color: #1e3a8a;
            font-weight: bold;
            text-align: center;
            font-size: 6.5px;
        }

        .pregunta {
            text-align: left;
            font-size: 6.3px;
            line-height: 1;
        }

        .nota {
            font-weight: bold;
            color: #0f172a;
            background: #f1f5f9;
        }

        /* =========================
           RESUMEN
        ========================== */
        .resumen {
            margin-top: 2px;
        }

        .resumen td {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            padding: 4px 3px;
            text-align: center;
            font-size: 6.5px;
            font-weight: bold;
            color: #1e3a8a;
        }

        /* =========================
           GRÁFICO
        ========================== */
        .grafico {
            margin-top: 4px;
            text-align: center;
        }

        .grafico img {
            width: 100%;
            height: 290px;
            object-fit: contain;
        }

        /* =========================
           PROMEDIO FINAL
        ========================== */
        .promedio-final {
            margin-top: 4px;
            background: #0f172a;
            color: white;
            text-align: center;
            padding: 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 0 0 4px 4px;
        }

        .promedio-final span {
            color: #93c5fd;
            font-size: 11px;
        }

        /* =========================
           EVITAR CORTES
        ========================== */
        thead {
            display: table-header-group;
        }

        tr,
        td,
        th {
            page-break-inside: avoid;
        }
    </style>

</head>

<body>

    <!-- HEADER -->
    <header>
        <div class="titulo">
            <h1>UNIVERSIDAD NACIONAL DEL CALLAO</h1>
            <h2>{{ $facultad }}</h2>
            <h2>Escuela Profesional de {{ $escuela }}</h2>
            <h3>ENCUESTA DE SATISFACTION SOBRE LOS SISTEMAS INFORMÁTICOS DE LA UNAC 2025-B</h3>
        </div>
    </header>

    <!-- FOOTER -->
    <footer>

        <strong>LEYENDA:</strong>

        1 = MUY EN DESACUERDO |
        2 = EN DESACUERDO |
        3 = DE ACUERDO |
        4 = MUY DE ACUERDO

        &nbsp;&nbsp;&nbsp;

        <span class="page-number"></span>

    </footer>

    <!-- CONTENIDO -->
    <main>

        @foreach($cursos as $curso => $turnos)
        @foreach($turnos as $turno => $data)

        <div class="contenedor">

            <!-- DOCENTE -->
            <div class="docente-bar">
                RESPONSABLE: {{ $docente }}
            </div>

            <!-- INFO -->
            <div class="info">

                <strong>TEMA:</strong>
                {{ $curso }}

                &nbsp;&nbsp;&nbsp;&nbsp;

                <!-- <strong>TURNO:</strong>
                {{ $turno }} -->

                &nbsp;&nbsp;&nbsp;&nbsp;

                <strong>ENCUESTADOS:</strong>
                {{ $data['totalEncuestados'] }}

            </div>

            <!-- TABLA -->
            <table>

                <thead>
                    <tr>
                        <th width="15%">Área</th>
                        <th width="51%">Pregunta</th>
                        <th width="5%">1</th>
                        <th width="5%">2</th>
                        <th width="5%">3</th>
                        <th width="5%">4</th>
                        <th width="9%">Nota</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($data['areas'] as $area => $infoArea)
                    @foreach($infoArea['preguntas'] as $i => $p)

                    <tr>

                        @if($i == 0)
                        <td class="area"
                            rowspan="{{ count($infoArea['preguntas']) }}">

                            {{ $area }}

                        </td>
                        @endif

                        <td class="pregunta">
                            {{ $p->pregunta }}
                        </td>

                        <td>{{ $p->n1 }}</td>
                        <td>{{ $p->n2 }}</td>
                        <td>{{ $p->n3 }}</td>
                        <td>{{ $p->n4 }}</td>

                        <td class="nota">
                            {{ number_format($p->nota_item_20, 2) }}
                        </td>

                    </tr>

                    @endforeach
                    @endforeach

                </tbody>

            </table>

            <!-- RESUMEN -->
            <table class="resumen">

                <tr>

                    @foreach($data['areas'] as $area => $infoArea)

                    <td>

                        {{ $area }}

                        <br><br>

                        <span style="font-size:8px; color:#0f172a;">

                            {{ number_format($infoArea['promedio'], 2) }}

                        </span>

                    </td>

                    @endforeach

                </tr>

            </table>

            <!-- GRÁFICO -->
            @if(isset($data['graficoBarras']))
            <div class="grafico">

                <img src="{{ $data['graficoBarras'] }}">

            </div>
            @endif

            <!-- PROMEDIO FINAL -->
            <div class="promedio-final">

                PROMEDIO GENERAL:

                <span>

                    {{ number_format($data['promedioFinal'], 2) }}

                </span>

            </div>

        </div>

        @endforeach
        @endforeach

    </main>

</body>

</html>