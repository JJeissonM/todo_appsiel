@extends('layouts.principal')

<?php
use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
    <style>
        
        #div_resumen_totales{
            font-size: 0.8em;
        }
        
        #suggestions {
            position: absolute;
            z-index: 9999;
        }

        #clientes_suggestions {
            position: absolute;
            z-index: 9999;
            bottom: 20px;
        }

        #existencia_actual, #tasa_impuesto, #tasa_descuento {
            width: 40px;
        }

        #popup_alerta {
            display: none; /**/
            color: #FFFFFF;
            background: red;
            border-radius: 5px;
            position: fixed; /*El div será ubicado con relación a la pantalla*/
            /*left:0px; A la derecha deje un espacio de 0px*/
            right: 10px; /*A la izquierda deje un espacio de 0px*/
            bottom: 10px; /*Abajo deje un espacio de 0px*/
            /*height:50px; alto del div */
            width: 20%;
            z-index: 999999;
            float: right;
            text-align: center;
            padding: 5px;
            opacity: 0.7;
        }


        @media only screen and (min-width: 993px) {
            .elemento_fondo {
                position: fixed;
                z-index: 9999;
                bottom: 0;
                margin-bottom: 0;
                float: left;
            }
        }

        .vendedor_activo{
            background-color: #574696;
            color: white;
        }

        .componente_vendedores{
            padding-top: 4px;
        };

    </style>
