@extends('layouts.edit')

@section('campos_adicionales')    
    <?php 
        $valor_base = $registro->base;
        $pdv_id_arqueo = isset($registro->pdv_id) ? (int)$registro->pdv_id : 0;
        $pdv_arqueo = $pdv_id_arqueo > 0 ? \App\VentasPos\Pdv::find($pdv_id_arqueo) : null;
        $pdv_descripcion_arqueo = is_null($pdv_arqueo) ? '' : $pdv_arqueo->descripcion;
        $fecha_hora_apertura_arqueo = isset($registro->fecha_hora_apertura) ? $registro->fecha_hora_apertura : '';
        $fecha_hora_cierre_arqueo = isset($registro->fecha_hora_cierre) ? $registro->fecha_hora_cierre : '';

        if (substr($fecha_hora_apertura_arqueo, 0, 10) == '0000-00-00') {
            $fecha_hora_apertura_arqueo = '';
        }

        if (substr($fecha_hora_cierre_arqueo, 0, 10) == '0000-00-00') {
            $fecha_hora_cierre_arqueo = '';
        }
    ?>
    <br>
    
    <div class="container-fluid">
        <input type="hidden" id="creado_por_arqueo" value="{{ $registro->creado_por }}">
        <input type="hidden" id="pdv_id" name="pdv_id" value="{{ $pdv_id_arqueo }}">

        <div class="form-group" style="margin-top: 20px;">
            <label>PDV:</label>
            <div id="pdv_descripcion" class="form-control" style="background-color: #f5f5f5;">{{ $pdv_descripcion_arqueo }}</div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label for="fecha_hora_apertura">Fecha y hora de apertura:</label>
                <input type="datetime-local" id="fecha_hora_apertura" name="fecha_hora_apertura" class="form-control" step="1"
                       value="{{ $fecha_hora_apertura_arqueo == '' ? '' : str_replace(' ', 'T', substr($fecha_hora_apertura_arqueo, 0, 19)) }}">
            </div>
            <div class="col-md-6 form-group">
                <label for="fecha_hora_cierre">Fecha y hora de cierre:</label>
                <input type="datetime-local" id="fecha_hora_cierre" name="fecha_hora_cierre" class="form-control" step="1"
                       value="{{ $fecha_hora_cierre_arqueo == '' ? '' : str_replace(' ', 'T', substr($fecha_hora_cierre_arqueo, 0, 19)) }}">
            </div>
        </div>

        <div id="rango_pdv_mensaje" class="alert alert-warning" style="{{ $fecha_hora_apertura_arqueo != '' && $fecha_hora_cierre_arqueo != '' ? 'display: none;' : '' }}">
            Complete el rango de apertura y cierre, o déjelo vacío para consultar el día completo.
        </div>
        
        <h4><i class="fa fa-money"></i> Saldo inicial:</h4>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="number" id="base" step="any" autocomplete="off" class="form-control" name="base" placeholder="$" value="{{$valor_base}}" required="required" style="width: 200px; text-align: right;">
            @include('tesoreria.arqueo_caja.boton_recalcular_saldo_inicial', ['usuarioArqueo' => auth()->user()])
        </div>

        <br><br>

        @if( !\App\Tesoreria\ArqueoCaja::usuario_tiene_bloqueo_movimientos_sistema(auth()->user()) )
            @include('tesoreria.arqueo_caja.seccion_movimientos_del_sistema')
        @endif

        <h4><i class="fa fa-money"></i> Conteo de efectivo y equivalentes</h4>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h5 style="text-align: center;">Billetes</h5>
                <?php
                $array_billetes = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
                ?>
                <table class="table table-striped table-bordered table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>Denominación</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($registro->billetes_contados as $key => $value)
                        <tr>
                            <td class="col-md-2">
                                ${{ number_format($key,'0',',','.') }}
                                <input type="hidden" class="denominacion_b" value="{{$key}}">
                            </td>
                            <td class="col-md-4">
                                <input type="number" min="0" class="form-control cantidad_b" id="billete_{{$key}}"
                                       autocomplete="off" name="billetes[{{$key}}]" value="{{$value==""?0:$value}}">
                            </td>
                            <td class="col-md-6">
                                <div class="lbl_total_b">{{$value == ""?0:$key*$value}}</div>
                                <input type="hidden" class="total_b" name="total_bi"
                                       value="{{$value == ""?0:$key*$value}}">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="success">
                        <td colspan="2"> Total Billetes</td>
                        <td>
                            <div id="lbl_total_billetes"> ${{$registro->total_billetes}}</div>
                            <input type="hidden" id="total_billetes" name="total_billetes" value="{{$registro->total_billetes}}">
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="col-md-6">
                <h5 style="text-align: center;">Monedas</h5>
                <?php
                $array_monedas = [1000, 500, 200, 100, 50, '', ''];
                ?>

                <table class="table table-striped table-bordered table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>Denominación</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($registro->monedas_contadas as $key => $value)

                        @if($key == '')
                            <tr>
                                <td>&nbsp;</td>
                                <td><input type="number" min="0" class="form-control" disabled="disabled"></td>
                                <td>&nbsp;</td>
                            </tr>
                        @else
                            <tr>
                                <td class="col-md-2">
                                    ${{ number_format($key,'0',',','.') }}
                                    <input type="hidden" class="denominacion_m" value="{{$key}}">
                                </td>
                                <td class="col-md-4">
                                    <input type="number" min="0" class="form-control cantidad_m"
                                           id="billete_{{$key}}" autocomplete="off" name="monedas[{{$key}}]" value="{{$value==""?0:$value}}">
                                </td>
                                <td class="col-md-6">
                                    <div class="lbl_total_m"> {{$value == ""?0:$key*$value}}</div>
                                    <input type="hidden" class="total_m" name="total_mo" value="{{$value == ""?0:$key*$value}}">
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="success">
                        <td colspan="2"> Total Monedas</td>
                        <td>
                            <div id="lbl_total_monedas"> ${{$registro->total_monedas}}</div>
                            <input type="hidden" id="total_monedas" name="total_monedas" value="{{$registro->total_monedas}}">
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th colspan="2">Saldo en bonos, recibos, pagarés y otros documentos</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="col-md-6">
                    <b>Saldo Total</b>
                    <br><br>
                    <input type="text" min="0" class="form-control otros_saldos" id="otros_saldos"
                           autocomplete="off" name="otros_saldos" placeholder="$" value="{{$registro->otros_saldos}}">
                </td>
                <td class="col-md-6">
                    <b>Observaciones</b>
                    <br>
                    <textarea class="form-control" name="detalle_otros_saldos" id="detalle_otros_saldos">{{$registro->detalle_otros_saldos}}</textarea>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="well">
            <h4> <a href="#" data-toggle="tooltip" data-placement="right" title="Conteo" style="text-decoration: none;"> <i class="fa fa-question-circle"></i> </a> Total efectivo físico:
                <div id="lbl_total_efectivo" style="display: inline;"> ${{$registro->lbl_total_efectivo}}</div>
            </h4>
            <input type="hidden" id="total_efectivo" name="lbl_total_efectivo" value="{{$registro->lbl_total_efectivo}}">
        </div>

        <div style="display: none;">
            <div class="well">
                <h4>Diferencia:
                    <div id="lbl_total_saldo" style="display: inline;"> ${{$registro->total_saldo}}</div>
                </h4>
                <input type="hidden" id="total_saldo" name="total_saldo" value="{{$registro->total_saldo}}">
            </div>
        </div>

        <input type="hidden" id="sumar_efectivo_base_en_saldo_esperado" name="sumar_efectivo_base_en_saldo_esperado" value="{{ (int)config('ventas_pos.sumar_efectivo_base_en_saldo_esperado') }}">
    </div>
