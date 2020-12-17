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
            <input type="hidden" name="email" id="email" value="{{ $inscripcion->tercero->email }}">
            <input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{ $inscripcion->tercero->id }}">
            <input type="hidden" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ $inscripcion->fecha_nacimiento }}">
            <input type="hidden" name="estudiante_existe" id="estudiante_existe" value="{{ $estudiante_existe }}">

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
                                <td><input type="checkbox" name="requisito5"> Certificación E.P.S.</td>
                                <td><input type="checkbox" name="requisito6"> Registro de vacunación</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            @if( !$estudiante_existe )

                <div class="panel panel-primary">
                    <div class="panel-heading">DATOS DE PADRES, ACUDIENTE, RESPONSABLE FINANCIERO, ETC.</div>
                    <div class="panel-body">
                        <div class="alert alert-warning" role="alert">
                            <h4><b>Atención! </b>Debe incluir, como mínimo, al responsable financiero (acudiente). Luego puede completar la información de los padres</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="ingreso_lineas_registros">
                                <thead>
                                    <tr>
                                        <th scope="col" style="display: none;">tercero_id</th>
                                        <th scope="col" style="display: none;">tiporesponsable_id</th>
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
                                            <input id="tercero_responsable_id" placeholder="*Nombre del tercero" autocomplete="off" class="form-control text_input_sugerencias" data-url_busqueda="{{ url('core_consultar_terceros_v2') }}" data-clase_modelo="App\Core\Tercero" name="numero_docp[]" type="text" value="">
                                        </td>
                                        <td colspan="3"></td>
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

                {{ Form::hidden( 'lineas_registros', '', ['id'=>'lineas_registros'] ) }}
                {{ Form::hidden( 'responsable_agregado', '', ['id'=>'responsable_agregado'] ) }}

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

            $('#tiporesponsable_idp').focus();

            // generar_html_fila
            html = "<tr>";

            html = html + '<td style="display: none;">' + item_sugerencia.attr( 'data-registro_id' ) + '</td>';
            html = html + '<td style="display: none;"> _id_tipo_responsable_ </td>';

            html = html + "<td>" + item_sugerencia.attr( 'data-numero_identificacion' ) + " - " + item_sugerencia.attr( 'data-descripcion' ) + "</td>";
            html = html + "<td>" + item_sugerencia.attr( 'data-direccion1' ) + "</td>";
            html = html + "<td>" + item_sugerencia.attr( 'data-telefono1' ) + "</td>";
            html = html + "<td>" + item_sugerencia.attr( 'data-email' ) + "</td>";
            html = html + '<td> _texto_responsable_ </td>';
            html = html + "<td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
            html = html + "</tr>";

        }

        $.fn.addRow = function() {
            
            if( $( "#tercero_responsable_id" ).val() == '' || $( "#tiporesponsable_idp" ).val() == '' )
            {
                alert('Debe selecionar un tercero y el tipo de responsable.');

                return false;
            }

            var html2 = html.replace('_id_tipo_responsable_', $( "#tiporesponsable_idp" ).val() );
            var html3 = html2.replace('_texto_responsable_', $( "#tiporesponsable_idp option:selected" ).text() );
            $('#ingreso_lineas_registros tbody:last').append( html3 );
            
            $( "#tercero_responsable_id" ).val('');

            if ( $( "#tiporesponsable_idp" ).val() == 3 )
            {
                $( "#responsable_agregado" ).val(1);
            }

            reset_tipo_responsable( $( "#tiporesponsable_idp" ).val(), $( "#tiporesponsable_idp option:selected" ).text(), 'quitar' );

            hay_responsable++;
        }

        function reset_tipo_responsable( tipo_id, texto_opcion, accion )
        {
            switch( accion )
            {
                case 'quitar':
                    $("#tiporesponsable_idp option[value='"+tipo_id+"']").remove();

                    break;
                case 'agregar':
                    $('#tiporesponsable_idp').append( $('<option>', { value: tipo_id, text: texto_opcion} ) );

                    if ( tipo_id == 3 )
                    {
                        $( "#responsable_agregado" ).val(0);
                    }
                    break;
                default:
                    break;
            }

            console.log( $( "#responsable_agregado" ).val() );
        }

        $(document).on('click', '.delete', function(event) {
            event.preventDefault();
            var fila = $(this).closest('tr');

            reset_tipo_responsable( fila.find('td').eq(1).text(), fila.find('td').eq(6).text(), 'agregar' );

            fila.remove();

            hay_responsable--;
        });



        $(document).ready(function() {

            if( "{{ $estudiante_existe }}" == 1 )
            {
                $( "#responsable_agregado" ).val( 1 );
            }

            $('#sga_grado_id').focus();

            if (typeof $('#btn_imprimir') !== 'undefined') {
                $('#btn_imprimir').focus();
            }

            $('#btn_agregar_linea').on('click', function(event) {
                event.preventDefault();

                $.fn.addRow();

            });

            $('#sga_grado_id').change(function() {
                var grado = $('#sga_grado_id').val().split('-');

                var codigo = $('#codigo').val();

                $('#codigo').val(codigo.replace(codigo.substr(codigo.search("-")), '-' + grado[1]));
            });

            
            $('#bs_boton_guardar').on('click', function(event) {
                event.preventDefault();

                if ( $( "#responsable_agregado" ).val() == 0 )
                {
                    alert('Debe ingresar al responsable del estudiante.');
                    $('#tercero_responsable_id').focus();
                    return false;
                }

                if (!validar_requeridos()) {
                    return false;
                }

                // Desactivar el click del botón
                $(this).off(event);

                var table = $('#ingreso_lineas_registros').tableToJSON();
                $('#lineas_registros').val(JSON.stringify(table));

                $('#form_create').submit();
            });


        });
    </script>
@endsection