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

        /* 🔵 Encabezado institucional */
        .header {
            text-align: center;
            border-bottom: 4px solid #003366;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
            color: #003366;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header p {
            font-size: 12px;
            margin: 5px 0 0 0;
            color: #555;
        }

        /* 📊 Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #003366;
            color: white;
            font-weight: bold;
        }

        /* 🔹 Títulos de sección */
        h2 {
            font-size: 16px;
            color: #003366;
            margin-bottom: 10px;
            border-left: 5px solid #003366;
            padding-left: 8px;
        }

        /* 🔻 Mensaje cuando no hay datos */
        .no-data {
            font-style: italic;
            color: #FF4721;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <!-- 🔵 ENCABEZADO -->
    <div class="header">
        <h1>UNIVERSIDAD NACIONAL DEL CALLAO</h1>
        <p>Reporte General de Evaluación Docente</p>
        <p>Escuela Profesional: <strong>{{ $escuela }}</strong></p>
    </div>

    <!-- 📊 DOCENTES CON 10 O MÁS ENCUESTADOS -->
    @if(count($masDiez) > 0)
    <!-- <h2>Docentes con más de 10 encuestados</h2> -->
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
            @foreach ($masDiez as $d)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $d->docente }}</td>
                <td>{{ $d->curso }}</td>
                <td>{{ $d->grupo_horario }}</td>
                <td>{{ $d->encuestados }}</td>
                <td><strong>{{ number_format($d->puntaje_promedio, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="no-data">⚠️ No hay docentes con más de 10 encuestados.</p>
    @endif


</body>

</html>