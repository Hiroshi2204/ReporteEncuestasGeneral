<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte General - {{ $escuela }}</title>

    <style>
        @page {
            margin: 150px 40px 100px 40px; /* Espacio para header y footer */
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 12px;
        }

        /* ======================================================
            ENCABEZADO FIJO (APARECE EN TODAS LAS PÁGINAS)
        =======================================================*/
        header {
            position: fixed;
            top: -130px;
            left: 0;
            right: 0;
            height: 110px;

            text-align: center;
            padding-top: 10px;

            border-bottom: 3px solid #002b55;
        }

        header h1 {
            font-size: 22px;
            color: #002b55;
            margin: 0;
            font-weight: bold;
            letter-spacing: 1px;
        }

        header p {
            margin: 3px 0;
            font-size: 13px;
            color: #555;
        }

        /* ======================================================
            PIE DE PÁGINA
        =======================================================*/
        footer {
            position: fixed;
            bottom: -80px;
            left: 0;
            right: 0;
            height: 60px;

            border-top: 2px solid #002b55;
            text-align: center;
            font-size: 11px;
            color: #002b55;
            padding-top: 8px;
        }

        /* ======================================================
            SECCIONES / TITULOS
        =======================================================*/
        h2 {
            font-size: 16px;
            color: #002b55;
            margin-top: 10px;
            margin-bottom: 8px;
            border-left: 6px solid #002b55;
            padding-left: 10px;
        }

        /* ======================================================
            TABLAS
        =======================================================*/
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 30px;
            font-size: 11.5px;
        }

        th {
            background: #002b55;
            color: white;
            padding: 7px;
            text-align: center;
            font-size: 11px;
        }

        td {
            padding: 6px;
            border: 1px solid #c9d6e4;
            text-align: center;
        }

        tr:nth-child(even) td {
            background: #f1f5fb;
        }

        /* RANGO DE ALERTA */
        .alert-text {
            color: #c40000;
            font-style: italic;
            font-size: 13px;
        }

    </style>
</head>

<body>

    <!-- ======================================================
        HEADER (FIJO)
    =======================================================-->
    <header>
        <h1>UNIVERSIDAD NACIONAL DEL CALLAO</h1>
        <p>Orden de Mérito de Evaluación Docente</p>
        <p>Escuela Profesional: <strong>{{ $escuela }}</strong></p>
    </header>

    <!-- ======================================================
        FOOTER (FIJO)
    =======================================================-->
    <footer>
        Reporte generado por la OTI - Sistema de Evaluación Docente
    </footer>

    <!-- ======================================================
        CONTENIDO
    =======================================================-->

    <h2>Ranking de Docentes Evaluados</h2>

    @if(count($ranking) > 0)
    <table>
        <thead>
            <tr>
                <th>Orden</th>
                <th>Docente</th>
                <th>Curso</th>
                <th>Grupo</th>
                <th>Encuestados</th>
                <th>Promedio Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $r)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="text-align:left;">{{ $r['docente'] }}</td>
                <td style="text-align:left;">{{ $r['curso'] }}</td>
                <td>{{ $r['grupo_horario'] }}</td>
                <td>{{ $r['encuestados'] }}</td>
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
