<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Escuelas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="p-4">
    <div class="container">
        <h2 class="mb-4">📘 Listado de Escuelas</h2>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre de la Escuela</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($escuelas as $e)
                <tr>
                    <td>{{ $e->COD_ESCUELA }}</td>
                    <td>{{ $e->NOM_ESCUELA }}</td>
                    <td>
                        <a href="{{ url('reportes/escuela/'.$e->COD_ESCUELA) }}" class="btn btn-primary btn-sm">
                            📄 Descargar PDF
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>