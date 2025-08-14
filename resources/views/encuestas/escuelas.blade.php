@extends('layouts.app')

@section('content')
<div class="container">
    <h3>📚 Escuelas disponibles</h3>
    <ul class="list-group mt-3">
        @foreach($escuelas as $escuela)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $escuela->NOM_ESCUELA }}
                <a href="{{ route('reporte.escuela', $escuela->NOM_ESCUELA) }}" class="btn btn-primary btn-sm">Ver reporte</a>
            </li>
        @endforeach
    </ul>
</div>
@endsection