@extends('layouts.principal')

@section('content')

    {{ Form::bsMigaPan($miga_pan) }}

    <div class="container-fluid">
        <div class="box-header with-border clearfix">
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
                @if(empty($estudiante->user_id))
                    <a href="{{ url('matriculas/estudiantes/asignar_usuario/' . $estudiante->id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo')) }}" class="btn-gmail" title="Asignar usuario">
                        <i class="fa fa-user-plus"></i> 
                    </a>
                @endif
            </div>
            <div class="btn-group pull-right">
                @if($reg_anterior != '')
                    {{ Form::bsBtnPrev('matriculas/estudiantes/show/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif
                @if($reg_siguiente != '')
                    {{ Form::bsBtnNext('matriculas/estudiantes/show/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif
            </div>
        </div>

        <br>
    <div class="container-fluid">
            @include('layouts.mensajes')

            @php
                $matricula_activa = $estudiante->matricula_activa();
                $curso_actual = $matricula_activa ? $matricula_activa->curso : null;
                $nivel_actual = $curso_actual ? $curso_actual->descripcion : null;
                $programa_actual = ($curso_actual && $curso_actual->grado) ? $curso_actual->grado->descripcion : null;
            @endphp

            @include('matriculas.estudiantes.datos_basicos', [
                'estudiante' => $estudiante->get_datos_basicos($estudiante->id),
                'curso_actual' => $curso_actual,
                'nivel_actual' => $nivel_actual,
                'programa_actual' => $programa_actual,
                'vista' => 'show'
            ])
        </div>
    </div>

@endsection
