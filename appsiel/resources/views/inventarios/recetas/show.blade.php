@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	
    <?php  
        $ingredientes = $registro->ingredientes();

		$ids_items_ingredientes = [];
		foreach ($ingredientes as $una_linea) {
			$ids_items_ingredientes[] = $una_linea['ingrediente']->id;
		}
		$ingredientes_posibles = \App\Inventarios\InvProducto::whereNotIn('id',$ids_items_ingredientes)
											->get();
    
        $vec['']='';
        foreach ($ingredientes_posibles as $opcion){
            $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion;
        }

        $reg_anterior = \App\Inventarios\RecetaCocina::where([
                                                        ['id', '<', $registro->id],
                                                        ['item_platillo_id', '<>', $registro->item_platillo_id]
                                                    ])
                                                    ->max('id');
        
        $reg_siguiente = \App\Inventarios\RecetaCocina::where([
                                                        ['id', '>', $registro->id],
                                                        ['item_platillo_id', '<>', $registro->item_platillo_id]
                                                    ])
                                                    ->min('id');

		$platillos = \App\Inventarios\RecetaCocina::opciones_campo_select();
    ?>

	<div class="row">
		<div class="col-md-4">
			&nbsp;&nbsp;&nbsp;
			<div class="btn-group">
				@if( isset($url_crear) )
					@if($url_crear!='')
						{{ Form::bsBtnCreate($url_crear) }}
					@endif
				@endif
			</div>
		</div>

		<div class="col-md-4">
			&nbsp;&nbsp;&nbsp;
			<div class="btn-group">
				<div style="vertical-align: center;">
					<br/>
					{{ Form::label('item_platillo_id','Producto terminado:') }}
					{{ Form::select('item_platillo_id',$platillos,null,['class'=>'combobox','id'=>'item_platillo_id']) }}
					&nbsp;&nbsp;&nbsp;
					<a class="btn btn-info btn-xs cambiar_platillo"><i class="fa fa-arrow-right"></i> Cambiar </a>
				</div>
			</div>
		</div>

		<div class="col-md-4">
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
			    <div class="container">
					<h3 style="width: 100%;text-align: center;">Producto terminado: "{{ $registro->item_platillo->descripcion }}"</h3>
				</div>
			    <div class="container">
			    	<div class="col-md-4" style="padding:5px;"> 
			    		<b>Unid. Medida: </b> {{ $registro->item_platillo->unidad_medida1 }}
			    	</div>
			    	<div class="col-md-4" style="padding:5px;"> 
			    		<b>Categoría: </b> {{ $registro->item_platillo->grupo_inventario->descripcion }}
			    	</div>
			    	<div class="col-md-4" style="padding:5px; border: 1px solid #ddd;"> 
			    		<?php 
							$fichas = $registro->item_platillo->fichas;
							$title = '';
							if (!empty($fichas->first())) {
								$title = '<p style="width: 100%;text-align:center;"><u>Ficha técnica</u></p>';
							}
						?>
						{!! $title !!}
						@foreach ($fichas as $ficha)
							<b>{{$ficha->key}}: </b> {!! strip_tags($ficha->descripcion) !!}
							<br>
						@endforeach
			    	</div>			    	
			    </div>
			    <hr>

			    <div class="container">
			    	<div class="table-responsive" id="table_content">
						<table class="table table-bordered table-striped" id="myTable">
							<thead>
								<tr>
									<th>ID item insumo</th>
									<th>Insumo (U.M.)</th>
									<th>Cant. x una(1) porción</th>
									<th>Costo unit.</th>
									<th>Costo total</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
                                <?php
									$sum_cantidad_porcion = 0;
                                    $sum_costo_total = 0;
									$cantidad_items = 0;
                                ?>
								@foreach( $ingredientes as $linea)
                                    <?php
										$sum_cantidad_porcion += $linea['cantidad_porcion'];
                                        $sum_costo_total += $linea['cantidad_porcion'] * $linea['ingrediente']->get_costo_promedio(0); 

                                        $string_search_platillo = $registro->item_platillo->descripcion;

										$cantidad_items++;
                                    ?>
                                    <tr>
										<td>{{ $linea['ingrediente']->id }}</td>
										<td>{{ $linea['ingrediente']->descripcion }} ({{ $linea['ingrediente']->unidad_medida1 }})</td>
										<td align="center">
											<div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('inv_cambiar_cantidad_porcion') . "/" . $registro->item_platillo->id . "/" . $linea['ingrediente']->id }}"> {{ number_format( $linea['cantidad_porcion'], 2, ',', '.') }}
											</div>											
										</td>
										<td align="right">${{ number_format( $linea['ingrediente']->get_costo_promedio(0), 2, ',', '.') }}</td>
										<td align="right">${{ number_format( $linea['cantidad_porcion'] * $linea['ingrediente']->get_costo_promedio(0), 2, ',', '.') }}</td>
										<td>
                                            &nbsp;
                                            <a class="btn btn-danger btn-xs btn-detail eliminarElement" data-ingrediente_id="{{ $linea['ingrediente']->id }}" data-item_platillo_id="{{ $registro->item_platillo->id }}" title="Eliminar"><i class="fa fa-trash"></i></a>
										</td>
									</tr>
								@endforeach
                                <tr>
                                    <td> &nbsp; </td>
                                    <td> &nbsp; </td>
                                    <td align="center"> {{ number_format( $sum_cantidad_porcion, 2, ',', '.') }}</td>
                                    <td> &nbsp; </td>
                                    <td align="right"> ${{ number_format( $sum_costo_total, 2, ',', '.') }}</td>
                                    <td> &nbsp; </td>
                                </tr>
                                <!-- 
							--><tr>
									<td> &nbsp; </td>
									<td> &nbsp; </td>
                                    <td> &nbsp; </td>
                                    <td> &nbsp; </td>
                                    <td align="right"> <a class="btn btn-primary btn-xs" href="{{ url('inv_actualizar_costo_promedio_platilllo/' . $registro->item_platillo->id . '/' . $sum_costo_total) }}"><i class="fa fa-save"></i> Actualizar Costo Prom. Producto terminado</a></td>
                                    <td> &nbsp; </td>
                                </tr>
							</tbody>
						</table>
						<div class="well">
							<b>Cantidad de ítems:</b> {{ $cantidad_items}}
						</div>
					</div>
			    </div>

			</div>

			<br/><br/>

			{{ Form::open( ['url'=> 'inv_agregar_ingrediente_a_receta', 'id'=>'form_create', 'files' => true]) }}
				<div class="row">
					<div class="col-md-8 col-md-offset-2" style="vertical-align: center; border: 1px solid gray;">
						<h3>Agregar Insumo</h3>
						<div class="row">
							<div class="col-md-6">
								{{ Form::bsSelect('item_ingrediente_id',null,'Ítem insumo',$vec,['class'=>'combobox']) }}
							</div>
							<div class="col-md-6">
								{{ Form::bsText('cantidad_porcion',null,'Cant. porción',['required'=>'required']) }}
							</div>

							{{ Form::hidden( 'registro_id', $registro->id ) }}
							{{ Form::hidden( 'item_platillo_id', $registro->item_platillo_id, ['id' => 'item_platillo_id'] ) }}

							{{ Form::hidden('url_id',Input::get('id'))}}
							{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
							{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion'))}}
						</div>
						<div align="center">
							<br/>
							<button class="btn btn-primary btn-sm" id="bs_boton_guardar"><i class="fa fa-save"></i> Guardar</button>
						</div>
						<br/><br/>
					</div>
				</div>
			{{ Form::close() }}

		</div>
	</div>

@endsection

@section('scripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( $('#item_ingrediente_id').val() == '' || $('#item_ingrediente_id').val() == 0 ) {
					Swal.fire({
						icon: "error",
						title: "Alerta!",
						text: "Debe ingresar un Insumo.",
					});
					$('.ui-autocomplete-input').focus();					
					return false;
				}

				if ( $('#cantidad_porcion').val() == '' || $('#cantidad_porcion').val() == 0 ) {
					Swal.fire({
						icon: "error",
						title: "Alerta!",
						text: "Debe ingresar una Cantidad.",
					});
					$('#cantidad_porcion').focus();
					return false;
				}

				if ( !$.isNumeric( $('#cantidad_porcion').val() ) ) {
					$('#cantidad_porcion').attr('style','background: #d17676;');
					Swal.fire({
						icon: "error",
						title: "Alerta!",
						text: "Debe ingresar una Cantidad numérica.",
					});
					return false;
				}

				var data = $('#form_create').serializeArray().map(function(x){this[x.name] = x.value; return this;}.bind({}))[0];

				if ( data.item_platillo_id == data.item_ingrediente_id ) {
					Swal.fire({
						icon: "error",
						title: "Alerta!",
						text: "El ítem de Insumo No puede ser igual al Ítem de Proucto terminado.",
					});
					$('.ui-autocomplete-input').focus();
					return false;
				}

				// Desactivar el click del botón
				$( this ).attr( 'disabled', 'disabled' );
				$( this ).off( event );

				$('#form_create').submit();
			});

			$("#cantidad_porcion").on("keyup", function (event) {
				event.preventDefault();

				$('#cantidad_porcion').attr('style','background: white;');

				var codigo_tecla_presionada = event.which || event.keyCode;

				if ( codigo_tecla_presionada == 13 ) {
					$('#bs_boton_guardar').focus();
				}

			});

			$(".eliminarElement").click(function(event){
                event.preventDefault();

				if ( confirm( "¿Realmente Quiere retirar este insumo?" ) == true ) {
                    var url = "{{ url('') }}" + "/" + "inv_eliminar_ingrediente/" + $(this).attr('data-item_platillo_id') + "/" + $(this).attr('data-ingrediente_id') + "?id=8&id_modelo=321";

                	location.href = url;
                }
			    
		    });

			$(".cambiar_platillo").click(function(event){
				event.preventDefault();		

				var url = "{{url('')}}" + "/" + "web/" + $('#item_platillo_id').val() + "?id=8&id_modelo=321&id_transaccion=";
				location.href = url;
			});
			
			var valor_actual, elemento_modificar, elemento_padre;
					
			// Al hacer Doble Click en el elemento a modificar
			$(document).on('dblclick','.elemento_modificar',function(){
				
				elemento_modificar = $(this);

				elemento_padre = elemento_modificar.parent();

				valor_actual = $(this).html();

				elemento_modificar.hide();

				elemento_modificar.after( '<input type="text" name="valor_nuevo" id="valor_nuevo" style="display:inline;"> ');

				document.getElementById('valor_nuevo').value = valor_actual;
				document.getElementById('valor_nuevo').select();

			});

			// Si la caja de texto pierde el foco
			$(document).on('blur','#valor_nuevo',function(){
				guardar_valor_nuevo( $(this) );
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
		        	guardar_valor_nuevo( $(this) );
				}
			});

			function guardar_valor_nuevo( caja_texto )
			{
				if( !validar_input_numerico( $( document.getElementById('valor_nuevo') ) ) )
				{
					return false;
				}

				var valor_nuevo = document.getElementById('valor_nuevo').value;

				// Si no cambió el valor_nuevo, no pasa nada
				if ( valor_nuevo == valor_actual) { return false; }

				$('#div_cargando').show();

				$.ajax({
		        	url: caja_texto.prev().attr('data-url_modificar') + "/" + valor_nuevo,
		        	method: "GET",
		        	success: function( data ){
		        		$('#div_cargando').hide();
				    	
				    	elemento_modificar.html( valor_nuevo );
						elemento_modificar.show();

						elemento_padre.find('#valor_nuevo').remove();

						location.reload();

			        },
			        error: function( data ) {
	                    $('#div_cargando').hide();
						elemento_padre.find('#valor_nuevo').remove();
			        	elemento_modificar.show();
			        	return false;
				    }
			    });
			}
		});
	</script>
@endsection