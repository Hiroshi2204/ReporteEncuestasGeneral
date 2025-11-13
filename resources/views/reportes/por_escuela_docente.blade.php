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

        h1,
        h6 {
            color: #004080;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 10px;
        }

        th {
            background-color: #004080;
            color: white;
            padding: 5px;
            text-align: center;
        }

        td {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f4f8ff;
        }

        .area-cell {
            text-align: left;
            font-weight: bold;
        }

        .resumen-horizontal td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }

        .resumen-final {
            font-weight: bold;
            background-color: #c9daf8;
            padding: 6px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h2>UNIVERSIDAD NACIONAL DEL CALLAO</h2>
    <p>Escuela: <strong>{{ $escuela }}</strong></p>
    <h6>Docente: {{ $docente }}</h6>

    @foreach($cursos as $curso => $turnos)
    @foreach($turnos as $turno => $data)
    <h6>Curso: {{ $curso }} - Turno: {{ $turno }}</h6>

    <table>
        <tr class="info-row">
            <td colspan="8" style="text-align:left;">TOTAL DE ENCUESTADOS: {{ $data['totalEncuestados'] }}</td>
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
            @foreach($data['areas'] as $area => $infoArea)
            @foreach($infoArea['preguntas'] as $index => $p)
            <tr>
                @if($index == 0)
                <td class="area-cell" rowspan="{{ count($infoArea['preguntas']) }}">{{ $area }}</td>
                @endif
                <td style="text-align:left;">{{ $p->pregunta }}</td>
                <td>{{ $p->n1 }}</td>
                <td>{{ $p->n2 }}</td>
                <td>{{ $p->n3 }}</td>
                <td>{{ $p->n4 }}</td>
                <td>{{ $p->n5 }}</td>
                <td><strong>{{ $p->nota_item_20 }}</strong></td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    <table class="resumen-horizontal">
        <tr>
            @foreach($data['areas'] as $area => $infoArea)
            <td><strong>{{ $area }}</strong><br>{{ $infoArea['promedio'] }}</td>
            @endforeach
        </tr>
    </table>

    <div class="resumen-final">
        PROMEDIO GENERAL DEL CURSO: {{ $data['promedioFinal'] }}
    </div>

    @if(! ($loop->last && $loop->parent->last))
    <div style="page-break-after: always;"></div>
    @endif
    @endforeach
    @endforeach
</body>

</html>