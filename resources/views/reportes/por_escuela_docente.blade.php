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
            margin-top: 8px;
            margin-bottom: 5px;
        }
        h6 {
            color: #004080;
            margin-top: 8px;
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

        /* ✅ Tabla resumen horizontal */
        .resumen-horizontal {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .resumen-horizontal td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
            width: auto;
        }

        .resumen-final {
            font-weight: bold;
            background-color: #c9daf8;
            margin-top: 8px;
            padding: 6px;
            text-align: center;
            display: inline-block;
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

        /* ❌ Quitamos saltos de página */
        .page-break {
            page-break-after: avoid;
        }
    </style>
</head>

<body>

    <!-- 🔵 Encabezado sin logo -->
    <div class="header">
        <div>
            <h1>UNIVERSIDAD NACIONAL DEL CALLAO</h1>
            <p style="font-size: 10px; margin: 0; color: #444;">
                Escuela: <strong>{{ $escuela }}</strong>
            </p>
        </div>
    </div>

    <!-- 🔵 Información principal -->
    <h6>Docente: {{ $docente }}</h6>

    <!-- 🔵 Tablas por curso y turno -->
    @foreach($cursos as $curso => $turnos)
    @foreach($turnos as $turno => $preguntas)
    <h6>Curso: {{ $curso }} - Turno: {{ $turno }}</h6>

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

            @php
            $promediosAreas[$area] = round($sumaNotas / $rowspan, 2);
            @endphp
            @endforeach
        </tbody>
    </table>

    <!-- ✅ TABLA RESUMEN HORIZONTAL -->
    @php
    $promedioFinal = round(array_sum($promediosAreas) / count($promediosAreas), 2);
    @endphp

    <table class="resumen-horizontal">
        <tr>
            @foreach($promediosAreas as $area => $promedio)
            <td><strong>{{ $area }}</strong><br>{{ $promedio }}</td>
            @endforeach
        </tr>
    </table>

    <!-- ✅ PROMEDIO FINAL ABAJO -->
    <div class="resumen-final">
        PROMEDIO FINAL DEL CURSO: {{ $promedioFinal }}
    </div>

    <!-- ✅ Salto de página solo si NO es el último turno del último curso -->
    @if(! ($loop->last && $loop->parent->last))
    <div style="page-break-after: always;"></div>
    @endif
    @endforeach
    @endforeach

</body>

</html>