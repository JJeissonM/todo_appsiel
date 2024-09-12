@extends('layouts.principal')

<?php
use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
    <style>
        
        #div_resumen_totales{
            font-size: 0.8em;
        }

        #existencia_actual, #tasa_impuesto, #tasa_descuento {
            width: 40px;
        }

        .vendedor_activo{
            background-color: #574696;
            color: white;
        }

        .componente_vendedores{
            padding-top: 4px;
        }

        .mesa_activa{
            background-color: #574696;
            color: white;
        }

        .componente_mesas{
            padding-top: 4px;
        }

        input[type="number"] {
  -webkit-appearance: textfield;
  -moz-appearance: textfield;
  appearance: textfield;
}

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  -webkit-appearance: none;
}

.number-input {
  border: 2px solid #ddd;
  display: inline-flex;
}

.number-input,
.number-input * {
  box-sizing: border-box;
}

.number-input button {
  outline:none;
  -webkit-appearance: none;
  background-color: transparent;
  border: none;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  cursor: pointer;
  margin: 0;
  position: relative;
}

.number-input button:before,
.number-input button:after {
  display: inline-block;
  position: absolute;
  content: '';
  width: 1rem;
  height: 2px;
  background-color: #212121;
  transform: translate(-50%, -50%);
}
.number-input button.plus:after {
  transform: translate(-50%, -50%) rotate(90deg);
}

.number-input input[type=number] {
  font-family: sans-serif;
  max-width: 5rem;
  padding: .5rem;
  border: solid #ddd;
  border-width: 0 2px;
  font-size: 2rem;
  height: 3rem;
  font-weight: bold;
  text-align: center;
}

    </style>
@endsection

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    <hr>

    @include('layouts.mensajes')

    <div class="container-fluid">
        <br>
            <h4>&nbsp;&nbsp;&nbsp;Nuevo Pedido</h4>
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

                <div class="row well" style="display: none;">
                    <div class="col-md-6">
                        {{ Form::bsText( 'cliente_descripcion_aux', $cliente->tercero->descripcion, 'Cliente', ['id'=>'cliente_descripcion_aux', 'required'=>'required', 'class'=>'form-control'] ) }}
                        {{ Form::bsText( 'direccion1', $cliente->tercero->direccion1, 'Dirección de entrega', ['id'=>'direccion1', 'required'=>'required', 'class'=>'form-control'] ) }}
                    </div>
                    <div class="col-md-6">
                        {{ Form::bsText( 'numero_identificacion', $cliente->tercero->numero_identificacion, config("configuracion.tipo_identificador").'/CC', ['id'=>'numero_identificacion', 'required'=>'required', 'class'=>'form-control'] ) }}
                        {{ Form::bsText( 'telefono1', $cliente->tercero->telefono1, 'Teléfono', ['id'=>'telefono1', 'required'=>'required', 'class'=>'form-control'] ) }}
                    </div>
                </div>

                <div id="popup_alerta"></div>

                @include('ventas.pedidos.restaurante.crud_pedido_campos_ocultos')

            {{ Form::close() }}

            <hr>

            <button onclick="ventana_imprimir();" style="display: none;">Mostrar plantilla</button>

            <div class="container-fluid">
                <div class="container">
                    <input type="text" style="width:1px;" id="mitad_focus">
                    
                    <div class="row">
                        @include('ventas.pedidos.restaurante.componente_meseros')
                    </div>
                    
                    <div class="row">
                        @include('ventas.pedidos.restaurante.componente_mesas')
                    </div>
                </div>
                <br>

                <div class="container">
                    <div class="container">
                        <div class="col-md-6" id="div_pedidos_mesero_para_una_mesa">

                        </div>
                        <div class="col-md-6" id="div_cambiar_mesa" style="display: none;">
                            <div style="padding:5px; background-color:aliceblue; border-radius:4px;border:#212121 solid 1px;">
                                <div class="row" style="padding:15px; text-align:center;">
                                    <select id="nueva_mesa_id" class="form-control" name="nueva_mesa_id"></select>
                                    <br>
                                    <button class="btn btn-primary" id="btn_cambiar_mesa" disabled="disabled">
                                        <i class="fa fa-send"></i> Cambiar de mesa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="row">
                    <div class="col-md-6">
                            <!-- NO QUITAR LOS ESPACIOS ENTRE <TBODY> DE STR_REPLACE -->
                            {!! str_replace("<tbody>
                    
                </tbody>", $lineas_registros, $tabla->dibujar() ) !!}
                                    
                            @include('ventas.pedidos.restaurante.crud_pedido_resumen_totales')
                        
                    </div>

                    <div class="col-md-6" style="font-size: 1.2em;">
                        
                        {!! $vista_categorias_productos !!}
                        
                    </div>

                </div>
            </div>

            <br>
            
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
                <button id="btn_listar_items" style="border: 0; background: transparent; display:none;"><i
                            class="fa fa-btn fa-search"></i></button>
            </td>
            <td>
                {{ Form::text( 'inv_producto_id', null, [ 'class' => 'form-control', 'id' => 'inv_producto_id', 'autocomplete' => 'off' ] ) }}
            </td>
            <td>
                <input class="form-control" id="cantidad" width="30px" name="cantidad" type="number" autocomplete="off" min="1">
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
    
    <input type="text" id="imprimir_pedidos_en_cocina" value="{{ (int)config('ventas_pos.imprimir_pedidos_en_cocina') }}">
    
@endsection

@section('scripts')

    <script src="{{ asset( 'assets/js/ventas/pedidos_restaurante.js?aux=' . uniqid() )}}"></script>

    <script type="text/javascript" src="{{asset( 'assets/js/ventas/facturas_restaurante.js?aux=' . uniqid() )}}"></script>
    
    <script src="{{ asset( 'assets/js/ventas/pedidos_restaurante_ventanas_modales.js?aux=' . uniqid() )}}"></script>    

    @if ( (int)config('inventarios.manejar_platillos_con_contorno'))
        <script type="text/javascript" src="{{asset( 'assets/js/ventas/restaurante/manejo_platillos_con_contorno.js?aux=' . uniqid())}}"></script>
    @endif

    <script type="text/javascript">
        
        $("#mitad_focus").focus();
        $("#linea_ingreso_default_aux").hide();

        $("#btn_cancelar").hide();
        $("#core_empresa_id_lbl").parent().parent().parent().parent().hide();
        $("#core_tipo_doc_app_id").parent().parent().parent().parent().hide();
        $("#fecha").parent().parent().parent().parent().hide();
        $("#contacto_cliente_id").parent().parent().parent().parent().hide();
        $("#descripcion").parent().parent().parent().parent().css('border','solid 2px gray');

        $("#div_ingreso_registros").find('h5').html('Ingreso de productos<br><span style="color:red;">NUEVO PEDIDO</span>');
        $("#div_ingreso_registros").find('h5').css('background-color','#50B794');
        $("#div_ingreso_registros").find('h5').css('text-align','center');

        
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