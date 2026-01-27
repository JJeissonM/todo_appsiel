@extends('layouts.principal')

@section('estilos_1')
<style>
    .estudiante-hero {
        background: linear-gradient(135deg, #0e4c8c, #1c75bb);
        color: #fff;
        border-radius: 1.2rem;
        padding: 2rem;
        box-shadow: 0 25px 60px rgba(9, 27, 71, 0.25);
        margin-bottom: 1.5rem;
    }

    .estudiante-hero h3 {
        margin: 0;
        font-weight: 700;
        font-size: 2rem;
    }

    .estudiante-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.85);
        font-size: 1rem;
    }

    .estudiante-card {
        border-radius: 1rem;
        box-shadow: 0 18px 40px rgba(15, 32, 92, 0.1);
        border: none;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .estudiante-card .card-body {
        padding: 1.75rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.2rem;
    }

    .info-box {
        background: #fafbff;
        border-radius: 0.8rem;
        padding: 1rem 1.2rem;
        border: 1px solid #e0e6f1;
        min-height: 160px;
    }

    .info-box strong {
        display: block;
        margin-bottom: 0.35rem;
        color: #1f2b44;
    }

    .info-box span {
        color: #4f5d7a;
        font-size: 0.95rem;
    }

    .informacion-adicional {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        border-radius: 0.8rem;
        padding: 1rem 1.2rem;
        color: #856404;
    }

    .informacion-adicional strong {
        color: #343a40;
    }

    .student-badge {
        display: inline-block;
        padding: 0.35rem 0.9rem;
        border-radius: 999px;
        font-size: 0.8rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .foto-estudiante {
        width: 150px;
        height: 190px;
        object-fit: cover;
        border-radius: 1rem;
        border: 1px solid #e1e5f0;
        box-shadow: 0 10px 20px rgba(15, 32, 92, 0.15);
    }

    @media (max-width: 768px) {
        .estudiante-hero {
            padding: 1.5rem;
        }
    }
</style>
@endsection

@section('content')

    {{ Form::bsMigaPan($miga_pan) }}

    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-6">
            <div class="btn-group">
                @if( isset($url_crear) && $url_crear != '' )
                    {{ Form::bsBtnCreate($url_crear) }}
                @endif
                @if( isset($url_edit) && $url_edit != '' )
                    {{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit), 'Editar') }}
                @endif
                @if(isset($botones))
                    @foreach($botones as $boton)
                        {!! str_replace('id_fila', $registro->id, $boton->dibujar()) !!}
                    @endforeach
                @endif
            </div>
        </div>
        <div class="col-md-6 text-right">
            <div class="btn-group">
                @if($reg_anterior != '')
                    {{ Form::bsBtnPrev('matriculas/estudiantes/show/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif
                @if($reg_siguiente != '')
                    {{ Form::bsBtnNext('matriculas/estudiantes/show/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif
            </div>
        </div>
    </div>

    @include('layouts.mensajes')

    @php
        $edad = null;
        if (!empty($estudiante->fecha_nacimiento)) {
            $edad = (int) \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->age;
        }
        $foto = $estudiante->imagen != ''
            ? asset(config('configuracion.url_instancia_cliente').'/storage/app/fotos_terceros/'.$estudiante->imagen)
            : asset('assets/images/avatar.png');
    @endphp

    <div class="estudiante-hero">
        <div class="row">
            <div class="col-md-9">
                <span class="student-badge">{{ $curso_label ?: 'Curso sin asignar' }}</span>
                <h3>{{ $estudiante->nombre_completo }}</h3>
                <p>Documento: {{ $estudiante->tipo_y_numero_documento_identidad }}</p>
                <p>
                    {{ $estudiante->ciudad_nacimiento ?: 'Ciudad sin dato' }} ·
                    {{ $estudiante->genero ?: 'Género sin dato' }} ·
                    {{ $estudiante->vive_con ?: 'Vive con sin dato' }}
                </p>
            </div>
            <div class="col-md-3 text-center">
                <img src="{{ $foto }}" alt="Foto del estudiante" class="foto-estudiante">
                <p class="text-muted" style="margin-top: 10px;">ID interno: #{{ $estudiante->id }}</p>
                @if($edad !== null)
                    <p class="text-muted">{{ $edad }} años</p>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card estudiante-card">
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-box">
                            <strong>Datos de contacto</strong>
                            <span>Dirección: {{ $estudiante->direccion1 ?: 'Sin dato' }}</span><br>
                            <span>Teléfono: {{ $estudiante->telefono1 ?: 'Sin dato' }}</span><br>
                            <span>Correo: {{ $estudiante->email ?: 'Sin dato' }}</span>
                        </div>
                        <div class="info-box">
                            <strong>Nacimiento</strong>
                            <span>Fecha: {{ $estudiante->fecha_nacimiento ?: 'Sin dato' }}</span><br>
                            <span>Ciudad: {{ $estudiante->ciudad_nacimiento ?: 'Sin dato' }}</span><br>
                            <span>Edad: {{ $edad !== null ? $edad . ' años' : 'Sin dato' }}</span>
                        </div>
                        <div class="info-box">
                            <strong>Salud</strong>
                            <span>EPS: {{ $estudiante->eps ?: 'Sin dato' }}</span><br>
                            <span>Grupo sanguíneo: {{ $estudiante->grupo_sanguineo ?: 'Sin dato' }}</span><br>
                            <span>Alergias / Medicamentos: {{ ($estudiante->alergias ?: 'Sin dato') . ' / ' . ($estudiante->medicamentos ?: 'Sin dato') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="informacion-adicional">
        <strong>Información adicional:</strong>
        <p>Diagnóstico: {{ $estudiante->diagnostico ?: 'Sin dato' }}</p>
        <p>Observaciones generales: {{ $estudiante->observacion_general ?: 'Sin dato' }}</p>
        <p>Número de hermanos: {{ $estudiante->numero_hermanos ?: 'Sin dato' }}</p>
    </div>

    @include('matriculas.estudiantes.datos_basicos_padres', ['vista' => 'show'])

@endsection
