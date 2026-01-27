@extends('layouts.academico_estudiante')

@section('estilos_1')
<style>
    .actividades-hero {
        background: linear-gradient(135deg, #0e3c91, #4f6ad7);
        color: #fff;
        border-radius: 1.2rem;
        padding: 1.6rem 2rem;
        box-shadow: 0 22px 50px rgba(12, 37, 86, 0.3);
        margin-bottom: 1.8rem;
    }

    .actividades-hero h3 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #fff !important;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
    }

    .actividades-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.8);
    }

    .actividades-card {
        border-radius: 1rem;
        box-shadow: 0 18px 40px rgba(15, 32, 92, 0.1);
        border: none;
        background: #fff;
        padding: 1.6rem;
    }

    .actividades-table th {
        background: #f4f5fb;
        color: #1f2a44;
        font-size: 0.85rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.1em;
        border-bottom: none;
    }

    .actividades-table td {
        vertical-align: middle;
        border-top: 1px solid #edf2f7;
        padding: 1rem;
    }

    .actividades-table tbody tr:hover {
        background: #f8f9ff;
    }

    .actividades-table .btn {
        border-radius: 0.6rem;
        font-weight: 600;
    }

    .table-responsive {
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .actividades-hero, .actividades-card {
            padding: 1.2rem;
        }
    }
</style>
@endsection

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
    <div class="actividades-hero">
        <h3>Mis Actividades asignadas</h3>
        <p>Consulta las tareas académicas planificadas para tu curso y accede directamente a cada actividad.</p>
    </div>

    <div class="actividades-card">
        <div class="table-responsive">
            <table class="table actividades-table" id="myTable">
                {{ Form::bsTableHeader(['Periodo','Asignatura','Descripción','Temática','Entrega','Acción']) }}
                <tbody>
                    @foreach ($actividades as $fila)
                    <tr>
                        <td>{{ $fila->periodo_descripcion }}</td>
                        <td>{{ $fila->asignatura_descripcion }}</td>
                        <td>{{ $fila->descripcion }}</td>
                        <td>{{ $fila->tematica }}</td>
                        <td>{{ $fila->fecha_entrega }}</td>
                        <td>
                            {{ Form::bsBtnVer('actividades_escolares/hacer_actividad/'.$fila->id.'?id='.Input::get('id')) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
