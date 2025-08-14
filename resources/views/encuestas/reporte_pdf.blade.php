<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte PDF - {{ $nombre }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h3 { text-align: center; }
        .col { width: 48%; display: inline-block; vertical-align: top; margin: 1%; }
        ul { padding-left: 20px; }
    </style>
</head>
<body>
    <h3>Reporte de Encuestas<br>{{ $nombre }}</h3>

    <div class="col">
        <h4 style="color: green;">✅ Encuestados ({{ count($encuestados) }})</h4>
        <ul>
            @foreach($encuestados as $a)
                <li>{{ $a->NOM_ALUMNO }} ({{ $a->COD_ALUMNO }})</li>
            @endforeach
        </ul>
    </div>

    <div class="col">
        <h4 style="color: red;">❌ No Encuestados ({{ count($noEncuestados) }})</h4>
        <ul>
            @foreach($noEncuestados as $a)
                <li>{{ $a->NOM_ALUMNO }} ({{ $a->COD_ALUMNO }})</li>
            @endforeach
        </ul>
    </div>
</body>
</html>
