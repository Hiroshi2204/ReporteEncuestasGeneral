<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte - {{ $docente }}</title>

    <style>
        /* =========================
           PÁGINA A4
        ========================== */
        @page {
            size: A4;
            margin: 90px 20px 50px 20px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.3px;
            color: #1e293b;
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }

        /* =========================
           HEADER — fijo en margen superior
        ========================== */
        header {
            position: fixed;
            top: -88px;
            left: 0;
            right: 0;
            height: 86px;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 5px;
            background: #ffffff;
        }

        .titulo {
            text-align: center;
            color: #0f172a;
        }

        .titulo h1 {
            margin: 0 0 2px 0;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .titulo h2 {
            margin: 1px 0;
            font-size: 9.5px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .titulo h3 {
            margin: 4px 0 0 0;
            font-size: 8.5px;
            background-color: #1e3a8a;
            color: #ffffff;
            display: inline-block;
            padding: 3px 14px;
            border-radius: 10px;
            letter-spacing: 0.4px;
        }

        /* =========================
           FOOTER — fijo en margen inferior
        ========================== */
        footer {
            position: fixed;
            bottom: -46px;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 1.5px solid #1e3a8a;
            text-align: center;
            font-size: 7px;
            padding-top: 5px;
            color: #475569;
            background: #ffffff;
        }

        /* =========================
           CONTENIDO PRINCIPAL
        ========================== */
        main {
            margin-top: 4px;
            /* espacio extra al final para que el footer no tape el promedio */
            padding-bottom: 8px;
        }

        /* =========================
           TARJETA DE DOCENTE
        ========================== */
        .contenedor {
            border: 1.5px solid #bfdbfe;
            border-radius: 6px;
            margin-bottom: 10px;
            /* NO overflow:hidden — evitamos que corte el promedio */
        }

        .docente-bar {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 6px 10px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px 4px 0 0;
            letter-spacing: 0.3px;
        }

        .info {
            background-color: #eff6ff;
            padding: 5px 10px;
            border-bottom: 1px solid #bfdbfe;
            font-size: 8px;
        }

        .info strong {
            color: #1e3a8a;
        }

        /* =========================
           TABLA PRINCIPAL
        ========================== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 4px 3px;
            font-size: 7.5px;
            border: 1px solid #93c5fd;
            text-align: center;
        }

        tbody td {
            border: 1px solid #dbeafe;
            padding: 3px 4px;
            font-size: 7.3px;
            vertical-align: middle;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fbff;
        }

        .area {
            background-color: #dbeafe;
            color: #1e3a8a;
            font-weight: bold;
            text-align: center;
            font-size: 7.2px;
        }

        .pregunta {
            text-align: left;
            font-size: 7px;
            line-height: 1.1;
        }

        .nota {
            font-weight: bold;
            color: #0f172a;
            background-color: #f1f5f9;
        }

        /* =========================
           RESUMEN POR ÁREAS
        ========================== */
        .resumen {
            margin-top: 3px;
            width: 100%;
            border-collapse: collapse;
        }

        .resumen td {
            background-color: #dbeafe;
            border: 1px solid #93c5fd;
            padding: 5px 4px;
            text-align: center;
            font-size: 7.2px;
            font-weight: bold;
            color: #1e3a8a;
            line-height: 1.4;
        }

        /* =========================
           PROMEDIO FINAL — sin gradiente para compatibilidad PDF
        ========================== */
        .promedio-final {
            margin-top: 4px;
            background-color: #0f172a;   /* color sólido, funciona en dompdf */
            color: #ffffff;
            text-align: center;
            padding: 9px 8px;
            font-size: 11.5px;
            font-weight: bold;
            border-radius: 0 0 4px 4px;
            letter-spacing: 0.4px;
            /* borde extra para garantizar visibilidad */
            border: 2px solid #0f172a;
        }

        .promedio-final span {
            color: #93c5fd;
            font-size: 13px;
        }

        /* =========================
           EVITAR CORTES BRUSCOS
        ========================== */
        .contenedor {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        tr, td, th {
            page-break-inside: avoid;
        }

        /* Numeración de páginas */
        .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
        }
    </style>

</head>

<body>

    <!-- HEADER FIJO -->
    <header>
        <div class="titulo">
            <h1>UNIVERSIDAD NACIONAL DEL CALLAO</h1>
            <h2>{{ $facultad }}</h2>
            <h2>Escuela Profesional de {{ $escuela }}</h2>
            <h3>ENCUESTA ESTUDIANTIL 2025-B</h3>
        </div>
    </header>

    <!-- FOOTER FIJO -->
    <footer>
        <strong>LEYENDA:</strong>
        &nbsp; 1 = MUY EN DESACUERDO &nbsp;|&nbsp;
        2 = EN DESACUERDO &nbsp;|&nbsp;
        3 = DE ACUERDO &nbsp;|&nbsp;
        4 = MUY DE ACUERDO
        &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="page-number"></span>
    </footer>

    <!-- CONTENIDO -->
    <main>

        @foreach($cursos as $curso => $turnos)
        @foreach($turnos as $turno => $data)

        <div class="contenedor">

            <!-- BARRA DE DOCENTE -->
            <div class="docente-bar">
                &#128100;: {{ $docente }}
            </div>

            <!-- INFORMACIÓN DEL CURSO -->
            <div class="info">
                <strong>|</strong> {{ $curso }}
                &nbsp;&nbsp;&nbsp;
                <strong>N° Encuestados:</strong> {{ $data['totalEncuestados'] }}
            </div>

            <!-- TABLA DE PREGUNTAS -->
            <table>
                <thead>
                    <tr>
                        <th width="15%">Área</th>
                        <th width="51%">Pregunta</th>
                        <th width="5%">1</th>
                        <th width="5%">2</th>
                        <th width="5%">3</th>
                        <th width="5%">4</th>
                        <th width="9%">Nota / 20</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($data['areas'] as $area => $infoArea)
                    @foreach($infoArea['preguntas'] as $i => $p)
                    <tr>
                        @if($i == 0)
                        <td class="area" rowspan="{{ count($infoArea['preguntas']) }}">
                            {{ $area }}
                        </td>
                        @endif

                        <td class="pregunta">{{ $p->pregunta }}</td>
                        <td>{{ $p->n1 }}</td>
                        <td>{{ $p->n2 }}</td>
                        <td>{{ $p->n3 }}</td>
                        <td>{{ $p->n4 }}</td>
                        <td class="nota">{{ number_format($p->nota_item_20, 2) }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>

            <!-- RESUMEN POR ÁREAS -->
            <table class="resumen">
                <tr>
                    @foreach($data['areas'] as $area => $infoArea)
                    <td>
                        {{ $area }}<br><br>
                        <span style="font-size:9px; color:#0f172a;">
                            {{ number_format($infoArea['promedio'], 2) }}
                        </span>
                    </td>
                    @endforeach
                </tr>
            </table>

            <!-- PROMEDIO FINAL — color sólido para dompdf -->
            <div class="promedio-final">
                PROMEDIO GENERAL DE LA ENCUESTA:
                <span>{{ number_format($data['promedioFinal'], 2) }}</span>
            </div>

        </div>

        @endforeach
        @endforeach

    </main>

</body>

</html>