<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <style>
        
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ config('ventas_pos.tamanio_fuente_factura') . 'px'  }};
        }

        @page {
            size: 3.15in 38.5in;
            margin: 15px;
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

    #tr_total_propina{
        background-color: #eaeaea;
    }

    #tr_total_datafono{
        background-color: #eaeaea;
    }
    </style>
</head>
    
<body onload="window.print()">
    <?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
        $tamanino_fuente_2 = '0.8em';
    ?>

    <div class="headempresap">
        <table border="0" style="margin-top: 12px !important;" width="100%">
            <tr>
                <td style="text-align: center;">
                    <img src="{{ $url_img }}" width="80px;" />
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    <b>{{ $empresa->descripcion }}</b>

                    @if($empresa->razon_social != '' && $empresa->descripcion != $empresa->razon_social)
                        <br />
                        {{ $empresa->razon_social }}
                    @else
                        @if($empresa->nombre1 != '' && $empresa->apellido1 != '' && $empresa->descripcion != $empresa->razon_social )
                            <br />
                            {{ $empresa->nombre1 }} {{ $empresa->apellido1 }} {{ $empresa->apellido2 }}
                        @endif    
                    @endif
                    <br />
                    <b>{{ config("configuracion.tipo_identificador") }}.
                        @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format(
                        $empresa->numero_identificacion, 0, ',', '.') }} @else {{ $empresa->numero_identificacion}}
                        @endif - {{ $empresa->digito_verificacion }}</b><br />

                    {{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} 
                    <br />
                    Teléfono(s): {{ $empresa->telefono1 }}
                    @if($empresa->pagina_web != '' )
                        <br />
                        <b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b>
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div class="headdocp" style="width: 100%">
        <b style="font-size: 1.2em; text-align: center; display: block;">Cotización Nro. {{ sprintf("%04d",
            $doc_encabezado->documento_transaccion_consecutivo) }}</b>
        <br />
        <?php
            $fecha = date_create($doc_encabezado->fecha);
            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
            $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
        ?>
        <b>Fecha:</b> {{ $fecha_final }}
        <br />
        <b>Contacto:</b> {{ $contacto->descripcion }}
        <br />
        <b>Teléfono:</b> {{ $contacto->telefono1 }}
        <br />
        <b>Mail:</b> {{ $contacto->email }}
        <br />
        <?php
                $fecha = date_create($doc_encabezado->fecha_vencimiento);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
        <b>Valido hasta:</b> {{ $fecha_final }}

        @yield('datos_adicionales_encabezado')

    </div>
    <div class="subhead">
        <b>Detalle: &nbsp;&nbsp;</b>
        <br>
        <p class="info text-indent">
            <?php echo $doc_encabezado->descripcion ?>
        </p>
    </div>

    <table style="width: 100%; font-size: {{ $tamanino_fuente_2 }};" id="tabla_productos_facturados">
        
        {{ Form::bsTableHeader(['Item','Cant.','Vr. unit.','Total']) }}
        
        <tbody>
            <?php 
                $i = 1;
                $total_cantidad = 0;
                $subtotal = 0;
                $total_impuestos = 0;
                $total_descuentos = 0;
                $total_factura = 0;
                $array_tasas = [];

                $impuesto_iva = 0;//iva en firma

                $cantidad_items = 0;

            
            ?>
            @foreach($doc_registros as $linea )
            <tr>
                <td style="width: 40%;"> {{ $linea->item->get_value_to_show(true)}} </td>
                <td style="text-align: right;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) ,
                    0, ',', '.') }}
                </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_total, 0, ',', '.') }} </td>
            </tr>
            <?php
                $i++;
                $total_cantidad += $linea->cantidad;
                $subtotal += (float)($linea->precio_unitario - $linea->valor_impuesto) * (float)$linea->cantidad;
                $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                $total_factura += $linea->precio_total;
                $total_descuentos += $linea->valor_total_descuento;

                // Si la tasa no está en el array, se agregan sus valores por primera vez
                if ( !isset( $array_tasas[$linea->tasa_impuesto] ) )
                {
                    // Clasificar el impuesto
                    $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA '.$linea->tasa_impuesto.'%';
                    if ( $linea->tasa_impuesto == 0)
                    {
                        $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA 0%';
                    }
                    // Guardar la tasa en el array
                    $array_tasas[$linea->tasa_impuesto]['tasa'] = $linea->tasa_impuesto;


                    // Guardar el primer valor del impuesto y base en el array
                    $array_tasas[$linea->tasa_impuesto]['precio_total'] = (float)$linea->precio_total;
                    $array_tasas[$linea->tasa_impuesto]['base_impuesto'] = (float)$linea->base_impuesto * (float)$linea->cantidad;
                    $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] = (float)$linea->valor_impuesto * (float)$linea->cantidad;

                }else{
                    // Si ya está la tasa creada en el array
                    // Acumular los siguientes valores del valor base y valor de impuesto según el tipo
                    $precio_total_antes = $array_tasas[$linea->tasa_impuesto]['precio_total'];
                    $array_tasas[$linea->tasa_impuesto]['precio_total'] = $precio_total_antes + (float)$linea->precio_total;
                    $array_tasas[$linea->tasa_impuesto]['base_impuesto'] += (float)$linea->base_impuesto * (float)$linea->cantidad;
                    $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                }
                $cantidad_items++;

                if($linea->valor_impuesto > 0){
                    $impuesto_iva = $linea->tasa_impuesto;
                }
            ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td>&nbsp;</td>
                <td style="text-align: right;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td colspan="4">&nbsp;</td>
            </tr>
        </tfoot>
    </table>

    @include('ventas.incluir.factura_firma_totales2')

    <table class="info">
        <tr>
            <td width="25%"><b>Cotizó:</b></td>
            <td>{{ $doc_encabezado->vendedor->tercero->descripcion }}</td>
        </tr>

        @if( !is_null( $doc_encabezado->plazo_entrega ) )
            <tr>
                <td width="30%"><b>Plazo de entrega:</b></td>
                <td>
                    {{ $doc_encabezado->plazo_entrega->valor }}
                </td>
            </tr>
        @endif
    </table>
    
    <div class="text-indent">
        @if( !is_null( $otroscampos ) )
        {!! $otroscampos->terminos_y_condiciones !!}
        @endif


        <br><br>
        {!! generado_por_appsiel() !!}

        

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