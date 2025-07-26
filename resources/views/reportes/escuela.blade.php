<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 15px;
            color: #333;
        }

        h2,
        h3 {
            text-align: center;
            margin: 5px 0;
        }

        h4 {
            margin-top: 12px;
            font-size: 13px;
            border-bottom: 1px solid #aaa;
            padding-bottom: 2px;
        }

        p {
            font-size: 11px;
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px;
            font-size: 10px;
            text-align: center;
        }

        th {
            background: #eee;
            font-weight: bold;
        }

        td:first-child {
            font-weight: bold;
        }

        td:nth-child(2) {
            text-align: left;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <h2>Reporte de Encuestas</h2>
    <h3>Escuela: {{ $escuela }} ({{ $cod_escuela }})</h3>

    @foreach($agrupado as $docente => $cursos)
    <h4>Docente: {{ $docente }}</h4>

    @foreach($cursos as $curso => $turnos)
    @foreach($turnos as $turno => $preguntas)
    <p><strong>Curso:</strong> {{ $curso }} | <strong>Turno:</strong> {{ $turno }}</p>

    <table>
        <thead>
            <tr>
                <th>Área</th>
                <th>Pregunta</th>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>Total</th>
                <th>Nota /20</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preguntas as $p)
            <tr>
                <td>{{ $p->nom_area }}</td>
                <td>{{ $p->pregunta }}</td>
                <td>{{ $p->n1 }}</td>
                <td>{{ $p->n2 }}</td>
                <td>{{ $p->n3 }}</td>
                <td>{{ $p->n4 }}</td>
                <td>{{ $p->n5 }}</td>
                <td>{{ $p->total_respuestas }}</td>
                <td><strong>{{ $p->nota_item_20 }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach
    @endforeach

    @if (!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach

</body>

</html>