@endsection

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    <hr>

    @include('layouts.mensajes')

    <div class="container-fluid">

        @include('ventas_pos.crud_factura_botones_accion')

        <br>

        <div class="marco_formulario">

            <h4>Nuevo registro</h4>
            <hr>

            @if( Input::get('action') == 'edit' )
                {{ Form::model($registro, ['url' => [$url_action], 'method' => 'PUT','files' => true,'id' => 'form_create']) }}
            @else
                {{ Form::open([ 'url' => $form_create['url'], 'id'=>'form_create']) }}
            @endif

            <?php
            if (count($form_create['campos']) > 0) {
                $url = htmlspecialchars($_SERVER['HTTP_REFERER']);
                echo '<div class="row" style="margin: 5px;">' . Form::bsButtonsForm2($url) . '</div>';
            } else {
                echo "<p>El modelo no tiene campos asociados.</p>";
            }
            ?>

            {{ VistaController::campos_dos_colummnas($form_create['campos']) }}

            {{ Form::hidden('url_id',Input::get('id')) }}
            {{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}

            <input type="hidden" name="url_id_transaccion" id="url_id_transaccion"
                   value="{{Input::get('id_transaccion')}}" required="required">

            {{ Form::hidden( 'pdv_id', Input::get('pdv_id'), ['id'=>'pdv_id'] ) }}
            {{ Form::hidden('cajero_id', Auth::user()->id, ['id'=>'cajero_id'] ) }}

            {{ Form::hidden('inv_bodega_id_aux',$pdv->bodega_default_id,['id'=>'inv_bodega_id_aux']) }}

            <input type="hidden" name="cliente_id" id="cliente_id" value="{{$cliente->id}}"
                   required="required">
            <input type="hidden" name="zona_id" id="zona_id" value="{{$cliente->zona_id}}" required="required">
            <input type="hidden" name="clase_cliente_id" id="clase_cliente_id"
                   value="{{$cliente->clase_cliente_id}}" required="required">
            <input type="hidden" name="equipo_ventas_id" id="equipo_ventas_id" value="{{$cliente->vendedor->equipo_ventas_id}}" required="required">

            <input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{$cliente->core_tercero_id}}"
                   required="required">

            <input type="hidden" name="caja_pdv_default_id" id="caja_pdv_default_id" value="{{$pdv->caja_default_id}}">

            <input type="hidden" name="vendedor_id" id="vendedor_id" data-vendedor_descripcion="{{$cliente->vendedor_descripcion}}" value="{{$cliente->vendedor_id}}">

            <input type="hidden" name="cliente_descripcion" id="cliente_descripcion"
                   value="{{$cliente->tercero->descripcion}}" required="required">

            <div class="row well">
                <div class="col-md-6">
                    {{ Form::bsText( 'cliente_descripcion_aux', $pdv->cliente->tercero->descripcion, 'Cliente', ['id'=>'cliente_descripcion_aux', 'required'=>'required', 'class'=>'form-control'] ) }}
                    {{ Form::bsText( 'direccion1', $pdv->cliente->tercero->direccion1, 'Dirección de entrega', ['id'=>'direccion1', 'required'=>'required', 'class'=>'form-control'] ) }}
                </div>
                <div class="col-md-6">
                    {{ Form::bsText( 'numero_identificacion', $pdv->cliente->tercero->numero_identificacion, config("configuracion.tipo_identificador").'/CC', ['id'=>'numero_identificacion', 'required'=>'required', 'class'=>'form-control'] ) }}
                    {{ Form::bsText( 'telefono1', $pdv->cliente->tercero->telefono1, 'Teléfono', ['id'=>'telefono1', 'required'=>'required', 'class'=>'form-control'] ) }}
                </div>
            </div>                    

            <input type="hidden" name="lista_precios_id" id="lista_precios_id"
                   value="{{$cliente->lista_precios_id}}" required="required">
            <input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id"
                   value="{{$cliente->lista_descuentos_id}}" required="required">
            <input type="hidden" name="liquida_impuestos" id="liquida_impuestos"
                   value="{{$cliente->liquida_impuestos}}" required="required">

            <input type="hidden" name="inv_motivo_id" id="inv_motivo_id" value="{{$inv_motivo_id}}">

            <input type="hidden" name="lineas_registros" id="lineas_registros" value="0">
            <input type="hidden" name="lineas_registros_medios_recaudos" id="lineas_registros_medios_recaudos" value="0">

            <input type="hidden" name="estado" id="estado" value="Pendiente">

            <input type="hidden" name="tipo_transaccion" id="tipo_transaccion" value="factura_directa">

            <input type="hidden" name="rm_tipo_transaccion_id" id="rm_tipo_transaccion_id"
                   value="{{config('ventas')['rm_tipo_transaccion_id']}}">
            <input type="hidden" name="dvc_tipo_transaccion_id" id="dvc_tipo_transaccion_id"
                   value="{{config('ventas')['dvc_tipo_transaccion_id']}}">

            <input type="hidden" name="caja_id" id="saldo_original" value="0">

            <input type="hidden" name="valor_total_cambio" id="valor_total_cambio" value="0">
            <input type="hidden" name="total_efectivo_recibido" id="total_efectivo_recibido">

            <input type="hidden" name="pedido_id" id="pedido_id" value="{{$pedido_id}}">

            <input type="hidden" name="action" id="action" value="{{ Input::get('action') }}">

            <div id="popup_alerta"></div>

            {{ Form::close() }}

            <hr>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8 well">
                        <!-- NO QUITAR LOS ESPACIOS ENTRE <TBODY> DE STR_REPLACE -->
                        {!! str_replace("<tbody>
                
            </tbody>", $lineas_registros, $tabla->dibujar() ) !!}

                        Productos ingresados: <span id="numero_lineas"> 0 </span>
                        <br/><br/>

                        @if( Input::get('action') == 'edit' )
                            {!! $vista_medios_recaudo !!}
                        @else
                            @include('tesoreria.incluir.medios_recaudos')
                        @endif

                        {!! $vista_categorias_productos !!}
                    </div>

                    <div class="col-md-4 well" style="font-size: 1.2em;">                        
                        @include('ventas_pos.crud_factura_resumen_totales')
                    </div>

                </div>
            </div>

            <br>

            @if( config('ventas_pos.permite_facturacion_con_archivo_plano') )
                @include('ventas_pos.form_cargue_archivo_plano')
            @endif
        </div>
    </div>
    <br/>
    <table style="display: none;">
        <tr id="linea_ingreso_default_aux">
            <td style="display: none;">
                <div class="inv_producto_id"></div>
            </td>
            <td style="display: none;">
                <div class="precio_unitario"></div>
            </td>
            <td style="display: none;">
                <div class="base_impuesto"></div>
            </td>
            <td style="display: none;">
                <div class="tasa_impuesto"></div>
            </td>
            <td style="display: none;">
                <div class="valor_impuesto"></div>
            </td>
            <td style="display: none;">
                <div class="base_impuesto_total"></div>
            </td>
            <td style="display: none;">
                <div class="cantidad"></div>
            </td>
            <td style="display: none;">
                <div class="precio_total"></div>
            </td>
            <td style="display: none;">
                <div class="tasa_descuento"></div>
            </td>
            <td style="display: none;">
                <div class="valor_total_descuento"></div>
            </td>
            <td>
                <button id="btn_listar_items" style="border: 0; background: transparent;"><i
                            class="fa fa-btn fa-search"></i></button>
            </td>
            <td>
                {{ Form::text( 'inv_producto_id', null, [ 'class' => 'form-control', 'id' => 'inv_producto_id', 'autocomplete' => 'off' ] ) }}
            </td>
            <td>
                <input class="form-control" id="cantidad" width="30px" name="cantidad" type="text" autocomplete="off">
            </td>
            <td>
                <input class="form-control" id="precio_unitario" name="precio_unitario" type="text">
            </td>
            <td>
                <input class="form-control" id="tasa_descuento" width="30px" name="tasa_descuento" type="text">
            </td>
            <td>
                <input class="form-control" id="tasa_impuesto" width="30px" name="tasa_impuesto" type="text">
            </td>
            <td>
                <input class="form-control" id="precio_total" name="precio_total" type="text">
            </td>
            <td></td>
        </tr>
    </table>

    <!-- La ventana contiene la variable contenido_modal hacer un @ incl para que funcione-->
    @include('components.design.ventana_modal',['titulo'=>'','texto_mensaje'=>'']) <!-- -->


    @include('components.design.ventana_modal2',['titulo2'=>'','texto_mensaje2'=>'', 'clase_tamanio' => 'modal-lg'])

    <div id="div_plantilla_factura" style="display: none;">
        {!! $plantilla_factura !!}
    </div>

    <div class="container-fluid elemento_fondo" style="left: 0; width: 99%; background: #bce0f1; height: 42px; z-index: 999; border-top-right-radius: 10px; border-top-left-radius: 10px; margin: 0px 10px;">
        @include('ventas_pos.componente_vendedores')
    </div>

@endsection

@section('scripts')

    <script src="{{ asset( 'assets/js/ventas_pos/commons.js' ) }}"></script>

    <script type="text/javascript" src="{{asset('assets/js/ventas_pos/facturas.js')}}"></script>

    <script type="text/javascript" src="{{asset('assets/js/tesoreria/medios_recaudos.js')}}"></script>
    
    <script type="text/javascript">
        
        var url_raiz = "{{ url('/') }}";
        hay_productos = {{ $numero_linea - 1 }};
        numero_linea = {{ $numero_linea }};

        $('#efectivo_recibido').val( {{ $total_efectivo_recibido }} );

        $('#total_efectivo_recibido').val( {{ $total_efectivo_recibido }} );
        $('#lbl_efectivo_recibido').text('$ ' + "{{ $total_efectivo_recibido }}");

        $('#total_valor_total').text('$ ' + "{{ $total_efectivo_recibido }}");

        $.fn.set_catalogos( $('#pdv_id').val() );

    </script>
@endsection