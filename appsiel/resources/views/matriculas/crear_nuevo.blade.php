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

        <br /><br />
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
                                <td><input type="checkbox" name="requisito1"> Documento identidad</td>
                                <td><input type="checkbox" name="requisito2"> Constancia SIMAT</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="requisito3"> Fotos</td>
                                <td><input type="checkbox" name="requisito4"> Registro calificaciones</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="requisito5"> Carnet EPS</td>
                                <td><input type="checkbox" name="requisito6"> Registro de vacunación</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            @if( !$estudiante_existe )
            <!--@include('matriculas.incluir.paneles_padres')-->

            <div class="panel panel-primary">
                <div class="panel-heading">DATOS DE PADRES, ACUDIENTE, RESPONSABLE FINANCIERO, ETC.</div>
                <div class="panel-body">
                    <div class="alert alert-warning" role="alert">
                        <h4><b>Atención! </b>Debe incluir, como mínimo, al responsable financiero (acudiente). Luego puede completar la información de los padres</h4>
                    </div>
                    <div class="table-responsive">
                        <table id="tbPersonas" class="table table-hover table-dark table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Tercero</th>
                                    <th scope="col">Dirección</th>
                                    <th scope="col">Teléfono</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col">Tipo de responsable</th>
                                    <th scope="col"><i class="fa fa-remove"></i></th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>
                                        <input id="core_tercero_id" placeholder="*Nombre del tercero" autocomplete="off" class="form-control text_input_sugerencias" data-url_busqueda="{{ url('core_consultar_terceros_v2') }}" data-clase_modelo="App\Core\Tercero" required="required" name="numero_docp[]" type="text" value="">
                                    </td>
                                    <td colspan="4"></td>
                                    <td>
                                        {{ Form::bsSelect('tiporesponsable_idp', null, '', App\Matriculas\Tiporesponsable::opciones_campo_select(), ['class'=>'form-control']) }}
                                    </td>
                                    <td> <button class="btn btn-success btn-xs" id="btn_agregar_linea"> <i class="fa fa-check"></i> </button></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                        
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
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
    
    var html;
    var hay_responsable = 0;

    function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
    {
        console.log( item_sugerencia.attr('data-descripcion') );

        $('#tiporesponsable_idp').focus();

        html = "<tr>";

        html = html + "<td>" + item_sugerencia.attr( 'data-numero_identificacion' ) + " - " + item_sugerencia.attr( 'data-descripcion' ) + "</td>";
        html = html + "<td>" + item_sugerencia.attr( 'data-direccion1' ) + "</td>";
        html = html + "<td>" + item_sugerencia.attr( 'data-telefono1' ) + "</td>";
        html = html + "<td>" + item_sugerencia.attr( 'data-email' ) + "</td>";
        html = html + "<td>" + $('#tiporesponsable_idp option:selected').text() + "</td>";
        html = html + "<td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
        html = html + "</tr>";

    }

    $.fn.addRow = function() {
        
        $('#tbPersonas tr:last').after( html );
        hay_responsable++;

    }

    $(document).on('click', '.delete', function(event) {
        event.preventDefault();
        $(this).closest('tr').remove();
        hay_responsable--;
    });



    $(document).ready(function() {
        
        $('#btn_agregar_linea').on('click', function(event) {
            event.preventDefault();

            /*if (!validar_requeridos()) {
                return false;
            }*/

            $.fn.addRow();

        });
        
        $('#bs_boton_guardar').on('click', function(event) {
            event.preventDefault();

            if (!validar_requeridos()) {
                return false;
            }
            // Desactivar el click del botón
            $(this).off(event);

            $('#form_create').submit();
        });


        $('#sga_grado_id').focus();

        if (typeof $('#btn_imprimir') !== 'undefined') {
            $('#btn_imprimir').focus();
        }



        $('#sga_grado_id').change(function() {
            var grado = $('#sga_grado_id').val().split('-');

            var codigo = $('#codigo').val();

            $('#codigo').val(codigo.replace(codigo.substr(codigo.search("-")), '-' + grado[1]));
        });


        var documento_inicial = parseInt( $("#numero_identificacion").val() );
        
        $(document).on('blur','.numero_docp',function(){
            var documento = $(this).val();

            /* Cuando el javascript está dentro de una vista blade se puede llamar la url de la siguiente forma:*/
            var url = "{{ url('core/validar_numero_identificacion/') }}" + "/" + documento;
            
            $.get( url, function( datos ) 
            {
                if ( datos != '') 
                {
                    if ( parseInt(datos) == documento_inicial ) 
                    {
                        // No hay problema
                        $('#bs_boton_guardar').show();
                    }else{
                        $('#bs_boton_guardar').hide();
                        alert( "Ya existe una persona con ese número de documento de identidad. Cambié el número o no podrá guardar el registro." );
                    }
                    
                }else{
                    // Número de identificación
                    $('#bs_boton_guardar').show();
                }
                
            });
        });

    });

    var tipos = <?php echo json_encode($tipos); ?>;
    var tiposdoc = <?php echo json_encode($tiposdoc); ?>;

    var io = 0;

    

    function cambiar(id) {
        var rf = $("#" + id + " option:selected").text();
        if (rf == 'RESPONSABLE-FINANCIERO') {
            $("#exampleModal").modal('show');
            $("#ponerID").val(id);
        }
    }

    function guardar() {
        var id = $("#ponerID").val();
        //recojo la informacion y la coloco en el input hidden del row
        var html = $("#direccion_trabajo").val() + ";" + $("#telefono_trabajo").val() + ";" + $("#puesto_trabajo").val();
        html = html + ";" + $("#empresa_labora").val() + ";" + $("#jefe_inmediato").val() + ";" + $("#telefono_jefe").val();
        html = html + ";" + $("#descripcion_trabajador_independiente").val()
        $("#hidden_" + id).val(html);
        $("#exampleModal").modal('hide');
    }
</script>
@endsection