@endsection

@section('scripts2')
    <script type="text/javascript">

        $(document).ready(function () {

            $('#teso_caja_id').focus();
            get_mov_entrada();
            get_mov_salida();

            var sum;

            $('#teso_caja_id').on('change', function () {
                $('#pdv_id').val('');
                $('#pdv_descripcion').text('');
                $('#fecha_hora_apertura').val('');
                $('#fecha_hora_cierre').val('');
                actualizarMensajeRango();
            });

            $('#fecha_hora_apertura, #fecha_hora_cierre').on('change', function () {
                actualizarMensajeRango();
            });

            $('form').on('submit', function () {
                return validarRangoFechaHora();
            });

            // PARA BILLETES
            $('.cantidad_b').on('change keyup', function () {
                var fila = $(this).closest('tr');
                var total = fila.find('.denominacion_b').val() * $(this).val();
                fila.find('.total_b').val(total);
                fila.find('.lbl_total_b').text('$' + new Intl.NumberFormat("de-DE").format(total));

                calcular_totales_b();

                calcular_total_efectivo();

                calcular_total_saldo();
            });

            function calcular_totales_b() {
                sum = 0;
                $('.total_b').each(function () {
                    sum += parseFloat($(this).val());
                });

                $('#total_billetes').val(sum);
                $('#lbl_total_billetes').text('$' + new Intl.NumberFormat("de-DE").format(sum));
            }


            // PARA MONEDAS
            $('.cantidad_m').on('change keyup', function () {
                var fila = $(this).closest('tr');
                var total = fila.find('.denominacion_m').val() * $(this).val();
                fila.find('.total_m').val(total);
                fila.find('.lbl_total_m').text('$' + new Intl.NumberFormat("de-DE").format(total));

                calcular_totales_m();

                calcular_total_efectivo();

                calcular_total_saldo();
            });

            function calcular_totales_m() {
                sum = 0;
                $('.total_m').each(function () {
                    sum += parseFloat($(this).val());
                });

                $('#total_monedas').val(sum);
                $('#lbl_total_monedas').text('$' + new Intl.NumberFormat("de-DE").format(sum));
            }

            $('#base').on('keyup', function () {
                if (validar_input_numerico($(this))) {
                    calcular_total_sistema();
                    calcular_total_saldo();
                } else {
                    $(this).select();
                }
            });

            @include('tesoreria.arqueo_caja.script_recalcular_saldo_inicial')

            $('.otros_saldos').on('keyup', function () {
                if (validar_input_numerico($(this))) {
                    calcular_total_efectivo();
                    calcular_total_saldo();
                } else {
                    $(this).select();
                }
            });

            function calcular_total_efectivo() {
                var otros_saldos = 0;
                if ($.isNumeric($('#otros_saldos').val())) {
                    otros_saldos = parseFloat($('#otros_saldos').val());
                }

                var total_efectivo = parseFloat($('#total_billetes').val()) + parseFloat($('#total_monedas').val()) + otros_saldos;
                $('#total_efectivo').val(total_efectivo);
                $('#lbl_total_efectivo').text('$' + new Intl.NumberFormat("de-DE").format(total_efectivo));
            }

            function valorFechaHoraArqueo(selector) {
                var valor = $(selector).val();
                if (!valor || valor.indexOf('0000-00-00') === 0) {
                    return '';
                }

                return valor;
            }

            function actualizarMensajeRango() {
                var apertura = valorFechaHoraArqueo('#fecha_hora_apertura');
                var cierre = valorFechaHoraArqueo('#fecha_hora_cierre');
                $('#rango_pdv_mensaje').toggle(apertura === '' || cierre === '');
            }

            function validarRangoFechaHora() {
                var apertura = valorFechaHoraArqueo('#fecha_hora_apertura');
                var cierre = valorFechaHoraArqueo('#fecha_hora_cierre');

                if ((apertura === '') !== (cierre === '')) {
                    alert('Debe ingresar tanto la fecha y hora de apertura como la de cierre.');
                    return false;
                }

                if (apertura !== '' && apertura.substring(0, 10) !== $('#fecha').val()) {
                    alert('La apertura debe pertenecer a la fecha seleccionada para el arqueo.');
                    return false;
                }

                if (apertura !== '' && cierre < apertura) {
                    alert('La fecha y hora de cierre no puede ser anterior a la apertura.');
                    return false;
                }

                return true;
            }

            function obtenerMensajeErrorAjax(xhr, fallback) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    return xhr.responseJSON.message;
                }

                return fallback;
            }

            function get_mov_entrada(){
                $('#div_cargando').show();
                var url = '../../../tesoreria/get_tabla_movimiento';
                $.get(url, {
                    movimiento: 'entrada',
                    fecha_desde: $('#fecha').val(),
                    fecha_hasta: $('#fecha').val(),
                    teso_caja_id: $('#teso_caja_id').val(),
                    creado_por: $('#creado_por_arqueo').val(),
                    pdv_id: $('#pdv_id').val(),
                    fecha_hora_apertura: valorFechaHoraArqueo('#fecha_hora_apertura'),
                    fecha_hora_cierre: valorFechaHoraArqueo('#fecha_hora_cierre')
                })
                    .done(function (respuesta) {
                        $('#div_cargando').hide();
                        $('#div_mov_entrada').html(respuesta[0]);
                        $('#total_mov_entradas').val(respuesta[1]);
                        $('#movimientos_entradas').val(JSON.stringify(respuesta[2]));
                        calcular_total_sistema();

                        calcular_total_saldo();
                    }).fail(function (xhr) {
                        $('#div_cargando').hide();
                        alert(obtenerMensajeErrorAjax(xhr, 'No fue posible obtener los movimientos de entrada.'));
                    });
            }

            $('#btn_get_mov_entrada').on('click', function (event){
                event.preventDefault();

                if (!validar_requeridos()) {
                    return false;
                }
                get_mov_entrada();
            });

            $('#btn_reset_mov_entrada').on('click', function (event) {
                event.preventDefault();

                if (!validar_requeridos()) {
                    return false;
                }

                $('#div_mov_entrada').html('');
                $('#total_mov_entradas').val(0);
                $('#movimientos_entradas').val('');

                calcular_total_sistema();

                calcular_total_saldo();

            });

            function get_mov_salida(){
                $('#div_cargando').show();
                var url = '../../../tesoreria/get_tabla_movimiento';
                $.get(url, {
                    movimiento: 'salida',
                    fecha_desde: $('#fecha').val(),
                    fecha_hasta: $('#fecha').val(),
                    teso_caja_id: $('#teso_caja_id').val(),
                    creado_por: $('#creado_por_arqueo').val(),
                    pdv_id: $('#pdv_id').val(),
                    fecha_hora_apertura: valorFechaHoraArqueo('#fecha_hora_apertura'),
                    fecha_hora_cierre: valorFechaHoraArqueo('#fecha_hora_cierre')
                })
                    .done(function (respuesta) {
                        $('#div_cargando').hide();
                        $('#div_mov_salida').html(respuesta[0]);
                        $('#total_mov_salidas').val(respuesta[1] * -1); // Viene negativo
                        $('#movimientos_salidas').val(JSON.stringify(respuesta[2]));
                        calcular_total_sistema();

                        calcular_total_saldo();
                    }).fail(function (xhr) {
                        $('#div_cargando').hide();
                        alert(obtenerMensajeErrorAjax(xhr, 'No fue posible obtener los movimientos de salida.'));
                    });
            }

            $('#btn_get_mov_salida').on('click', function (event) {
                event.preventDefault();

                if (!validar_requeridos()) {
                    return false;
                }
                get_mov_salida();
            });

            $('#btn_reset_mov_salida').on('click', function (event) {
                event.preventDefault();

                if (!validar_requeridos()) {
                    return false;
                }

                $('#div_mov_salida').html('');
                $('#total_mov_salidas').val(0);
                $('#movimientos_salidas').val('');
                calcular_total_sistema();

                calcular_total_saldo();

            });


            function calcular_total_sistema() {

                var efectivo_base = parseFloat($('#base').val());
                if ( $('#sumar_efectivo_base_en_saldo_esperado').val() == 0 ) {
                    efectivo_base = 0;
                }

                var total_sistema = parseFloat($('#total_mov_entradas').val()) + efectivo_base - parseFloat($('#total_mov_salidas').val());

                var color_fondo = 'transparent';
                var color_letra = '#444444';
                var signo = '$';
                var lbl_total_sistema = total_sistema;
                if (total_sistema < 0) {
                    color_letra = 'red';
                    signo = '-$';
                    lbl_total_sistema = total_sistema * -1; // para una mejor visualización del signo
                } else {
                    if (total_sistema > 0) {
                        color_letra = 'green';
                    }
                }


                $('#total_sistema').val(total_sistema);
                $('#lbl_total_sistema').html('<span style=" color:' + color_fondo + '; color:' + color_letra + '">' + signo + new Intl.NumberFormat("de-DE").format(lbl_total_sistema) + '</span>');
            }


            function calcular_total_saldo() {
                var total_saldo;


                total_saldo = parseFloat($('#total_efectivo').val()) - parseFloat($('#total_sistema').val());

                $('#total_saldo').val(total_saldo);

                var color_fondo = 'transparent';
                var color_letra = '#444444';
                var signo = '$';
                if (total_saldo < 0) {
                    color_letra = 'red';
                    signo = '-$';
                    total_saldo = total_saldo * -1; // para una mejor visualización del signo
                } else {
                    if (total_saldo > 0) {
                        color_letra = 'orange';
                    }
                }

                $('#lbl_total_saldo').html('<span style=" background-color:' + color_fondo + '; color:' + color_letra + '">' + signo + new Intl.NumberFormat("de-DE").format(total_saldo) + '</span>');
            }

        });

    </script>
@endsection
