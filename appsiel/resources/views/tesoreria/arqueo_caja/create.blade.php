@extends('layouts.create')

@section('campos_adicionales')
    <?php
        $valor_base = 0;
        if (Input::get('pdv_id') != null) {
            $pdv = \App\VentasPos\Pdv::find(Input::get('pdv_id'));
            $valor_base = $pdv->get_valor_base_ultima_apertura();
        }
    ?>
    <br>
    <div class="container-fluid">
        
        <h4><i class="fa fa-money"></i> Saldo inicial:</h4>
        <input type="number" id="base" min="0" autocomplete="off" class="form-control" name="base" placeholder="$" value="{{$valor_base}}" required="required" style="width: 200px; text-align: right;">

        <br><br>

        @if( !auth()->user()->hasPermissionTo('vtas_pos_bloqueo_ver_movimientos_sistema_en_arqueo_caja') )
            @include('tesoreria.arqueo_caja.seccion_movimientos_del_sistema')
        @endif

        <h4><i class="fa fa-money"></i>Conteo de efectivo y equivalentes</h4>
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
                    @foreach($array_billetes as $key => $value)
                        <tr>
                            <td class="col-md-2">
                                ${{ number_format($value,'0',',','.') }}
                                <input type="hidden" class="denominacion_b" value="{{$value}}">
                            </td>
                            <td class="col-md-4">
                                <input type="number" min="0" class="form-control cantidad_b" id="billete_{{$value}}"
                                       autocomplete="off" name="billetes[{{$value}}]">
                            </td>
                            <td class="col-md-6">
                                <div class="lbl_total_b"> $0</div>
                                <input type="hidden" class="total_b" name="total_bi" value="0">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="success">
                        <td colspan="2"> Total Billetes</td>
                        <td>
                            <div id="lbl_total_billetes"> $0</div>
                            <input type="hidden" id="total_billetes" name="total_billetes" value="0">
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
                    @foreach($array_monedas as $key => $value)
                        @if($value == '')
                            <tr>
                                <td>&nbsp;</td>
                                <td><input type="number" min="0" class="form-control" disabled="disabled"></td>
                                <td>&nbsp;</td>
                            </tr>
                        @else
                            <tr>
                                <td class="col-md-2">
                                    ${{ number_format($value,'0',',','.') }}
                                    <input type="hidden" class="denominacion_m" value="{{$value}}">
                                </td>
                                <td class="col-md-4">
                                    <input type="number" min="0" class="form-control cantidad_m"
                                           id="billete_{{$value}}" autocomplete="off" name="monedas[{{$value}}]">
                                </td>
                                <td class="col-md-6">
                                    <div class="lbl_total_m"> $0</div>
                                    <input type="hidden" class="total_m" name="total_mo" value="0">
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="success">
                        <td colspan="2"> Total Monedas</td>
                        <td>
                            <div id="lbl_total_monedas"> $0</div>
                            <input type="hidden" id="total_monedas" name="total_monedas" value="0">
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
                           autocomplete="off" name="otros_saldos" placeholder="$" value="0">
                </td>
                <td class="col-md-6">
                    <b>Observaciones</b>
                    <br>
                    <textarea class="form-control" name="detalle_otros_saldos" id="detalle_otros_saldos">&nbsp;</textarea>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="well">
            <h4> <a href="#" data-toggle="tooltip" data-placement="right" title="Conteo" style="text-decoration: none;"> <i class="fa fa-question-circle"></i> </a>  Total efectivo físico:
                <div id="lbl_total_efectivo" style="display: inline;"> $0</div>
            </h4>
            <input type="hidden" id="total_efectivo" name="lbl_total_efectivo" value="0">
        </div>

        <div style="display: none;">
            <div class="well">
                <h4>Diferencia:
                    <div id="lbl_total_saldo" style="display: inline;"> $0</div>
                </h4>
                <input type="hidden" id="total_saldo" name="total_saldo" value="0">
            </div>
        </div>

        <input type="hidden" id="sumar_efectivo_base_en_saldo_esperado" name="sumar_efectivo_base_en_saldo_esperado" value="{{ (int)config('ventas_pos.sumar_efectivo_base_en_saldo_esperado') }}">
    </div>
@endsection

@section('scripts2')
    <script type="text/javascript">

        $(document).ready(function () {

            $('#teso_caja_id').focus();

            $('#fecha').val(get_fecha_hoy());

            var teso_caja_id = getParameterByName( 'teso_caja_id' );

            if( teso_caja_id !== '' )
            {
                var lbl = $('#teso_caja_id option:selected').text();
                $('#teso_caja_id').html('');
                $('#teso_caja_id').html( '<option value="' + teso_caja_id + '">' + lbl + '</option>');

                $('.breadcrumb > li').eq(1).find('a').attr('href','#');   
            }
            var sum;

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


            $('#btn_get_mov_entrada').on('click', function (event) {
                event.preventDefault();

                if (!validar_requeridos()) {
                    return false;
                }
                $('#div_cargando').show();
                var url = '../tesoreria/get_tabla_movimiento';
                $.get(url, {
                    movimiento: 'entrada',
                    fecha_desde: $('#fecha').val(),
                    fecha_hasta: $('#fecha').val(),
                    teso_caja_id: $('#teso_caja_id').val()
                })
                    .done(function (respuesta) {
                        $('#div_cargando').hide();
                        $('#div_mov_entrada').html(respuesta[0]);
                        $('#total_mov_entradas').val(respuesta[1]);
                        $('#movimientos_entradas').val(JSON.stringify(respuesta[2]));
                        calcular_total_sistema();

                        calcular_total_saldo();
                    });

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


            $('#btn_get_mov_salida').on('click', function (event) {
                event.preventDefault();

                if (!validar_requeridos()) {
                    return false;
                }
                $('#div_cargando').show();
                var url = '../tesoreria/get_tabla_movimiento';

                $.get(url, {
                    movimiento: 'salida',
                    fecha_desde: $('#fecha').val(),
                    fecha_hasta: $('#fecha').val(),
                    teso_caja_id: $('#teso_caja_id').val()
                })
                    .done(function (respuesta) {
                        $('#div_cargando').hide();
                        $('#div_mov_salida').html(respuesta[0]);
                        $('#total_mov_salidas').val(respuesta[1] * -1); // Viene negativo
                        $('#movimientos_salidas').val(JSON.stringify(respuesta[2]));

                        calcular_total_sistema();

                        calcular_total_saldo();
                    });

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
                if (total_sistema < 0) {
                    //color_fondo = 'red';
                    //color_letra = 'white';
                    signo = '-$';
                    total_sistema = total_sistema * -1; // para una mejor visualización del signo
                } else {
                    if (total_sistema > 0) {
                        //color_fondo = 'green';
                        //color_letra = 'white';
                    }
                }


                $('#total_sistema').val(total_sistema);
                $('#lbl_total_sistema').html('<span style=" color:' + color_fondo + '; color:' + color_letra + '">' + signo + new Intl.NumberFormat("de-DE").format(total_sistema) + '</span>');

                //$('#lbl_total_sistema').text( '$' + new Intl.NumberFormat("de-DE").format( total_sistema ) );
            }


            function calcular_total_saldo() {
                var total_saldo;


                total_saldo = parseFloat($('#total_efectivo').val()) - Math.abs(parseFloat($('#total_sistema').val()));

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