@extends('layouts.principal')

	<?php
		$metodo_impresion_pedido = config('ventas.metodo_impresion_pedido_ventas') ?: 'normal';
		if ($metodo_impresion_pedido != 'apm' && (int)config('ventas_pos.imprimir_pedidos_en_cocina')) {
			$metodo_impresion_pedido = 'jspm';
		}
	?>

@section('estilos_2')
	<style>

		div.boton {
		  border: 1px solid #ddd;
		  border-radius: 4px;
		  /*background: linear-gradient(90deg, rgba(110,41,183,1) 0%, rgba(79,138,232,1) 44%, rgba(13,214,159,1) 100%);
		  background-color: #ddd;*/
		  text-align: center;
		  margin: 20px 20px;
		}
		
		thead>tr>th{
			text-align: center;
		}

		thead > tr{
			background-color: unset;
		}

		.card{
			border-radius: 12px 12px 0 0;
			border: 2px solid #ddd;
			margin-bottom: 20px;
			box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
		}

		.card-header{
			text-align: center;
			font-size: 24px;
			padding-top: .8rem;
			padding-bottom: .8rem;
			color: #333 !important;
			border-radius: 10px 10px 0 0;
			margin-bottom: 0;
			margin-top: 0;
		}
	</style>

@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">

            <div class="col">
                <br><br>
                <a class="btn btn-default btn-bg btn-info" href="{{ url('vtas_mesero_listado_pedidos_pendientes?id=13') }}" title="Actualizar"><i class="fa fa-refresh"></i> Actualizar</a>
            </div>

            <div class="container-fluid" id="div_pedidos_pendientes">
                {!! $vista_pedidos !!}
            </div>

            <div class="col">
                <br><br>
                                
                <input type="hidden" id="impresora_cocina_por_defecto" name="impresora_cocina_por_defecto" value="{{ config('ventas_pos.impresora_cocina_por_defecto') }}">
                <input type="hidden" id="metodo_impresion_pedido_ventas" value="{{ $metodo_impresion_pedido }}">
                <input type="hidden" id="apm_ws_url" value="{{ config('ventas.apm_ws_url') }}">
                <input type="hidden" id="apm_printer_id_pedidos_ventas" value="{{ config('ventas.apm_printer_id_pedidos_ventas') }}">
                <input type="hidden" id="apm_devices_config" value="{{ e(json_encode(App\Ventas\ApmDevice::frontConfigByDeviceIds([config('ventas.apm_printer_id_pedidos_ventas')]))) }}">
    
                <input type="hidden" id="tamanio_letra_impresion_items_cocina" name="tamanio_letra_impresion_items_cocina" value="{{ config('ventas_pos.tamanio_letra_impresion_items_cocina') }}">
    
                <input type="hidden" id="lbl_consecutivo_doc_encabezado" value="0">
                <input type="hidden" id="lbl_fecha" value="0">
                <input type="hidden" id="lbl_cliente_descripcion" value="">
                <input type="hidden" id="lbl_descripcion_doc_encabezado" value="">
                <input type="hidden" id="lbl_total_factura" value="0">
                <input type="hidden" id="nombre_vendedor" value="0">
                <input type="hidden" id="core_tipo_transaccion_id" value="42">
                <input type="hidden" id="core_tipo_doc_app_id" value="0">

                <table class="table table-bordered table-striped" id="tabla_lineas_registros" style="display: none;">
                    <tbody>
                    </tbody>
                </table>
    
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    @include('ventas.apm_scripts')
    @if( $metodo_impresion_pedido == 'jspm' )
        <script src="{{ asset('assets/js/ventas_pos/external_print/cptable.js') }}"></script>
        <script src="{{ asset('assets/js/ventas_pos/external_print/cputils.js') }}"></script>
        <script src="{{ asset('assets/js/ventas_pos/external_print/JSESCPOSBuilder.js') }}"></script>
        <script src="{{ asset('assets/js/ventas_pos/external_print/JSPrintManager.js') }}"></script>
        <script src="{{ asset('assets/js/ventas_pos/external_print/jspm_client.js?aux=' . uniqid()) }}"></script>
    @endif
    <script src="{{ asset( 'assets/js/ventas/pedidos/script_to_printer.js?aux=' . uniqid() )}}"></script>
    <script src="{{ asset('assets/js/ventas/pedidos/pendientes_cocina.js?aux=' . uniqid()) }}"></script>
@endsection
