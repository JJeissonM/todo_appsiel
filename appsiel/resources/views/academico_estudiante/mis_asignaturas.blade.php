@extends('layouts.academico_estudiante')

@section('estilos_1')
<link rel="stylesheet" href="{{asset('assets/css/fullcalendar.min.css')}}">
<style>
    .mis-asignaturas-page {
        padding-top: 30px;
    }

    .mis-asignaturas-hero {
        background: linear-gradient(135deg, #1d3c78, #516de7);
        color: #fff;
        border-radius: 1rem;
        padding: 1.6rem 2rem;
        box-shadow: 0 14px 40px rgba(15, 32, 92, 0.2);
        margin-bottom: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .mis-asignaturas-hero h2 {
        margin: 0;
        font-weight: 600;
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .mis-asignaturas-hero p {
        margin: 0;
        color: rgba(255, 255, 255, 0.85);
    }

    .mis-asignaturas-search {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1.5rem;
    }

    .mis-asignaturas-search input {
        flex: 1;
        border-radius: 999px;
        padding: 0.6rem 1.2rem;
        border: 1px solid #ced4da;
        box-shadow: none;
    }

    .asignatura-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.2rem;
    }

    .asignatura-card {
        border-radius: 1rem;
        padding: 1.6rem;
        background: #fff;
        box-shadow: 0 18px 40px rgba(15, 32, 92, 0.08);
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        min-height: 220px;
    }

    .asignatura-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(77, 119, 255, 0.9), rgba(50, 70, 180, 0.9));
        color: #fff;
        font-size: 1.4rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .asignatura-card h4 {
        margin: 0;
        font-weight: 600;
    }

    .asignatura-meta span {
        display: block;
        color: #54607a;
        font-size: 0.9rem;
    }

    .asignatura-links .btn {
        margin-right: 0.4rem;
        margin-bottom: 0.35rem;
        border-radius: 0.6rem;
    }

    .asignatura-links .btn:last-child {
        margin-right: 0;
    }

    @media (max-width: 768px) {
        .mis-asignaturas-hero {
            padding: 1.2rem;
        }

        .mis-asignaturas-search {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endsection

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid mis-asignaturas-page">
    <div class="mis-asignaturas-hero">
        <h2>Mis asignaturas</h2>
        <p>Explora tus actividades escolares, guías académicas y foros del curso actual.</p>
    </div>

    <div class="mis-asignaturas-search">
        <label for="asignaturasSearch"><strong>Buscar asignatura:</strong></label>
        <input type="search" id="asignaturasSearch" placeholder="Filtra por nombre, temática o curso">
    </div>

    <div class="asignatura-grid" id="asignaturaGrid">
        @foreach ($asignaturas as $fila)
        @php
            $label = strtoupper(substr($fila->descripcion, 0, 1));
            $periodo = $fila->periodo_descripcion ?? '';
        @endphp
        <div class="asignatura-card" data-name="{{ strtolower($fila->descripcion . ' ' . ($fila->tematica ?? '')) }}">
            <div class="d-flex justify-content-between align-items-center">
                <div class="asignatura-icon">{{ $label }}</div>
                <span class="text-muted" style="font-size:0.85rem;">{{ $periodo }}</span>
            </div>
            <h4>{{ $fila->descripcion }}</h4>
            <div class="asignatura-meta">
                <span><strong>Temática:</strong> {{ $fila->tematica ?: 'N/A' }}</span>
                <span><strong>Curso:</strong> {{ $fila->curso_descripcion ?? 'Sin info' }}</span>
            </div>
            <div class="asignatura-links">
                <a href="{{url('academico_estudiante/actividades_escolares/'.$curso_id.'/'.$fila->id.'?id='.Input::get('id'))}}"
                    class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i> Actividades</a>
                @if( config('calificaciones.estudiante_revisar_guia_academicas') == 'Si' )
                    <a href="{{ url('academico_estudiante/guias_planes_clases/'.$curso_id.'/'.$fila->id.'?id='.Input::get('id'))}}"
                        class="btn btn-sm btn-outline-danger"><i class="fa fa-book"></i> Guías</a>
                @endif
                @if( config('calificaciones.estudiante_activar_foros_discucion') == 'Si' )
                    <a href="{{ url( 'foros/'.$curso_id.'/'.$fila->id.'/'.$periodo_lectivo->id.'/inicio?id='.Input::get('id') ) }}"
                        class="btn btn-sm btn-outline-info" target="_blank"><i class="fa fa-bullhorn"></i> Foros</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection

@section('scripts')
<script src="{{asset('assets/js/fullcalendar/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/js/fullcalendar/moment.min.js')}}"></script>
<script src="{{asset('assets/js/fullcalendar/fullcalendar.min.js')}}"></script>
<script src="{{asset('assets/js/fullcalendar/es.js')}}"></script>
<script>
    $(document).ready(function () {
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listMonth'
            },
            defaultView: 'agendaWeek',
            minTime: '06:00:00',
            maxTime: '18:00:00',
            eventSources: [
                {
                    url: '../get_eventos',
                    textColor: 'black'
                }
            ]
        });
    });

    $(function() {
        $('#asignaturasSearch').on('input', function() {
            const term = $(this).val().toLowerCase();
            $('#asignaturaGrid .asignatura-card').each(function() {
                const nombre = $(this).data('name');
                $(this).toggle(nombre.indexOf(term) !== -1);
            });
        });
    });
</script>
@endsection
