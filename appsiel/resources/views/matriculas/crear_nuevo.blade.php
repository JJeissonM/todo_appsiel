<?php
    use App\Http\Controllers\Sistema\VistaController;
?>

@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    <hr>

    @include('layouts.mensajes')
    <div class="container-fluid">
        <div class="marco_formulario">

            @include('matriculas.incluir.matriculas_anteriores')

            <br/><br/>
			<form action="{{ url('matriculas') }}" method="POST" class="form-horizontal" id="form_create">
				{{ csrf_field() }}
                
				<input type="hidden" name="id_colegio" id="id_colegio" value="{{ $id_colegio }}">

				@include('matriculas.incluir.datos_inscripcion')

                <div class="panel panel-primary">
                    <div class="panel-heading">DATOS DE LA MATRÍCULA</div>
                    <div class="panel-body">
                        {{ VistaController::campos_dos_colummnas($form_create['campos']) }}
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">Requisitos de matrícula</div>
                        <div class="panel-body">
                            <table class="fluid" width="100%">
                                <tr>
                                    <td><input type="checkbox" name="requisito1" id="matricular"> Documento identidad</td>
                                    <td><input type="checkbox" name="requisito2" id="matricular"> Constancia SIMAT</td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="requisito3" id="matricular"> Fotos</td>
                                    <td><input type="checkbox" name="requisito4" id="matricular"> Registro calificaciones</td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="requisito5" id="matricular"> Carnet EPS</td>
                                    <td><input type="checkbox" name="requisito6" id="matricular"> Registro de vacunación</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                @if( !$estudiante_existe )
                    @include('matriculas.incluir.paneles_padres')

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">Controles médicos</div>
                                <div class="panel-body">
                                    <div class="row" style="padding:5px;">
                                        {{ Form::bsText('grupo_sanguineo', null, 'Grupo sanguíneo', []) }}
                                    </div>

                                    <div class="row" style="padding:5px;">
                                        {{ Form::bsText('medicamentos', null, 'Medicamento', []) }}
                                    </div>

                                    <div class="row" style="padding:5px;">
                                        {{ Form::bsText('alergias', null, 'Alergias', []) }}
                                    </div>

                                    <div class="row" style="padding:5px;">
                                        {{ Form::bsText('eps', null, 'EPS', []) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            &nbsp;
                        </div>                    
                    </div>
                @endif

                <div align="center">

                    {{ Form::hidden('estudiante_existe', $estudiante_existe) }}
                    
                    {{ Form::hidden('core_tercero_id', $tercero->id) }}
                    {{ Form::hidden('nombre1', $tercero->nombre1) }}
                    {{ Form::hidden('otros_nombres', $tercero->otros_nombres) }}
                    {{ Form::hidden('apellido1', $tercero->apellido1) }}
                    {{ Form::hidden('apellido2', $tercero->apellido2) }}
                    {{ Form::hidden('email', $tercero->email) }}
                    {{ Form::hidden('nombres', $tercero->nombre1." ".$tercero->otros_nombres) }}
                    {{ Form::hidden('tipo_doc_id', $tercero->id_tipo_documento_id) }}
                    {{ Form::hidden('doc_identidad', $tercero->numero_identificacion) }}
                    {{ Form::hidden('direccion1', $tercero->direccion1) }}
                    {{ Form::hidden('telefono1', $tercero->telefono1) }}

                    {{ Form::hidden('genero',$inscripcion->genero) }}

                    {{ Form::hidden('fecha_nacimiento',$inscripcion->fecha_nacimiento) }}
                    {{ Form::hidden('ciudad_nacimiento',$inscripcion->ciudad_nacimiento) }}
                    {{ Form::hidden('estado','Activo') }}
                    {{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
                    {{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}

                    {{ Form::bsButtonsForm($miga_pan[count($miga_pan)-2]['url'])}}
                </div>
			</form>

        </div>
	</div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function(){

            $('#bs_boton_guardar').on('click',function(event){
                event.preventDefault();

                if ( !validar_requeridos() )
                {
                    return false;
                }

                // Desactivar el click del botón
                $( this ).off( event );

                $('#form_create').submit();
            });
            

            $('#sga_grado_id').focus();

            if ( typeof $('#btn_imprimir') !== 'undefined' ) {
                $('#btn_imprimir').focus();
            }

            

            $('#sga_grado_id').change(function(){
                var grado = $('#sga_grado_id').val().split('-');

                var codigo = $('#codigo').val();

                $('#codigo').val(  codigo.replace( codigo.substr( codigo.search("-") ),'-'+grado[1]) );
            });
        });        
    </script>
@endsection