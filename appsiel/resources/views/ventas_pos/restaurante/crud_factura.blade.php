@extends('layouts.principal')

<?php
    use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
    <link rel="stylesheet" href="{{asset('assets/css/ventas_pos/estilos_crud_pos.css')}}">
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
                {{ Form::model($factura, ['url' => [$url_action], 'method' => 'PUT','files' => true,'id' => 'form_create']) }}
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

            <div class="row" style="margin: 5px;">
                <button class="btn btn-danger btn-xs" id="btn_cancelar_pedido" style="display: none;"> Cancelar </button>
            </div>

            {{ VistaController::campos_dos_colummnas($form_create['campos']) }}

            @include('ventas_pos.crud_factura_campos_ocultos')

            <span id="lbl_mesa_seleccionada" style="color: white;">{{$cliente->tercero->descripcion}}</span>
            <span id="lbl_vendedor_mesero" style="color: white;">{{$vendedor->tercero->descripcion}}</span>

            <div id="popup_alerta"></div>         

            {{ Form::close() }}

            <hr>

            <!-- Boton para hacer pruebas -->
            <button onclick="ventana_imprimir();" style="display: none;">Mostrar plantilla</button>

            <div class="container-fluid">
                <div class="row">

                    <div class="container">
                        @include('ventas_pos.crud_factura_btn_revisar_pedidos')
                    </div>

                    <!-- Vista Tactil -->
                    @if($vista_categorias_productos != '')
                        <div class="col-md-12 well">
                            <div class="container-fluid">
                                {!! $vista_categorias_productos !!}
                            </div>
                        </div>
                    @endif

                    <!-- Cinta Filtro Items 
                    @ include('ventas_pos.crud_factura_cinta_filtro_items')-->

                        <!-- NO QUITAR LOS ESPACIOS NI TABULACIONES DESDE AQUI HASTA <INMODIFICABLE> -->
                    <div class="col-md-8"><div class="container-fluid">

    <div class="marco_formulario">
                        {!! str_replace("<tbody>
                
            </tbody>", $lineas_registros, $tabla->dibujar() ) !!}

                        @include('core.componentes.productos_y_cantidades_ingresadas')
                        <br/><br/>
</div></div> <!-- INMODIFICABLE -->
                        
                        <div class="container" style="display:none; color:red; font-size:1.1em;" id="msj_fecha_diferente"> 
                            &nbsp; 
                            <span><i class="fa fa-warning"></i> La fecha de la factura es diferente a la fecha del día.</span>
                            <br><br>
                        </div>

                        @if( Input::get('action') == 'edit' )
                            {!! $vista_medios_recaudo !!}
                        @else
                            @include('tesoreria.incluir.medios_recaudos')
                        @endif

                    </div>

                    <div class="col-md-4 well" style="font-size: 1.2em;">
                        <div class="marco_formulario">
                            @include('ventas_pos.crud_factura.tabs_totales_y_clientes')
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <br/>

    @include('ventas_pos.crud_factura_tabla_oculta_linea_ingreso_default_aux')

    <!-- 
        La ventana contiene la variable $contenido_modal que se envia desde FacturaPosController.
    -->
    @include('components.design.ventana_modal',['titulo'=>'','texto_mensaje'=>''])
    
    <!-- 
       Esta venta modal se usa el resgistro de Otros Ingreso y Gastos.
    -->
    @include('components.design.ventana_modal2',['titulo2'=>'','texto_mensaje2'=>'', 'clase_tamanio' => 'modal-lg'])

    <div id="div_plantilla_factura" style="display: none;">
        {!! $plantilla_factura !!}
    </div>
    
    @include('ventas_pos.formatos_impresion.campos_adicionales_usar_JSPrintManager')

    <div class="container-fluid elemento_fondo" style="left: 0; width: 99%; background: #bce0f1; height: 42px; z-index: 999; border-top-right-radius: 10px; border-top-left-radius: 10px; margin: 0px 10px;">
        @include('ventas_pos.componente_vendedores')
    </div>

@endsection

@section('scripts')

    <script src="{{ asset( 'assets/js/ventas_pos/catalogos.js?aux=' . uniqid() )}}"></script>
    <script src="{{ asset( 'assets/js/ventas_pos/precios.js?aux=' . uniqid() )}}"></script>
    <script src="{{ asset( 'assets/js/ventas_pos/clientes.js?aux=' . uniqid() )}}"></script>
        
    <script src="{{ asset( 'assets/js/ventas_pos/agregar_linea_item.js?aux=' . uniqid() )}}"></script>
    <script src="{{ asset( 'assets/js/ventas_pos/commons.js?aux=' . uniqid() )}}"></script>

    <script src="{{ asset( 'assets/js/apm/client.js?aux=' . uniqid() )}}"></script>
    <script src="{{ asset( 'assets/js/ventas_pos/script_to_printer.js?aux=' . uniqid() )}}"></script>

    <script type="text/javascript" src="{{asset( 'assets/js/ventas/facturas_restaurante.js?aux=' . uniqid() )}}"></script>
    
    <script type="text/javascript" src="{{asset( 'assets/js/ventas_pos/cinta_filtro_items.js?aux=' . uniqid())}}"></script>

    <script type="text/javascript" src="{{asset( 'assets/js/ventas_pos/pedidos/cargar_para_facturar.js?aux=' . uniqid() )}}"></script>

    <script type="text/javascript" src="{{asset( 'assets/js/tesoreria/medios_recaudos.js?aux=' . uniqid())}}"></script>

    <script src="{{ asset( 'assets/js/ventas_pos/restaurante/doble_click.js?aux=' . uniqid() )}}"></script>
    
    <script src="{{ asset( 'assets/js/ventas_pos/componentes/boton_prefactura.js?aux=' . uniqid() )}}"></script>

    @if( (int)config('ventas_pos.manejar_propinas') )
        <script type="text/javascript" src="{{asset( 'assets/js/ventas_pos/propinas.js?aux=' . uniqid())}}"></script>
    @endif

    @if( (int)config('ventas_pos.manejar_datafono') )
        <script type="text/javascript" src="{{asset( 'assets/js/ventas_pos/datafono.js?aux=' . uniqid())}}"></script>
    @endif

    @if ( (int)config('ventas_pos.modulo_fe_activo'))
        <script type="text/javascript" src="{{asset( 'assets/js/ventas_pos/factura_electronica.js?aux=' . uniqid())}}"></script>
    @endif

    <script type="text/javascript" src="{{asset( 'assets/js/ventas/restaurante/manejo_platillos_con_contorno.js?aux=' . uniqid())}}"></script>

    <script type="text/javascript">

        var url_raiz = "{{ url('/') }}";
        hay_productos = {{ $numero_linea - 1 }};
        numero_linea = {{ $numero_linea }};

        // Bajar el Scroll hasta el final de la página
        $("html, body").animate({ scrollTop: ( $(document).height() / 2 - 150 ) + "px" });
        
        var fecha = "{{$fecha}}";
        var fecha_vencimiento = "{{$fecha_vencimiento}}";

        $('#numero_lineas').text( {{ $numero_linea - 1 }} );

        $('#efectivo_recibido').val( {{ $total_efectivo_recibido }} );

        $('#total_efectivo_recibido').val( {{ $total_efectivo_recibido }} );
        $('#lbl_efectivo_recibido').text('$ ' + "{{ $total_efectivo_recibido }}");

        $('#total_valor_total').text('$ ' + "{{ $total_efectivo_recibido }}");

    </script>
@endsection
