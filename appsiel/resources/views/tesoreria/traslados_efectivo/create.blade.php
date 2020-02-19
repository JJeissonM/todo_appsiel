@extends('layouts.principal')

<?php
use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    <hr>

    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <h4>Nuevo registro</h4>
            <hr>
            {{ Form::open([ 'url' => $form_create['url'],'id'=>'form_create']) }}
            <?php
            if (count($form_create['campos']) > 0) {
                $url = htmlspecialchars($_SERVER['HTTP_REFERER']);
                echo '<div class="row" style="margin: 5px;">' . Form::bsButtonsForm2($url) . '</div>';
            } else {
                echo "<p>El modelo no tiene campos asociados.</p>";
            }
            ?>

            {{ VistaController::campos_dos_colummnas($form_create['campos']) }}

            {{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
            {{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}
            {{ Form::hidden( 'url_id_transaccion', Input::get( 'id_transaccion' ) ) }}
            {{ Form::hidden( 'teso_tipo_motivo', 'Traslado' ) }}

            <input type="hidden" name="lineas_registros" id="lineas_registros" value="0">

            {{ Form::close() }}

            <?php

            $fila_foot = '<tr>
					                <td style="display: none;"> <div id="total_valor_total">0</div> </td>
					                <td colspan="5">&nbsp;</td>
					                <td> <div id="total_valor_aux">$0</div> </td>
					                <td> &nbsp;</td>
					            </tr>';

            $datos = [
                'titulo' => '',
                'columnas' => [
                    ['name' => 'teso_medio_recaudo_id', 'display' => '', 'etiqueta' => 'Medio de recaudo', 'width' => ''],
                    ['name' => 'teso_motivo_id', 'display' => '', 'etiqueta' => 'Motivo', 'width' => ''],
                    ['name' => 'teso_caja_id', 'display' => '', 'etiqueta' => 'Caja', 'width' => ''],
                    ['name' => 'teso_cuenta_bancaria_id', 'display' => '', 'etiqueta' => 'Cta. Bancaria', 'width' => ''],
                    ['name' => 'valor', 'display' => '', 'etiqueta' => 'Valor', 'width' => ''],
                    ['name' => '', 'display' => '', 'etiqueta' => ' ', 'width' => '10px']
                ],
                'fila_body' => '',
                'fila_foot' => '<tr id="foot">
								                <td colspan="4">&nbsp;</td>
								                <td> <div id="total_valor_total">$0.00</div> </td>
								                <td> &nbsp;</td>
								            </tr>'
            ];
            ?>

            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#home">Medios de recaudo</a></li>
            </ul>

            <div class="tab-content">

                <div id="home" class="tab-pane fade in active">
                    @include('layouts.elementos.tabla_ingreso_lineas_registros')
                    <a id="btn_nuevo" style="background-color: transparent; color: #3394FF; border: none;"><i
                                class="fa fa-btn fa-plus"></i> Agregar registro</a>
                </div>

            </div>

            <!-- Modal
            @ include('tesoreria.incluir.ingreso_valores_recaudos')-->
        </div>
    </div>
@endsection

@section('scripts')

    <script type="text/javascript">
        $(document).ready(function () {

            $('#fecha').val(get_fecha_hoy());
            $('#fecha').focus();

            /*
            **	Abrir formulario de medios de pago
            */
            $("#btn_nuevo").click(function (event) {
                event.preventDefault();
                nueva_linea_ingreso_datos();
            });

            var LineaNum = 0;


            function nueva_linea_ingreso_datos() {
                $('#div_cargando').fadeIn();

                var url = '{{url('tesoreria/traslado_efectivo/prueba/ajax_get_fila')}}';
                $.get(url, function (datos) {
                    $('#div_cargando').hide();

                    $('#ingreso_registros').find('tbody:first').append(datos);

                    $('#teso_motivo_id').focus();

                    $('#btn_nuevo').hide();
                });

            }


            $('#valor_total').keyup(function (event) {
                /**/
                var ok;
                if ($.isNumeric($(this).val())) {
                    $(this).attr('style', 'background-color:white;');
                    ok = true;
                } else {
                    $(this).attr('style', 'background-color:#FF8C8C;');
                    $(this).focus();
                    ok = false;
                }

                var x = event.which || event.keyCode;
                if (x === 13) {

                    if (ok) {
                        $('#btn_agregar').show();
                        $('#btn_agregar').focus();
                    }

                }
            });


            $(document).on('click', '.btn_confirmar', function (event) {
                event.preventDefault();
                LineaNum++;
                var fila = $(this).closest("tr");
                var ok = validar_linea();
                if (ok) {
                    $("#ingreso_registros").attr('class', 'table');
                    var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
                    var medio = '<span style="color:white;">' + $('#teso_medio_recaudo_id').val() + '-</span>' + $("#teso_medio_recaudo_id option:selected").text();
                    var motivo = '<span style="color:white;">' + $('#teso_motivo_id').val() + '-</span>' + $("#teso_motivo_id option:selected").text();
                    var caja = '<span style="color:white;">' + $('#teso_caja_id').val() + '-</span>' + $("#teso_caja_id option:selected").text();
                    var cuenta = '<span style="color:white;">' + $('#teso_cuenta_bancaria_id').val() + '-</span>' + $("#teso_cuenta_bancaria_id option:selected").text();
                    var valor = $('#valor_total').val();
                    var string = '<tr class="recorrer" id="fila_' + LineaNum + '" >' +
                        '<td id="medio_' + LineaNum + '">' + medio + '</td>' +
                        '<td id="motivo_' + LineaNum + '">' + motivo + '</td>' +
                        '<td id="caja_' + LineaNum + '">' + caja + '</td>' +
                        '<td id="cuenta_' + LineaNum + '">' + cuenta + '</td>';
                    if ($('#teso_motivo_id').val() == 'salida') {
                        string = string + '<td id="valor_' + LineaNum + '"  class="valor">$-' + valor + '</td>';
                    } else {
                        string = string + '<td id="valor_' + LineaNum + '"  class="valor">$' + valor + '</td>';
                    }
                    string = string + '<td>' + btn_borrar + '</td>' + '</tr>';

                    $('#ingreso_registros').find('tbody:last').append(string);

                    calcular_totales();
                    fila.remove();
                    nueva_linea_ingreso_datos();
                }

            });

            function validar_linea() {
                var ok;

                if ($('#teso_medio_recaudo_id').val() != '') {
                    var motivo = '<span style="color:white;">' + $('#teso_motivo_id').val() + '-</span>' + $("#teso_motivo_id option:selected").text();
                    var caja = '<span style="color:white;">' + $('#teso_caja_id').val() + '-</span>' + $("#teso_caja_id option:selected").text();
                    var cuenta = '<span style="color:white;">' + $('#teso_cuenta_bancaria_id').val() + '-</span>' + $("#teso_cuenta_bancaria_id option:selected").text();
                    if ($('#teso_motivo_id').val() == 'salida') {
                        var valor = '-' + $('#valor_total').val();
                    } else {
                        var valor = $('#valor_total').val();
                    }
                    if (valor != '') {
                        if ($.isNumeric(valor)) {
                            ok = true;
                        } else {
                            $('#col_valor').attr('style', 'background-color:#FF8C8C;');
                            $('#col_valor').focus();
                            ok = false;
                        }
                    } else {
                        $('#col_valor').attr('style', 'background-color:#FF8C8C;');
                        $('#col_valor').focus();
                        ok = false;
                    }
                } else {
                    alert('Debe seleccionar un medio de recaudo.');
                    $('#teso_medio_recaudo_id').focus();
                    ok = false;
                }
                return ok;
            }

            /*
            ** Al presionar el botón agregar (ingreso de medios de recaudo)
            */
            // $('#btn_agregar').click(function(event){
            // 	event.preventDefault();
            //
            // 	var valor_total = $('#valor_total').val();
            //
            // 	if($.isNumeric(valor_total) && valor_total>0)
            // 	{
            //
            // 		var medio_recaudo = $( "#teso_medio_recaudo_id" ).val().split('-');
            // 		var texto_medio_recaudo = [ medio_recaudo[0], $( "#teso_medio_recaudo_id option:selected" ).text() ];
            // 		console.log(medio_recaudo);
            // 		if ( medio_recaudo[1] == 'Tarjeta bancaria')
            // 		{
            // 			var texto_caja = [0,''];
            // 			var texto_cuenta_bancaria = [
            // 				$('#teso_cuenta_bancaria_id').val(),
            // 				$('#teso_cuenta_bancaria_id option:selected').text()
            // 			];
            // 		}else{
            // 			var texto_cuenta_bancaria = [0,''];
            // 			var texto_caja = [
            // 				$('#teso_caja_id').val(),
            // 				$('#teso_caja_id option:selected').text()
            // 			];
            // 		}
            //
            // 		var texto_motivo = [ $( "#teso_motivo_id" ).val(), $( "#teso_motivo_id option:selected" ).text() ];
            //
            //
            // 		var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
            //
            //
            // 		celda_valor_total = '<td class="valor_total">$'+valor_total+'</td>';
            //
            // 		$('#ingreso_registros').find('tbody:last').append('<tr>'+
            // 				'<td><span style="color:white;">'+texto_medio_recaudo[0]+'-</span><span>'+texto_medio_recaudo[1]+'</span></td>'+
            // 				'<td><span style="color:white;">'+texto_motivo[0]+'-</span><span>'+texto_motivo[1]+'</span></td>'+
            // 				'<td><span style="color:white;">'+texto_caja[0]+'-</span><span>'+texto_caja[1]+'</span></td>'+
            // 				'<td><span style="color:white;">'+texto_cuenta_bancaria[0]+'-</span><span>'+texto_cuenta_bancaria[1]+'</span></td>'+
            // 				celda_valor_total+
            // 				'<td>'+btn_borrar+'</td>'+
            // 				'</tr>');
            //
            // 		// Se calculan los totales para la última fila
            // 		calcular_totales();
            // 		reset_form_registro();
            //
            // 		deshabilitar_campos_form_create();
            // 		$('#btn_guardar').show();
            //
            // 	}else{
            //
            // 		$('#valor_total').attr('style','background-color:#FF8C8C;');
            // 		$('#valor_total').focus();
            //
            // 		alert('Datos incorrectos o incompletos. Por favor verifique.');
            //
            // 		if ($('#total_valor_total').text()=='$0.00') {
            // 			$('#btn_continuar2').hide();
            // 		}
            // 	}
            // });

            /*
            ** Al eliminar una fila
            */
            // Se utiliza otra forma con $(document) porque el $('#btn_eliminar') no funciona pues
            // es un elemento agregadi despues de que se cargó la página
            $(document).on('click', '.btn_eliminar', function (event) {
                event.preventDefault();
                var fila = $(this).closest("tr");
                fila.remove();
                calcular_totales();
                if ($('#total_valor_total').text() == '$0.00') {
                    habilitar_campos_form_create();
                }
            });


            // Para guardar anticipos o otros recaudos
            $('#btn_guardar').click(function (event) {
                event.preventDefault();

                if ($('#total_valor_total').text() != '$0.00') {
                    alert('Los movimientos de caja no cuadran, el total debe estar en $0.00.');
                    $('#btn_nuevo').focus();
                    return false;
                }


                if (validar_requeridos()) {
                    // Desactivar el click del botón
                    $(this).off(event);

                    habilitar_campos_form_create();

                    $('#foot').remove();
                    $('.linea_ingreso_default').remove();

                    // Se transfoma la tabla a formato JSON a través de un plugin JQuery
                    var table = $('#ingreso_registros').tableToJSON();

                    // Se asigna el objeto JSON a un campo oculto del formulario
                    $('#lineas_registros').val(JSON.stringify(table));

                    $('#form_create').submit();
                }

            });


            function reset_form_registro() {

                var url = '../../tesoreria/ajax_get_motivos/' + $('#teso_tipo_motivo').val();
                $.get(url, function (datos) {
                    $('#teso_motivo_id').html(datos);
                });

                $('#form_registro input[type="text"]').val('');

                $('#form_registro input[type="text"]').attr('style', 'background-color:#ECECE5;');
                $('#form_registro input[type="text"]').attr('disabled', 'disabled');

                $('#div_caja').hide();
                $('#div_cuenta_bancaria').hide();

                $('#btn_agregar').hide();

                $('#teso_medio_recaudo_id').val('');
                $('#teso_medio_recaudo_id').focus();
            }

            function deshabilitar_campos_form_create() {

                // Se cambia el de name al campo core_tercero_id, pues como está desabilitado
                // el SUBMIT del FORM no lo envía en el REQUEST
                $('#fecha').attr('disabled', 'disabled');

                // se oculta la caja de texto del terceor y se muestra el select real
                $('.custom-combobox').hide();

                $('#core_tercero_id').show();
                $('#core_tercero_id').attr('disabled', 'disabled');
                $('#core_tercero_id').attr('name', 'core_tercero_id_original');
                $('#core_tercero_id_aux').val($('#core_tercero_id').val());
                $('#core_tercero_id_aux').attr('name', 'core_tercero_id');


                $('#teso_tipo_motivo').attr('disabled', 'disabled');
            }

            function habilitar_campos_form_create() {
                $('#fecha').removeAttr('disabled');
                $('.custom-combobox').show();

                // Se revierte el cambio de name del select core_tercero_id
                $('#id_tercero').attr('name', 'id_tercero');

                $('#core_tercero_id').hide();
                $('#core_tercero_id').removeAttr('disabled');
                $('#core_tercero_id').attr('name', 'core_tercero_id');

                $('#teso_tipo_motivo').removeAttr('disabled');
            }
        });

        function cambio(event) {
            var valor = $('#teso_medio_recaudo_id').val().split('-');
            if (valor != '') {
                if (valor[1] == 'Tarjeta bancaria') {
                    $('#teso_caja_id').val("");
                    $('#teso_caja_id').attr('disabled', 'true');
                    $('#teso_cuenta_bancaria_id').removeAttr('disabled');
                    $('#teso_cuenta_bancaria_id').parent().show();
                } else {
                    $('#teso_cuenta_bancaria_id').val("");
                    $('#teso_cuenta_bancaria_id').attr('disabled', 'true');
                    $('#teso_caja_id').removeAttr('disabled');
                    $('#teso_caja_id').parent().show();
                }
                habilitar_text($('#valor_total'));
            } else {
                $('#teso_cuenta_bancaria_id').parent().hide();
                $('#teso_caja_id').parent().hide();
                deshabilitar_text($('#valor_total'));
                $(this).focus();
            }
        }

        function calcular_totales() {
            var sum = 0.0;
            sum = 0.0;
            $('.valor').each(function () {
                var cadena = $(this).text();
                sum += parseFloat(cadena.substring(1));
            });

            $('#total_valor_total').text("$" + sum.toFixed(2));
        }

        function habilitar_text($control) {
            $control.removeAttr('disabled');
            $control.attr('style', 'background-color:white;');
        }

        function deshabilitar_text($control) {
            $control.attr('style', 'background-color:#ECECE5;');
            $control.attr('disabled', 'disabled');
        }
    </script>
@endsection