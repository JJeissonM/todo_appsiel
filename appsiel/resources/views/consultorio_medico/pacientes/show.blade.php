<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
			
		/* Style the buttons that are used to open and close the accordion panel */
		.accordion {
		  background-color: #eee;
		  color: #444;
		  cursor: pointer;
		  padding: 18px;
		  width: 100%;
		  text-align: left;
		  border: none;
		  outline: none;
		  transition: 0.4s;
		  font-weight: bold;
		}

		/* Add a background color to the button if it is clicked on (add the .active class with JS), and when you move the mouse over it (hover) */
		.active, .accordion:hover {
		  background-color: #ccc;
		}

		/* Style the accordion panel. Note: hidden by default */		

		.tab-pane,.fade{
			background-color: transparent;
		}

	</style>
@endsection

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-6">
			&nbsp;&nbsp;&nbsp;
			<div class="btn-group">

				@if($url_crear!='')
					&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate($url_crear) }}
				@endif

				@if($url_edit!='')
					{{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
				@endif

				@if( is_null($consultas->first()) )
					{{ Form::formEliminar( 'consultorio_medico/eliminar_paciente', $registro->id ) }}
				@endif
			</div>
		</div>

		<div class="col-md-6">
				<div class="btn-group pull-right">
					@if($reg_anterior!='')
						{{ Form::bsBtnPrev('consultorio_medico/pacientes/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
					@endif

					@if($reg_siguiente!='')
						{{ Form::bsBtnNext('consultorio_medico/pacientes/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
					@endif
				</div>
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		<div class="marco_formulario">
			
			<br>

	        @include('consultorio_medico.pacientes_datos_historia_clinica_show')

	        @if( (int)config('consultorio_medico.mostrar_datos_laborales_paciente') )
	        	@include('consultorio_medico.pacientes.datos_laborales')
	        @endif

			<div class="container-fluid" >
				
				<h3>
					Consultas Médicas
					@can('salud_anamnesis_create')
						&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'consultorio_medico_create_consulta?id='.Input::get('id').'&id_modelo='.$modelo_consultas->id.'&paciente_id='.$registro->id.'&profesional_salud_id='.Auth::user()->id . '&action=create' ) }}
					@endcan
				</h3>

				<div class="panel-group" id="accordion">
					<?php
						$es_el_primero = true;						
					?>
					@foreach($consultas as $consulta)
						<div class="panel panel-default">
							<span class="consulta_id" data-consulta_id="{{ $consulta->id }}"></span>
							<div class="panel-heading">
								<a class="well well-lg" data-toggle="collapse" data-parent="#accordion" href="#collapse{{ $consulta->id }}" style="display: block; margin: 0;background-color: #f5f5f5">
									<h3 class="panel-title" style="font-size: 24px">
										Consulta No. {{ $consulta->id }} <span class="close">&plus;</span>
									</h3>
								</a>
							</div>
							
							
							<?php
								if( $es_el_primero )
								{
									$clase = 'collapse';
									$es_el_primero = false;
								}else{
									$clase = 'collapse';
								}	
							?>

							<div id="collapse{{ $consulta->id }}" class="panel-collapse {{$clase}}">
								<div class="panel-body">								

					            	<div class="secciones_consulta">
										<ul class="nav nav-tabs">
											<?php $cont = 1; ?>
											@foreach($secciones_consulta as $seccion)
												@if( $seccion->activo )
													<?php $href = "#tab_".$cont."_".$consulta->id; ?>
													@if($cont == 1)
														<li class="active"><a data-toggle="tab" href="{{$href}}">{{ $seccion->nombre_seccion }}</a></li>
													@else
														<li><a data-toggle="tab" href="{{$href}}">{{ $seccion->nombre_seccion }}</a></li>
													@endif
													<?php $cont++; ?>
									            @endif
									        @endforeach
									    </ul>

									    <div class="tab-content">
									    	<?php $cont = 1; ?>
											@foreach($secciones_consulta as $seccion)
												@if( $seccion->activo )
													<?php $ID = "tab_".$cont."_".$consulta->id; ?>
													@if($cont == 1)
														<div id="{{$ID}}" class="tab-pane fade in active">
										            		@include( $seccion->url_vista_show )
										            	</div>
										            @else
										            	<div id="{{$ID}}" class="tab-pane fade">
										            		@include( $seccion->url_vista_show )
										            	</div>
										            @endif
													<?php $cont++; ?>
									            @endif
									        @endforeach
									    </div>
									</div> <!-- FIN secciones_consulta -->		
								</div>
														
							</div> <!-- FIN class panel accordion -->			
						</div>
						<br>
					@endforeach
		    	</div>
			</div>
			<br><br>

		</div> <!-- Marco -->
	</div>
	<br/><br/>


	@include('components.design.ventana_modal2',['titulo2'=>'Editar registro','texto_mensaje2'=>''])

@endsection


@section('scripts')
	<script>
		var paciente_id = "{{$id}}";
		$(document).ready(function(){

			$(".btn_eliminar_datos_modelo").click(function(event){
				event.preventDefault();
				var r = confirm("¿Desea eliminar todos los datos de " + $(this).attr('data-descripcion_modelo') + " ingresados?");
				if (r == true) {
				  $(this).parents('.form_eliminar').submit();
				}
			});
	
			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}

		});
	</script>
@endsection