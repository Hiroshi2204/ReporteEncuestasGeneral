<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte General - {{ $escuela }}</title>

    <style>
        @page {
            margin: 135px 40px 80px 40px;
            /* Más espacio para header y footer */
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 12px;
        }

        /* ======================================================
           HEADER PROFESIONAL FIJO
        =======================================================*/
        header {
            position: fixed;
            top: -115px;
            /* Ubicación exacta sobre todas las páginas */
            left: 0;
            right: 0;
            height: 110px;
            text-align: center;
            padding-top: 10px;
            border-bottom: 3px solid #ffffffff;
            color: #002b55;
        }

        header h1,
        header .linea-compacta {
            margin: 0;
            padding: 0;
            line-height: 1.05;
            /* MUCHÍSIMO más pegado */
        }

        header .linea-compacta {
            margin-top: 1px;
            /* Ajuste fino */
            font-size: 14px;
        }

        /* El título siguiente queda SEPARADO */
        header .separado {
            margin-top: 8px;
            /* Espacio hacia abajo */
            font-size: 14px;
            font-weight: bold;
        }

        /* ======================================================
           FOOTER PROFESIONAL FIJO
        =======================================================*/
        footer {
            position: fixed;
            bottom: -55px;
            /* Más abajo */
            left: 0;
            right: 0;
            height: 40px;
            border-top: 2px solid #002b55;
            padding: 4px 20px 0 20px;
            font-size: 6px;
            /* Reducido */
            color: #002b55;
        }

        footer .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        footer td {
            border: none;
            padding: 0;
            vertical-align: middle;
            white-space: nowrap;
            /* Para que todo quede en una sola línea */
        }

        footer .left {
            text-align: left;
        }

        footer .center {
            text-align: center;
        }

        footer .right {
            text-align: right;
        }

        footer .page-number::before {
            content: counter(page);
        }

        /* ======================================================
           TABLA
        =======================================================*/
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 30px;
            font-size: 11px;
            table-layout: fixed;
        }

        th {
            background: #002b55;
            color: #fff;
            padding: 6px;
            text-align: center;
            font-size: 10.5px;
            word-wrap: break-word;
            line-height: 12px;
        }

        td {
            padding: 6px;
            border: 1px solid #c9d6e4;
            text-align: center;
            word-wrap: break-word;
        }

        tr:nth-child(even) td {
            background: #f1f5fb;
        }

        .col-docente {
            width: 44%;
        }

        .col-curso {
            width: 30%;
            font-size: 9px;
        }

        .col-encuestados {
            width: 9%;
            max-width: 38px;
            word-wrap: break-word;
        }

        .alert-text {
            color: #c40000;
            font-style: italic;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <!-- ======================================================
         HEADER
    =======================================================-->
    <header>
        <h1>UNIVERSIDAD NACIONAL DEL CALLAO</h1>

        <!-- Bloque compacto (FACULTAD y ESCUELA) -->
        <h2 class="linea-compacta">{{ $facultad }}</h2>
        <h2 class="linea-compacta">
            Escuela Profesional de <strong>{{ $escuela }}</strong>
        </h2>

        <!-- Bloque separado -->
        <h2 class="separado">ORDEN DE MÉRITO DE ENCUESTA ESTUDIANTIL 2025-B</h2>
    </header>

    <!-- ======================================================
         FOOTER
    =======================================================-->
    <footer>
        <table class="footer-table">
            <tr>
                <td class="left">VICE RECTORADO ACADÉMICO (VRA)</td>
                <td class="center">Página <span class="page-number"></span></td>
                <td class="right">OFICINA DE TECNOLOGÍAS DE LA INFORMACIÓN (OTI)</td>
            </tr>
        </table>
    </footer>

    <!-- ======================================================
         CONTENIDO
    =======================================================-->

    @if(count($ranking) > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Orden</th>
                <th class="col-docente">Docente</th>
                <th class="col-curso">Curso</th>
                <th style="width: 10%;">Sección</th>
                <th class="col-encuestados">Encuestados</th>
                <th style="width: 10%;">Nota Final</th>
            </tr>
        </thead>

        <tbody>
            @foreach($ranking as $r)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="text-align:left;" class="col-docente">{{ $r['docente'] }}</td>
                <td style="text-align:left;" class="col-curso">{{ $r['curso'] }}</td>
                <td>{{ $r['grupo_horario'] }}</td>
                <td class="col-encuestados">{{ $r['encuestados'] }}</td>
                <td><strong>{{ number_format($r['promedio_final'], 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="alert-text">⚠️ No hay docentes con más de 10 encuestados.</p>
    @endif

</body>

</html>