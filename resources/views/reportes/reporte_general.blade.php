<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte General - {{ $escuela }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 15px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 4px solid #003366;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 20px;
            color: #003366;
            margin: 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #003366;
            color: #fff;
        }

        h2 {
            font-size: 15px;
            color: #003366;
            border-left: 5px solid #003366;
            padding-left: 8px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>UNIVERSIDAD NACIONAL DEL CALLAO</h1>
        <p>Reporte General de Evaluación Docente</p>
        <p>Escuela Profesional: <strong>{{ $escuela }}</strong></p>
    </div>

    @if(count($ranking) > 0)
    <table>
        <thead>
            <tr>
                <th>Orden</th>
                <th>Docente</th>
                <th>Curso</th>
                <th>Grupo Horario</th>
                <th>Encuestados</th>
                <th>Promedio Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $r)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $r['docente'] }}</td>
                <td>{{ $r['curso'] }}</td>
                <td>{{ $r['grupo_horario'] }}</td>
                <td>{{ $r['encuestados'] }}</td>
                <td><strong>{{ number_format($r['promedio_final'], 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color: #ff0000; font-style: italic;">⚠️ No hay docentes con más de 10 encuestados.</p>
    @endif

</body>

</html>