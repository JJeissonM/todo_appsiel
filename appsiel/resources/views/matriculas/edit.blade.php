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
            <br/><br/>

            {{ Form::model($registro, ['url' => ['matriculas/'.$registro->id], 'method' => 'PUT', 'id' => 'form_create']) }}

				@include('matriculas.incluir.datos_inscripcion')

                <div class="panel panel-primary">
                    <div class="panel-heading">DATOS DE LA MATRÍCULA</div>
                    <div class="panel-body">
                        {{ VistaController::campos_dos_colummnas($form_create['campos']) }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Requisitos de matrícula</div>
                            <div class="panel-body">
                                <?php
                                    $requisitos = explode("-",str_replace("on","checked",$registro->requisitos));
                                    //print_r($requisitos);
                                ?>
                                <table class="fluid">
                                    <tr>
                                        <td><input type="checkbox" {{$requisitos[0]}} name="requisito1" id="matricular"> Documento identidad</td>
                                        <td><input type="checkbox" {{$requisitos[1]}} name="requisito2" id="matricular"> Constancia SIMAT</td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" {{$requisitos[2]}} name="requisito3" id="matricular"> Fotos</td>
                                        <td><input type="checkbox" {{$requisitos[3]}} name="requisito4" id="matricular"> Registro calificaciones</td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" {{$requisitos[4]}} name="requisito5" id="matricular"> Carnet EPS</td>
                                        <td><input type="checkbox" {{$requisitos[5]}} name="requisito6" id="matricular"> Registro de vacunación</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>                    
                </div>

                <br/><br/>  

                <div class="row">
                    <div class="col-md-6">
                        {{ Form::bsSelect('estado',null,'Estado',['Activo'=>'Activo','Inactivo'=>'Inactivo'],[]) }}
                    </div>
                    <div class="col-md-6">
                        &nbsp;
                    </div>
                </div>

                <div align="center">

                    
                    {{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
                    {{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}

                    {{ Form::bsButtonsForm($miga_pan[count($miga_pan)-2]['url'])}}
                </div>
			{{ Form::close() }}

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

            $('#sga_grado_id').change(function(){
                var grado = $('#sga_grado_id').val().split('-');

                var codigo = $('#codigo').val();

                $('#codigo').val(  codigo.replace( codigo.substr( codigo.search("-") ),'-'+grado[1]) );
            });
        });        
    </script>
@endsection