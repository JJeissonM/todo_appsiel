<!DOCTYPE html>
<html>
<head>
    <title> {{ $pdv->tipo_doc_app->descripcion }} No. </title>

    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
        }

        @page {
          size: 3.15in 38.5in;
          margin: 15px;
        }

        .page-break {
            page-break-after: always;
        }

        .lbl_doc_anulado{
            background-color: rgba(253, 1, 1, 0.33);
            width: 100%;
            top: 300px;
            transform: rotate(-45deg);
            text-align: center;
            font-size: 2em;
        }

        #tabla_productos_facturados,#tabla_totales
        {
            border-collapse: collapse;
        }

        #tabla_productos_facturados tbody tr td
        {
            border: 1px solid gray;
        }

        #tabla_productos_facturados thead tr th
        {
            border: 1px solid gray;
            background-color: #eaeaea;
        }

        #tabla_totales td
        {
            border: 1px solid gray;
        }
    </style>
</head>
<body>
    <?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where( 'id', $empresa->codigo_ciudad )->get()[0];
    ?>
    <table border="0" style="margin-top: 12px !important; font-size: 11px;" width="100%">
        <tr>
            <td width="15%">
                <img src="{{ $url_img }}" width="120px;" />
            </td>
            <td>
                <div style="text-align: center;">
                    <br/>
                    <b>{{ $empresa->descripcion }}</b><br/>
                    <b>{{ config("configuracion.tipo_identificador") }}: @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>

                    {{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} <br/>
                    Teléfono(s): {{ $empresa->telefono1 }}<br/>
                    <b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
                </div>
            </td>
        </tr>
    </table>

    @if( $etiquetas['encabezado'] != '')
        <table style="width: 100%; font-size: 12px;">
            <tr>
                <td style="border: solid 1px #ddd; text-align: center;">
                    <b> {!! $etiquetas['encabezado'] !!} </b> 
                </td>
            </tr>
        </table>
    @endif

    <table border="0" style="margin-top: 12px !important; font-size: 10px;" width="100%">
            <tr>
                <td>
                    <b>{{ $pdv->tipo_doc_app->descripcion }} No.</b> 
                    @if( !is_null( $resolucion ) )
                        {{ $resolucion->prefijo }}
                    @else
                        {{ $pdv->tipo_doc_app->prefijo }}
                    @endif
                    <div class="lbl_consecutivo_doc_encabezado" style="display: inline;"></div>
                </td>
                <td>
                    <b>Fecha:</b> <div id="lbl_fecha" style="display: inline;"></div> / 
                    <b>Hora:</b> <div id="lbl_hora" style="display: inline;"></div>
                </td>
            </tr>

            <tr id="tr_fecha_vencimiento" style="display: none;">
                <td colspan="2">
                    <b>Condición pago:</b> <div class="lbl_condicion_pago" style="display: inline;"></div>
                    <br>
                    <b>Fecha vencimiento:</b> <div class="lbl_fecha_vencimiento" style="display: inline;"></div>
                </td>
            </tr>

    </table>
    
    <div class="lbl_doc_anulado" style="display: none;">
        Documento Anulado
    </div>

    <div style="border: solid 1px #ddd;">
        <table width="100%" style=" font-size: 10px;">
            <tr>
                <td>
                    <b>Cliente:</b> <div class="lbl_cliente_descripcion" style="display: inline;"> {{ $pdv->cliente->tercero->descripcion }} </div> 
                </td>
                <td>
                    <b>{{ config("configuracion.tipo_identificador") }}/CC:</b> <div class="lbl_cliente_nit" style="display: inline;">
                        @if( config("configuracion.tipo_identificador") == 'NIT') 
                        {{ number_format( $pdv->cliente->tercero->numero_identificacion, 0, ',', '.') }}	
                        @else {{ $pdv->cliente->tercero->numero_identificacion}} @endif </div> 
                </td>
            </tr>
            <tr>
                <td>
                    <b>Dirección:</b> <div class="lbl_cliente_direccion" style="display: inline;"> {{ $pdv->cliente->tercero->direccion1 }} </div>
                </td>
                <td>
                    <b>Teléfono:</b> <div class="lbl_cliente_telefono" style="display: inline;"> {{ $pdv->cliente->tercero->telefono1 }} </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <b>Atendido por: &nbsp;&nbsp;</b> <div class="lbl_atendido_por" style="display: inline;"> {{ $pdv->cliente->vendedor->tercero->descripcion }} </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <b>Detalle: &nbsp;&nbsp;</b> <div class="lbl_descripcion_doc_encabezado" style="display: inline;"> </div>
                </td>
            </tr>
        </table>        
    </div>

    <table style="width: 100%; font-size: 10px;" id="tabla_productos_facturados">
        {{ Form::bsTableHeader(['Producto','Cant. (Precio)','IVA','Total']) }}
        <tbody>
        </tbody>
    </table>

    <table style="width: 100%; font-size: 10px;" id="tabla_totales">
        <tbody>
            <tr style="font-weight: bold; background-color: #eaeaea;">
                <td style="text-align: right;"> Total factura: </td>
                <td style="text-align: right;">
                    <div class="lbl_total_factura" style="display: inline; margin-right: 15px;"> </div>
                    <br>
                    <div class="lbl_ajuste_al_peso" style="display: inline; margin-right: 15px; font-size: 9px;"> </div>
                </td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="text-align: right;"> Recibido: </td>
                <td style="text-align: right;">
                    <div class="lbl_total_recibido" style="display: inline; margin-right: 15px;"> </div>
                </td>
            </tr>
            <tr style="font-weight: bold; background-color: #eaeaea;">
                <td style="text-align: right;"> Cambio: </td>
                <td style="text-align: right;">
                    <div class="lbl_total_cambio" style="display: inline; margin-right: 15px;"> </div>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; font-size: 11px;" class="table table-bordered">
        <!-- <thead>
            <tr>
                <th>Tipo producto</th>
                <th>Vlr. Compra</th>
                <th>Base IVA</th>
                <th>Vlr. IVA</th>
            </tr>            
        </thead> -->
        <tbody>
            <tr>
                <td colspan="4">
                    &nbsp;
                </td>
            </tr>
            @if( !is_null($resolucion) ) 
                <tr>
                    <td colspan="4">
                        Factura {{ $resolucion->tipo_solicitud }} por la DIAN. Resolución No. {{ $resolucion->numero_resolucion }} del {{ $resolucion->fecha_expedicion }}. Prefijo {{ $resolucion->prefijo }} desde {{ $resolucion->numero_fact_inicial }} hasta {{ $resolucion->numero_fact_final }}
                    </td>
                </tr>
            @endif
            <tr>
                <td colspan="4">
                    Esta factura se asimila en todos sus efectos a una Letra de Cambio según Art. 774 del Código de Comercio.
                </td>
            </tr>

        </tbody>
    </table>

    <table style="width: 100%; font-size: 11px;">
        <tr>
            <td style="border: solid 1px black;"> <b> Firma del aceptante: </b> <br><br><br><br> </td>
        </tr>
        @if( $etiquetas['pie_pagina'] != '')
            <tr>
                <td style="border: solid 1px #ddd; text-align: center; font-style: italic;">
                    <b> {!! $etiquetas['pie_pagina'] !!} </b>
                </td>
            </tr>
        @endif
    </table>
    
    <br><br>

    <script type="text/javascript">
        window.onkeydown = function( event ) {
            // Si se presiona la tecla q (Quit)
            if ( event.keyCode == 81 )
            {
                window.close();
            }
        };
    </script>

</body>

</html>