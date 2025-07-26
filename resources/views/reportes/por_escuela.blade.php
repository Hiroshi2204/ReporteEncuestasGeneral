<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
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
                <td><strong>{{ $p->total_respuestas }}</strong></td>
                <td style="background: #f1f8e9;"><strong>{{ $p->nota_item_20 }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach
    @endforeach

    <div class="page-break"></div>
    @endforeach
</body>

</html>