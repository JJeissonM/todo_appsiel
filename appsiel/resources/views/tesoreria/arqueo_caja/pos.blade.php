<!DOCTYPE html>
<html>
<head>
    <title>Arqueo de Caja</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style>
        @page {
          size: 3.15in 38.5in;
          margin: 15px;
        }
        
    </style>
</head>
<?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
    ?>
<body>
        <div class="headempresap">
            <table border="0" style="margin-top: 12px !important;" width="100%">
                <tr>
                    <td width="15%">
                        <img src="{{ $url_img }}" width="80px;" />
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <br/>
                            <b>{{ $empresa->descripcion }}</b><br/>
                            <b>{{ $empresa->nombre1 }} {{ $empresa->apellido1 }} {{ $empresa->apellido2 }}</b><br/>
                            <b>{{ config("configuracion.tipo_identificador") }}.
                            @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
    
                            {{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} <br/>
                            Teléfono(s): {{ $empresa->telefono1 }}<br/>
                            <b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
                        </div>
                    </td>
                </tr>
            </table>
        </div>  
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

        <table class="table-bordered">
            <thead>
                <tr>
                    <td colspan="3">
                        <center><strong>ARQUEO DE CAJA</strong></center>
                    </td>
                </tr>       
            </thead>
            <tbody>

                <tr>
                    <td colspan="2" style="color: black;">
                        SALDO INICIAL
                    </td>
                    <td class="subject" style="color: black;">
                        ${{ number_format($registro->base,'0',',','.') }}
                    </td>
                </tr>
            <tr>
                    <td colspan="3">
                        <center><strong>MOVIMIENTOS DEL SISTEMA</strong></center>
                    </td>
                </tr>
                <tr class="read">
                    <td class="contact"><b>MOTIVO</b></td>
                    <td class="subject text-center"><b>MOVIMIENTO</b></td>
                    <td class="subject text-right"><b>VALOR</b></td>
                </tr>
            
            @foreach($registro->detalles_mov_entradas as $item)
                <tr class="read">
                    <td class="contact"><b>{{$item->motivo}}</b></td>
                    <td class="subject text-center">{{strtoupper($item->movimiento)}}</td>
                    <td class="subject text-right">${{number_format($item->valor_movimiento,'0',',','.')}}</td>
                </tr>
            @endforeach
            @foreach($registro->detalles_mov_salidas as $item)
                <tr class="read">
                    <td class="contact"><b>{{$item->motivo}}</b></td>
                    <td class="subject text-center">{{strtoupper($item->movimiento)}}</td>
                    <td class="subject text-right">${{number_format($item->valor_movimiento,'0',',','.')}}</td>
                </tr>
            @endforeach
            <tr class="read">
                <td class="contact"><b>Total Entrada de Caja</b></td>
                <td class="subject text-center"></td>
                <td class="subject text-right">
                    ${{number_format($registro->total_mov_entradas,'0',',','.')}}</td>
            </tr>
            <tr class="read">
                <td class="contact"><b>Total Salida de Caja</b></td>
                <td class="subject text-center"></td>
                <td class="subject text-right">
                    ${{number_format($registro->total_mov_salidas,'0',',','.')}}</td>
            </tr>
            <tr class="read">
                <td class="contact"><b>TOTAL ESPERADO</b></td>
                <td class="subject text-center"></td>
                <td class="subject text-right">
                    <b>${{number_format($registro->lbl_total_sistema,'0',',','.')}}</b>
                </td>
            </tr>
            
                <tr>
                    <td colspan="3">
                        <center><strong>CONTEO DE EFECTIVO</strong></center>
                    </td>
                </tr>
                <tr class="read">
                    <th class="contact"><b>EFECTIVO</b></td>
                    <th class="subject"><b>UNIDADES</b></td>
                    <th class="subject"><b>VALOR</b></td>
                </tr> 
            
            @foreach($registro->billetes_contados as $key => $value)
                <tr class="read">
                    <td class="contact"><b>Billetes de ${{number_format($key,'0',',','.')}}</b></td>
                    <td class="subject text-center">{{$value == ""?0:$value}}</td>
                    <td class="subject text-right">
                        ${{number_format($value == ""?0:$key*$value,'0',',','.')}}</td>
                </tr>
            @endforeach
            @foreach($registro->monedas_contadas as $key => $value)
                <tr class="read">
                    <td class="contact"><b>Monedas de ${{number_format($key,'0',',','.')}}</b></td>
                    <td class="subject text-center">{{$value == ""?0:$value}}</td>
                    <td class="subject text-right">
                        ${{number_format($value == ""?0:$key*$value,'0',',','.')}}</td>
                </tr>
            @endforeach
            <tr class="read">
                <td class="contact"><b>Otros Saldos (bonos,pagarés,etc.)</b></td>
                <td class="subject text-center">{{$registro->detalle_otros_saldos}}</td>
                <td class="subject text-right">${{number_format($registro->otros_saldos,'0',',','.')}}</td>
            </tr>
            <tr class="read">
                <td class="contact"><b>Total Billetes</b></td>
                <td class="subject text-center"></td>
                <td class="subject text-right">${{number_format($registro->total_billetes,'0',',','.')}}</td>
            </tr>
            <tr class="read">
                <td class="contact"><b>Total Monedas</b></td>
                <td class="subject text-center"></td>
                <td class="subject text-right">${{number_format($registro->total_monedas,'0',',','.')}}</td>
            </tr>
            <tr class="read">
                <td class="contact"><b>TOTAL EFECTIVO</b></td>
                <td class="subject text-center"></td>
                <td class="subject text-right">
                    <b>${{number_format($registro->lbl_total_efectivo,'0',',','.')}}</b>
                </td>
            </tr>
            <tr class="read">
                <td class="contact"><b>Diferencia</b></td>
                <td class="subject text-center"></td>
                <td class="subject text-right">
                    ${{number_format($registro->total_saldo,'0',',','.')}}</td>
            </tr>
            </tbody>
        </table>

    <br><br>
    {!! generado_por_appsiel() !!}     
</body>
</html>