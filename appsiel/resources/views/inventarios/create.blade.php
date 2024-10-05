@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">

		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>

		    @if( $form_create['modo'] == 'create' )
				{{ Form::open(['url' => $form_create['url'],'id'=>'form_create','files' => true]) }}
			@else
				{{ Form::model($registro_id, ['url' => $form_create['url'] , 'id'=>'form_create', 'method' => 'PUT','files' => true]) }}
			@endif

				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
				{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion'))}}
				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux'])}}

        		{{ Form::hidden('hay_productos_aux', $cantidad_filas,['id'=>'hay_productos_aux']) }}

        		<input id="permitir_inventarios_negativos" name="permitir_inventarios_negativos" type="hidden" value="{{ config('ventas.permitir_inventarios_negativos') }}">

        		<input id="descripcion_tercero" name="descripcion_tercero" type="hidden" value="{{ $descripcion_tercero }}">

				@if( !is_null( Input::get('ruta_redirect') ))
					{{ Form::bsHidden( 'ruta_redirect', Input::get('ruta_redirect') ) }}
				@endif

				@if( !is_null( Input::get('registro_id') ))
					{{ Form::bsHidden( 'registro_id', Input::get('registro_id') ) }}
				@endif

				@if( !is_null( Input::get('doc_ventas_id') ))
					{{ Form::bsHidden( 'doc_ventas_id', Input::get('doc_ventas_id') ) }}
				@endif

				@if( isset( $hay_existencias_negativas ) )
					{{ Form::bsHidden( 'hay_existencias_negativas', $hay_existencias_negativas ) }}
				@else
					{{ Form::bsHidden( 'hay_existencias_negativas', 0 ) }}
				@endif

				<div class="alert alert-warning" id="div_hay_existencias_negativas" style="display: none;">
				  <strong>Advertencia!</strong> Los items con filas en rojo no tienen cantidades sufiencientes. No podrá guardar este documento.
				</div>
				
				
			{{ Form::close() }}

			@include('inventarios.create_tabla_productos')

			<!-- Modal -->
			@include('inventarios.incluir.ingreso_productos_2')
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script src="{{ asset( 'assets/js/inventarios/commons.js?aux=' . uniqid() ) }}"></script>
	<script src="{{ asset( 'assets/js/modificar_con_doble_click_sin_recargar.js' ) }}"></script>

	<script type="text/javascript">	

		if( direccion.search("edit") >= 0 )
		{
			$('#core_tercero_id').val( $('#descripcion_tercero').val() );
			$('#core_tercero_id').attr( 'title', $('#descripcion_tercero').val() );
		}

		function ejecutar_funcion_guardar_nuevo_valor_doble_click( campo_modificado, nuevo_valor )
	    {			
	    	recalcular_totales();
	    }

		function recalcular_totales()
		{
			var sum = 0.0;
			$('.cantidad').each(function()
			{
			    var cantidad = $(this).text();
				// Se elimina la cadena "UND" del texto de la cantidad
				var pos_espacio = cantidad.search(" ");
				cantidad = cantidad.substring(0,pos_espacio);
			    sum += parseFloat(cantidad);

			    var fila = $(this).closest("tr");
			    var costo_unitario_text = fila.find('td.costo_unitario').text();
			    var costo_unitario = parseFloat( costo_unitario_text.substring(1) );
			    fila.find('td.costo_total').text( '$' + cantidad * costo_unitario );
			});
			var texto = sum.toFixed(2);
			$('#total_cantidad').text(texto);

			sum = 0.0;
			$('.costo_total,.costo_total2').each(function()
			{
			    var cadena = $(this).text();
			    sum += parseFloat(cadena.substring(1));
			});

			$('#total_costo_total').text("$"+sum.toFixed(2));
		}
	</script>
@endsection