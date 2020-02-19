@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<div style="padding-left: 10px;">{{ Form::formEliminar( '/actividades_escolares/eliminar_actividad', $actividad->id ) }}</div>
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>{{$actividad->descripcion}}</h4>
		    <hr>
				<h5><b>Temática: </b> {{$actividad->tematica}}</h5>
				
				<div style="border: solid 1px; border-bottom: solid 2px; border-right: solid 2px; border-radius: 5px; padding: 10px;">
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
												<a href="'.config('configuracion.url_instancia_cliente')."/storage/app/".$modelo->ruta_storage_archivo_adjunto.$actividad->archivo_adjunto.'" class="btn btn-warning btn-sm" target="_blank"> <i class="fa fa-file"></i> '.$actividad->archivo_adjunto.' </a>
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
				if( confirm("¿Desea eliminar la actividad?") )
				{
					$(this).parent('form').submit();
				}
			});
		});
	</script>
@endsection