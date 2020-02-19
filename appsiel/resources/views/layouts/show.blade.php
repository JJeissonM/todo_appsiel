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

	@include('layouts.form_show',compact('form_create','url_edit','tabla'))

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

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
				if ( valor_nuevo == valor_actual) { return false; }

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