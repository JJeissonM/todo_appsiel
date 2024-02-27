<!DOCTYPE html>
<html>
<head>
    <title>Arqueo de Caja</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style>

        @page {
          margin: 15px;
          size: 2in 38.5in;
        }

    </style>
</head>
<?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
    ?>
<body>
    
    <img src="{{ $url_img }}" style="max-height: 110px; width: 100%;" />
    <br>
    @include('ventas_pos.formatos_impresion.datos_encabezado_factura')

        <div class="headdocp" style="width: 100%">
            <b style="font-size: 1.2em; text-align: center; display: block;">{{ $doc_encabezado['titulo'] }}</b>
            <br/>
            <b>Fecha:</b> {{ $doc_encabezado['fecha'] }}

            @yield('datos_adicionales_encabezado')

        </div>                
        <div class="subhead">
            <table>
                <tr>
                    <td>
                        <b>Caja:</b> {{$registro->caja->descripcion}}
                        <br/>
                        <b>Observaciones:</b> {{$registro->observaciones}}
                        <br/>
                        <b>Responsable:</b> {{$user->name}}
                        <br/>
                        <b>Fecha/Hora: &nbsp;&nbsp;</b> {{$registro->created_at}}
                        <br/>
                    </td>                                               
                </tr>
            </table>    
        </div>

        @if(config('ventas_pos.mostrar_resumen_ventas_pos_en_arqueo'))
            @include('tesoreria.arqueo_caja.resumen_ventas_pos2')
        @endif
            
        <table class="table table-bordered">
            <thead>
                <tr>
                    <td colspan="2">
                        <center><strong>ARQUEO DE CAJA</strong></center>
                    </td>
                </tr>  
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="color: black;text-align: center;">
                        <b>SALDO INICIAL:</b> 
                        <br>
                        ${{ number_format($registro->base,'0',',','.') }}
                    </td>
                </tr>
                
                @foreach($registro->detalles_mov_entradas as $item)
                    <tr>
                        <td><b>(+) {{$item->motivo}}</b></td>
                        <td>${{number_format($item->valor_movimiento,'0',',','.')}}</td>
                    </tr>
                @endforeach

                @foreach($registro->detalles_mov_salidas as $item)
                    <tr>
                        <td><b>(-) {{$item->motivo}}</b></td>
                        <td>${{number_format($item->valor_movimiento,'0',',','.')}}</td>
                    </tr>
                @endforeach

                <?php  
                    $efectivo_base = $registro->base;
                    $lbl_efectivo_base = '';
                    if( config('ventas_pos.sumar_efectivo_base_en_saldo_esperado') == 0)
                    {
                        $efectivo_base = 0;
                        $lbl_efectivo_base = '<br>(Sin saldo inicial)';
                    }
                ?>

                <tr>
                    <td colspan="2" style="color: black;text-align: center;">
                        <b>SALDO ESPERADO:</b> {!! $lbl_efectivo_base !!}
                        <br>
                        ${{number_format($efectivo_base + $registro->total_mov_entradas - $registro->total_mov_salidas,'0',',','.')}}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td><b>Total Entradas</b></td>
                    <td>
                        ${{number_format($registro->total_mov_entradas,'0',',','.')}}
                    </td>
                </tr>
                <tr>
                    <td><b>Total Salidas</b></td>
                    <td>
                        ${{number_format($registro->total_mov_salidas,'0',',','.')}}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <center><strong>CONTEO DE EFECTIVO</strong></center>
                    </td>
                </tr>
            
                @foreach($registro->billetes_contados as $key => $value)
                    <tr>
                        <td>
                            <b>{{$value == ""?0:$value}} x ${{number_format($key,'0',',','.')}} = </b>
                        </td>                        <td>
                            ${{number_format($value == ""?0:$key*$value,'0',',','.')}}
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td><b>Total Billetes</b></td>
                    <td>${{number_format($registro->total_billetes,'0',',','.')}}</td>
                </tr>
                
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>

                @foreach($registro->monedas_contadas as $key => $value)
                    <tr>
                        <td>
                            <b>{{$value == ""?0:$value}} x ${{number_format($key,'0',',','.')}} = </b>
                        </td>
                        <td>
                            ${{number_format($value == ""?0:$key*$value,'0',',','.')}}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td><b>Total Monedas</b></td>
                    <td>${{number_format($registro->total_monedas,'0',',','.')}}</td>
                </tr>

                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                
                <tr>
                    <td>
                        <b>Otros Saldos (bonos, pagarés, etc.)</b>: {{$registro->detalle_otros_saldos}}
                    </td>
                    <td>
                        ${{number_format($registro->otros_saldos,'0',',','.')}}
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="color: black;text-align: center;">
                        <b>SALDO EFECTIVO</b>
                        <br>
                        ${{number_format($registro->lbl_total_efectivo,'0',',','.')}}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="color: black;text-align: center;">
                        <b>Diferencia</b>
                        <br>
                        ${{number_format($registro->total_saldo,'0',',','.')}}
                    </td>
                </tr>

            </tbody>
        </table>

    <br><br>
    {!! generado_por_appsiel() !!}     
</body>
</html>