@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<div style="padding-left: 10px;">
		<!-- { { Form::formEliminar( '/actividades_escolares/eliminar_actividad', $actividad->id ) }}
		-->
		<button class="btn-gmail" id="btn_anular" title="Eliminar"><i class="fa fa-btn fa-trash"></i></button>

		<a href="{{ url('actividades_escolares/'.$actividad->id.'/edit?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion=0') }}" class="btn-gmail" title="Modificar"><i class="fa fa-btn fa-edit"></i></a>

		@can('acdo_cambiar_estado_actividades_escolares')
			<a class="btn-gmail" href="{{ url('a_i') . '/' . $actividad->id . '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion=' }}" title="Activar/Inactivar">A/I</a>
		@endcan

	</div>


	{{ Form::open([ 'url' => '/actividades_escolares/eliminar_actividad?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'id'=>'form_anular']) }}
		<div class="alert alert-warning" style="display: none;">
			<a href="#" id="close" class="close">&times;</a>
			<strong>Advertencia!</strong>
			<br>
			Al eliminar la actividad, se eliminan todas las respuestas ingresadas por los estudiantes.
			<br>
			Si realmente quiere eliminar la actividad, haga click en el siguiente enlace: <small> <button style="background: transparent; border: 0px;"> Anular </button> </small>
		</div>

		{{ Form::hidden( 'recurso_a_eliminar_id', $actividad->id ) }}

	{{ Form::close() }}

	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">

			@if( $actividad->id == 0)
				<?php
					dd( 'La actividad ha sido eliminada o está inactiva. Por favor, consulte con el administrador. ID actividad = ' . $actividad_id );
				?>
			@endif
			
		    <h4>{{$actividad->descripcion}}</h4>
		    <hr>

		    	<?php 

		    		$color = 'red';
	        		if ( $actividad->estado == 'Activo' )
	        		{
	        			$color = 'green';
	        		}

		    	?>
		    	
				<h5><b>Estado: </b> <i class="fa fa-circle" style="color: {{$color}}"> </i> {{ $actividad->estado }} </h5>
				<h5><b>Asignatura: </b> {{ $asignatura->descripcion }}</h5>
				<h5><b>Temática: </b> {{$actividad->tematica}}</h5>
				
				<div style="border: solid 1px; border-bottom: solid 2px; border-right: solid 2px; border-radius: 5px; padding: 10px; margin: 10px;">
					<h4><b>Instrucciones: </b> </h4>
					<hr>
					{!! $actividad->instrucciones !!}
				</div>

				<br>
				<p><b>Fecha de entrega: </b> {{$actividad->fecha_entrega}}</p>
				<?php
					$mostrar = '';
					switch ($actividad->tipo_recurso) {
						case 'Imagen':
							$mostrar = '<p><b>Recurso: </b> Imágen</p>
										<div class="row">
											<div class="col-md-10 col-md-offset-1">
												<img width="100%" height="550px" src="'.$actividad->url_recurso.'">
											</div>
										</div>';
							break;
						
						case 'Video':
							$mostrar = '<p><b>Recurso: </b> Video</p>
										<div class="row">
											<div class="col-md-10 col-md-offset-1">
												<iframe width="100%" height="380" src="'.str_replace('watch?v=','embed/',explode('&', $actividad->url_recurso)[0]).'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
											</div>
										</div>';
							break;
						
						case 'Adjunto':
							$mostrar = '<p><b>Recurso: </b> Archivo adjunto</p>
										<div class="row">
											<div class="col-md-10 col-md-offset-1">
												<a href="'.config('configuracion.url_instancia_cliente')."/storage/app/".$modelo->ruta_storage_archivo_adjunto.$actividad->archivo_adjunto.'" class="btn btn-info btn-md" target="_blank"> <i class="fa fa-file"></i> '.$actividad->archivo_adjunto.' </a>
											</div>
										</div>';
							break;
						
						default:
							# code...
							break;
					}

					echo $mostrar;

					
				?>
				
				<br>

				@if( isset($cuestionario->id) )
					<h4><b>Cuestionario</b></h4>
					<hr>
					<p> {!! $cuestionario->detalle !!} </p>

					<ul class="nav nav-tabs">
					  <li class="active"><a data-toggle="tab" href="#home">Preguntas</a></li>
					  <li><a data-toggle="tab" href="#menu1">Resultados</a></li>
					</ul>

					<div class="tab-content">

					  <div id="home" class="tab-pane fade in active">
					  	<br>
					  	<h3> De esta forma los estudiantes visualizarán las preguntas: </h3>
					    @include('calificaciones.actividades_escolares.preguntas_respuestas')
					  </div>

					  <div id="menu1" class="tab-pane fade">
					  	<br>
					    @include('calificaciones.actividades_escolares.profesor_resultados_cuestionario')
					  </div>

					</div>

					<input type="hidden" name="cuestionario_id" id="cuestionario_id" value="{{ $cuestionario->id }}">
				@else
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-12">

								<h4> Respuestas ó anotaciones de estudiantes </h4>
								@include('calificaciones.actividades_escolares.profesor_respuestas_actividad_sin_cuestionario')

							</div>
						</div>
					</div>
						
						
				@endif

		</div>
	</div>
	<br/><br/>

	@include('components.design.ventana_modal',['titulo'=>'Resultados Estudiante','texto_mensaje'=>'Nada'])

@endsection

@section('scripts')
	
	<script type="text/javascript">
		$(document).ready(function(){
			$('.btn_agregar_respuesta').attr('disabled','disabled');
			$('.btn_guardar_respuestas').attr('disabled','disabled');

			$('.btn_ver_respuestas').on('click', function(){

				$('#contenido_modal').html( '' );

				$(".modal-dialog").addClass("modal-lg");

				$("#myModal").modal();

				$("#div_spin").show();
				
				$(".btn_edit_modal").hide();
				$(".btn_save_modal").hide();
				
				var url = '../../actividades_escolares/visualizar_resultados_estudiante/'+$('#cuestionario_id').val()+'/'+$(this).attr('data-estudiante_id');
				var btn = $(this);
				$.get( url, function( respuesta ){
					$("#div_spin").hide();
					$('#contenido_modal').html( respuesta );
					$('.modal-title').html( btn.closest("tr").find('td:first').html() );
				});
			});

			$('.btn_eliminar').on('click',function(event){
				event.preventDefault();
				alert('¡¡¡ ADVERTENCIA !!! Al elminar la actividad se borrarán todas las respuestas ingresadas por los estudiantes para esta actividad.');
				if( confirm("¿REALMENTE Desea eliminar la actividad?") )
				{
					$(this).parent('form').submit();
				}
			});


			var valor_actual, elemento_modificar, elemento_padre;
			
			// Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
			$(document).on('dblclick','.elemento_modificar',function(){

				$('#popup_alerta_success').hide();
				
				elemento_modificar = $(this);

				elemento_padre = elemento_modificar.parent();

				valor_actual = $(this).html();

				elemento_modificar.hide();

				elemento_modificar.after('<textarea name="valor_nuevo" id="valor_nuevo" class="form-control"></textarea>');

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
					quitar_caja_texto_valor_nuevo();
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
					quitar_caja_texto_valor_nuevo();					
					elemento_modificar.show();
					return false;
				}

				elemento_modificar.html( valor_nuevo );
				elemento_modificar.show();

				quitar_caja_texto_valor_nuevo();

				// Se llama al método en ActividadesEscolaresController
				var url = "{{url('almacenar_calificacion_a_respuesta_estudiante')}}";
				// almacenar el valor_nuevo
				$.get( url, { respuesta_id: elemento_modificar.attr('data-respuesta_id'), estudiante_id: elemento_modificar.attr('data-estudiante_id'), actividad_id: elemento_modificar.attr('data-actividad_id'), campo: elemento_modificar.attr('data-campo'), valor_nuevo: valor_nuevo } )
					.done(function( data ) {
						
						elemento_modificar.attr('data-respuesta_id', data);
						if ( data == 0 )
						{
							mostrar_popup( 'Anotación NO puso ser guardada. Por favor, recague la página e intente nuevamente.' );
						}else{
							mostrar_popup( 'Anotación actualizada correctamente.' );
						}
					});
			}


			function quitar_caja_texto_valor_nuevo()
			{
				if ( document.getElementById('valor_nuevo') !== null )
				{
					elemento_padre.find('#valor_nuevo').remove();
				}
			}


			function mostrar_popup( mensaje )
			{
				$('#popup_alerta_success').show();
				$('#popup_alerta_success').text( mensaje );
			}

			$('#btn_anular').on('click',function(e){
				e.preventDefault();
				$('.alert.alert-warning').show(1000);
			});

		});
	</script>
@endsection