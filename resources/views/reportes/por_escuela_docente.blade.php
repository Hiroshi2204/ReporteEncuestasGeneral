<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte - {{ $docente }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            color: #333;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px solid #004080;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header img {
            height: 70px;
            margin-right: 15px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
            color: #004080;
        }

        .header h2 {
            font-size: 14px;
            margin: 2px 0 0 0;
            color: #555;
        }

        h3,
        h4 {
            color: #004080;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        th {
            background-color: #004080;
            color: white;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }

        td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f4f8ff;
        }

        .page-break {
            page-break-after: always;
        }

        /* ✅ Tabla resumen */
        .resumen-table {
            width: 50%;
            margin-top: 20px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .resumen-table th {
            background-color: #004080;
            color: #fff;
            padding: 6px;
        }

        .resumen-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }

        .resumen-final {
            font-weight: bold;
            background-color: #c9daf8;
        }

        .area-cell {
            text-align: left;
            vertical-align: middle;
            font-weight: bold;
        }

        /* Total encuestados arriba */
        .info-row td {
            font-weight: bold;
            background-color: #e6eefc;
        }
    </style>
</head>

<body>

    <!-- 🔵 Encabezado con logo UNAC -->
    <div class="header">
        <img src="{{ public_path('img/logo_original.png') }}" alt="UNAC">
        <div>
            <h1>UNIVERSIDAD NACIONAL DEL CALLAO</h1>
            <h2>Reporte de Encuestas PREGRADO - {{ $escuela }}</h2>
        </div>
    </div>

    <!-- 🔵 Información principal -->
    <h3>Docente: {{ $docente }}</h3>

    <!-- 🔵 Tablas por curso y turno -->
    @foreach($cursos as $curso => $turnos)
    @foreach($turnos as $turno => $preguntas)
    <h4>{{ $curso }} - Turno: {{ $turno }}</h4>

    @php
    // Agrupar preguntas por área
    $preguntasPorArea = $preguntas->groupBy('nom_area');
    $totalEncuestados = $preguntas->first()->total_respuestas;
    $promediosAreas = [];
    @endphp

    <!-- ✅ Tabla principal -->
    <table>
        <!-- Total encuestados arriba -->
        <tr class="info-row">
            <td colspan="8" style="text-align: left;">TOTAL DE ENCUESTADOS: {{ $totalEncuestados }}</td>
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
                <th>Nota /20</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preguntasPorArea as $area => $items)
            @php
            $rowspan = count($items);
            $sumaNotas = 0;
            @endphp
            @foreach($items as $index => $p)
            <tr>
                @if($index == 0)
                <td class="area-cell" rowspan="{{ $rowspan }}">{{ $area }}</td>
                @endif
                <td style="text-align: left;">{{ $p->pregunta }}</td>
                <td>{{ $p->n1 }}</td>
                <td>{{ $p->n2 }}</td>
                <td>{{ $p->n3 }}</td>
                <td>{{ $p->n4 }}</td>
                <td>{{ $p->n5 }}</td>
                <td><strong>{{ $p->nota_item_20 }}</strong></td>
            </tr>
            @php
            $sumaNotas += $p->nota_item_20;
            @endphp
            @endforeach

            <!-- ✅ Guardamos promedio de esta área -->
            @php
            $promediosAreas[$area] = round($sumaNotas / $rowspan, 2);
            @endphp
            @endforeach
        </tbody>
    </table>

    <!-- ✅ TABLA RESUMEN ABAJO -->
    @php
    $promedioFinal = round(array_sum($promediosAreas) / count($promediosAreas), 2);
    @endphp

    <table class="resumen-table">
        <thead>
            <tr>
                <th>Área</th>
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($promediosAreas as $area => $promedio)
            <tr>
                <td>{{ $area }}</td>
                <td>{{ $promedio }}</td>
            </tr>
            @endforeach
            <tr class="resumen-final">
                <td>PROMEDIO FINAL DEL CURSO</td>
                <td>{{ $promedioFinal }}</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>
    @endforeach
    @endforeach

</body>

</html>