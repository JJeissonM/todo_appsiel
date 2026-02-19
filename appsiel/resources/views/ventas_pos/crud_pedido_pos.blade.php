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

/*        @media only screen and (min-width: 993px) { */
            .elemento_fondo {
                position: fixed;
                z-index: 9999;
                bottom: 0;
                margin-bottom: 0;
                float: left;
            }
        /*}*/

        .vendedor_activo{
            background-color: #574696;
            color: white;
        }

        .componente_vendedores{
            padding-top: 4px;
        };

        
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

        .icono_item{    
        background-color: #ddd;
        border: 0px;
        margin: 4px;
        height: 100px;
        width: 150px;
        float: left;
        }
    </style>
@endsection

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    <hr>

    @include('layouts.mensajes')

    <div class="container-fluid">

        @include('ventas_pos.pedidos.crud_botones_accion')

        <br>

        <div class="marco_formulario">

            <h4>Nuevo Pedido</h4>
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

            @include('ventas_pos.crud_factura_campos_ocultos')         

            <div class="container-fluid">
                <div class="row well">
                    <div class="col-md-6">
                        {{ Form::bsText( 'cliente_descripcion_aux', $cliente->tercero->descripcion, 'Cliente', ['id'=>'cliente_descripcion_aux', 'required'=>'required', 'class'=>'form-control'] ) }}
                        {{ Form::bsText( 'direccion1', $cliente->tercero->direccion1, 'Dirección de entrega', ['id'=>'direccion1', 'required'=>'required', 'class'=>'form-control'] ) }}
                    </div>
                    <div class="col-md-6">
                        {{ Form::bsText( 'numero_identificacion', $cliente->tercero->numero_identificacion, config("configuracion.tipo_identificador").'/CC', ['id'=>'numero_identificacion', 'required'=>'required', 'class'=>'form-control'] ) }}
                        {{ Form::bsText( 'telefono1', $cliente->tercero->telefono1, 'Teléfono', ['id'=>'telefono1', 'required'=>'required', 'class'=>'form-control'] ) }}
                    </div>
                </div>
            </div>

            <div id="popup_alerta"></div>         

            {{ Form::close() }}

            <hr>           
                                
    <button onclick="ventana_imprimir();" style="display: none;">Mostrar plantilla</button>

            <div class="container-fluid">
                <div class="row">

                    <!-- Vista Tactil -->
                    @if($vista_categorias_productos != '')
                        @include('ventas_pos.componentes.tactil.tabs_inv_grupos')
                    @endif

                    <!-- Cinta Filtro Items -->
                    @if( !(int)config('ventas_pos.ocultar_cinta_de_busqueda_items') )
                        @include('ventas_pos.crud_factura_cinta_filtro_items')
                    @endif

                    <h4 class="center" style="color: #574696">Registro de pedidos</h4>
                    
                        <!-- NO QUITAR LOS ESPACIOS NI TABULACIONES DESDE AQUI HASTA <INMODIFICABLE> -->
                    <div class="col-md-8 well"><div class="container-fluid">

    <div class="marco_formulario">
                        {!! str_replace("<tbody>
                
            </tbody>", $lineas_registros, $tabla->dibujar() ) !!}

                        @include('core.componentes.productos_y_cantidades_ingresadas')
                        <br/><br/>
</div></div> <!-- INMODIFICABLE -->
                    </div>

                    <div class="col-md-4 well" style="font-size: 1.2em;">
                        <div class="container-fluid">
                            <div class="marco_formulario">                       
                                @include('ventas_pos.pedidos.resumen_totales')
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <br>
        </div>
    </div>
    <br/>

    @include('ventas_pos.crud_factura_tabla_oculta_linea_ingreso_default_aux')

    <!-- La ventana contiene la variable contenido_modal hacer un @ incl para que funcione-->
    @include('components.design.ventana_modal',['titulo'=>'','texto_mensaje'=>'']) <!-- -->

    @include('components.design.ventana_modal2',['titulo2'=>'','texto_mensaje2'=>'', 'clase_tamanio' => 'modal-lg'])

    <div id="div_plantilla_factura" style="display: none;">
        {!! $plantilla_pedido !!}
    </div>
    
    @include('ventas_pos.formatos_impresion.campos_adicionales_usar_JSPrintManager')

    <div class="container-fluid elemento_fondo" style="left: 0; width: 99%; background: #bce0f1; height: 42px; z-index: 999; border-top-right-radius: 10px; border-top-left-radius: 10px; margin: 0px 10px;">
        <div class="btn-group pull-left">
            <label class="control-label col-sm-3 col-md-3" for="cliente_input">Cliente:</label>              
            <div class="col-sm-9 col-md-9">
                <input class="form-control" id="cliente_input" autocomplete="off" required="required" name="cliente_input" type="text" value="{{ $cliente->tercero->descripcion }}"><div id="clientes_suggestions"> </div>
            </div>
        </div>
        @include('ventas_pos.componente_vendedores')
    </div>

@endsection

@section('scripts')

    <script src="{{ asset( 'assets/js/ventas_pos/catalogos.js?aux=' . uniqid() )}}"></script>
    <script src="{{ asset( 'assets/js/ventas_pos/precios.js?aux=' . uniqid() )}}"></script>
    
    <script src="{{ asset( 'assets/js/ventas_pos/agregar_linea_item.js?aux=' . uniqid() )}}"></script>

    <script src="{{ asset( 'assets/js/ventas_pos/pedidos/commons.js?aux=' . uniqid() )}}"></script>

    <script src="{{ asset( 'assets/js/apm/client.js?aux=' . uniqid() )}}"></script>
    <script src="{{ asset( 'assets/js/ventas_pos/pedidos/script_to_printer.js?aux=' . uniqid() )}}"></script>

    <script type="text/javascript" src="{{asset( 'assets/js/ventas_pos/pedidos/facturas.js?aux=' . uniqid() )}}"></script>

    <script type="text/javascript" src="{{asset( 'assets/js/ventas_pos/cinta_filtro_items.js?aux=' . uniqid())}}"></script>

    <script type="text/javascript" src="{{asset( 'assets/js/ventas_pos/componentes/tactil.js?aux=' . uniqid())}}"></script>
    
    <script type="text/javascript">
        
        var url_raiz = "{{ url('/') }}";
        hay_productos = {{ $numero_linea - 1 }};
        numero_linea = {{ $numero_linea }};
        
        var fecha = "{{$fecha}}";
        var fecha_vencimiento = "{{$fecha_vencimiento}}";

        $('#efectivo_recibido').val( {{ $total_efectivo_recibido }} );

        $('#total_efectivo_recibido').val( {{ $total_efectivo_recibido }} );
        $('#lbl_efectivo_recibido').text('$ ' + "{{ $total_efectivo_recibido }}");

        $('#total_valor_total').text('$ ' + "{{ $total_efectivo_recibido }}");

        set_catalogos( $('#pdv_id').val() );

    </script>
@endsection
