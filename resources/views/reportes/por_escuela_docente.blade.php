<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte - {{ $docente }}</title>
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #333; padding: 4px; }
        h2, h3 { margin: 5px 0; }
    </style>
</head>
<body>
    <h2>Reporte de Encuestas - {{ $escuela }}</h2>
    <h3>Docente: {{ $docente }}</h3>

    @foreach($cursos as $curso => $turnos)
        @foreach($turnos as $turno => $preguntas)
            <h4>{{ $curso }} - Turno: {{ $turno }}</h4>
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
                        <td>{{ $p->nota_item_20 }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="page-break-after: always;"></div>
        @endforeach
    @endforeach
</body>
</html>
