@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	

	<div class="row">
		<div class="col-md-6">
			&nbsp;&nbsp;&nbsp;
			<div class="btn-group">
				@if( isset($url_crear) )
					@if($url_crear!='')
						{{ Form::bsBtnCreate($url_crear) }}
					@endif
				@endif

				@if( isset($url_edit) )
					@if($url_edit!='')
						{{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
					@endif
				@endif
				@if(isset($botones))
					@php
						$i=0;
					@endphp
					@foreach($botones as $boton)
						{!! str_replace( 'id_fila', $registro->id, $boton->dibujar() ) !!}
						@php
							$i++;
						@endphp
					@endforeach
				@endif
			</div>
		</div>

		<div class="col-md-6">
			<div class="btn-group pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev('web/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext('web/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
				@endif
			</div>
		</div>
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <div class="container">
			    <h3 style="width: 100%;text-align: center;">Detalle lista de precios "{{ $form_create['campos'][0]['value'] }}"</h3>
			    <div class="row">
			    	<div class="col-md-4" style="padding:5px;"> 
			    		<b> ID: </b> {{$registro->id}} 
			    	</div>
			    	<div class="col-md-4" style="padding:5px;"> 
			    		<b> Estado: </b> {{ $form_create['campos'][2]['value'] }} 
			    	</div>
			    	<div class="col-md-4" style="padding:5px;"> 
			    		<button class="btn btn-primary btn-sm btn_agregar_precio" type="button"><i class="fa fa-plus"></i> Agregar precio de producto </button> 
			    	</div>			    	
			    </div>
			    <div class="row">
			    	<div class="col-md-12">
			    		<b> Nota: solo se muestran los precios de la última fecha de activación. Para ver los precios de todas las fechas haga click <a href="{{ url( 'web?id=13&id_modelo=129&id_transaccion=' ) }}" title="Modificar" target="_blank"> aquí </a>. </b>
			    	</div>
			    </div>
			    <hr>


			    <?php
			    	$precios = App\Ventas\ListaPrecioDetalle::get_precios_productos_de_la_lista( $registro->id );
			    ?>

			    <div class="row">
			    	<div class="table-responsive" id="table_content">
						<table class="table table-bordered table-striped" id="myTable">
							<thead>
								<tr>
									<th>Cód.</th>
									<th>Ref.</th>
									<th>Producto (U.M.)</th>
									<th>Fecha activación</th>
									<th>Precio</th>
									<th>Tipo</th>
									<th>IVA</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								@foreach( $precios as $linea)
									<tr>
										<td>{{ $linea->producto_codigo }}</td>
										<td>{{ $linea->referencia }}</td>
										<td>{{ $linea->producto_descripcion }} ({{ $linea->item->get_unidad_medida1() }})</td>
										<td>{{ $linea->fecha_activacion }}</td>
										<td align="right">${{ number_format( $linea->precio, 1, ',', '.') }}</td>
										<td>{{ $linea->tipo }}</td>
										<td>{{ $linea->tasa_impuesto }}%</td>
										<td>
											<a class="btn btn-warning btn-xs btn-detail" href="{{ url( 'web/'.$linea->id.'/edit?id=13&id_modelo=129&id_transaccion=' ) }}" title="Modificar" target="_blank"><i class="fa fa-btn fa-edit"></i>&nbsp;</a>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
			    </div>
				<br>
				<button class="btn btn-primary btn-sm btn_agregar_precio" type="button"><i class="fa fa-plus"></i> Agregar precio de producto </button>

			</div>

			<br/><br/>

		</div>
	</div>

	<?php 

		$contenido_modal = Form::open(['url'=>'vtas_agregar_precio_lista','id'=>'form_create','files' => true]).Form::bsInputListaSugerencias( 'inv_producto_id', [null,''], 'Producto', ['required'=>'required','class'=>'form-control text_input_sugerencias','data-url_busqueda'=>url('inv_consultar_productos_v2'),'data-clase_modelo'=>'App\\Inventarios\\InvProducto'] ).'<br><br><br>'.Form::bsFecha('fecha_activacion',null,'Fecha activación',[],['required'=>'required']).'<br><br>'.Form::bsText('precio',null, 'Precio', ['required'=>'required'] ).Form::hidden('lista_precios_id', $registro->id).Form::hidden('url_id',Input::get('id')).Form::hidden('url_id_modelo', Input::get('id_modelo')).Form::hidden('url_id_transaccion', Input::get('id_transaccion')).'<br><br><br>'.Form::close();

	?>

	@include( 'components.design.ventana_modal',['titulo'=>'Agregar precio de producto','texto_mensaje'=>'','contenido_modal' => $contenido_modal] )

@endsection

@section('scripts')
	<script type="text/javascript">

		function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
        {
			$('#lista_sugerencias').remove();
        	$('#fecha_activacion').focus();
        }

		$(document).ready(function(){

			$(".btn_agregar_precio").click(function(event){
		        $("#myModal").modal({backdrop: "static"});
		        $('#inv_producto_id').focus();
		        $(".btn_edit_modal").hide();
		    });


			$('#inv_producto_id').on('change',function(){
				if ( $(this).val() != '' )
				{
					$("#fecha_activacion").val( get_fecha_hoy() );
					$("#fecha_activacion").focus();
				}
			});

			$('#fecha_activacion').on('keyup',function(event){
				event.preventDefault();

		    	var codigo_tecla_presionada = event.which || event.keyCode;

		    	if ( codigo_tecla_presionada == 13 )
		    	{
		    		$("#precio").focus();
		    	}
			});

			$('#precio').on('keyup',function(event){
				event.preventDefault();

		    	var codigo_tecla_presionada = event.which || event.keyCode;

		    	if ( codigo_tecla_presionada == 13 && validar_input_numerico( $(this) ) )
		    	{
		    		$(".btn_save_modal").focus();
		    	}
			});

			$('.btn_save_modal').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() && !validar_input_numerico( $(this) ) )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});


			var valor_actual, elemento_modificar, elemento_padre;
			
			// Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
			$('.elemento_modificar').on('dblclick',function(){
				
				elemento_modificar = $(this);

				elemento_padre = elemento_modificar.parent();

				valor_actual = $(this).html();

				elemento_modificar.hide();

				elemento_modificar.after( '<input type="text" name="valor_nuevo" id="valor_nuevo" class="form-control input-sm"> ');

				document.getElementById('valor_nuevo').value = valor_actual;
				document.getElementById('valor_nuevo').select();

			});

			// Si la caja de texto pierde el foco
			$(document).on('blur','#valor_nuevo',function(){
				guardar_valor_nuevo();
			});

			// Al presiona teclas en la caja de texto
			$(document).on('keyup','#valor_nuevo',function(){

				var x = event.which || event.keyCode; // Capturar la tecla presionada

				// Abortar la edición
				if( x == 27 ) // 27 = ESC
				{
					elemento_padre.find('#valor_nuevo').remove();
		        	elemento_modificar.show();
		        	return false;
				}

				// Guardar
				if( x == 13 ) // 13 = ENTER
				{
		        	guardar_valor_nuevo();
				}
			});

			function guardar_valor_nuevo()
			{
				var valor_nuevo = document.getElementById('valor_nuevo').value;

				// Si no cambió el valor_nuevo, no pasa nada
				if ( valor_nuevo == valor_actual)
				{
					elemento_padre.find('#valor_nuevo').remove();
					elemento_modificar.show();
					return false;
				}

				// almacenar el valor_nuevo
				$.get( '../actualizar_campos_modelos_relacionados', { modelo_id: elemento_modificar.attr('data-modelo_id'), registro_modelo_padre_id: elemento_modificar.attr('data-registro_modelo_padre_id'), registro_modelo_hijo_id: elemento_modificar.attr('data-registro_modelo_hijo_id'), valor_nuevo: valor_nuevo } )
					.done(function( data ) {
						
						elemento_modificar.html( valor_nuevo );
						elemento_modificar.show();

						elemento_padre.find('#valor_nuevo').remove();
					});
			}
		});
	</script>
@endsection