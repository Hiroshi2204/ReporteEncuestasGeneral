@extends('layouts.app')

@section('content')
<div class="container">
    <h3>📋 Reporte - {{ $nombre }}</h3>

    <a href="{{ route('reporte.escuela.pdf', $nombre) }}" class="btn btn-danger mb-3">📄 Descargar PDF</a>

    <div class="row">
        <div class="col-md-6">
            <h5 class="text-success">✅ Encuestados ({{ count($encuestados) }})</h5>
            <ul>
                @foreach($encuestados as $a)
                    <li>{{ $a->NOM_ALUMNO }} ({{ $a->COD_ALUMNO }})</li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-6">
            <h5 class="text-danger">❌ No Encuestados ({{ count($noEncuestados) }})</h5>
            <ul>
                @foreach($noEncuestados as $a)
                    <li>{{ $a->NOM_ALUMNO }} ({{ $a->COD_ALUMNO }})</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
