@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Actualización</h4>
		    <hr>

			{{Form::open( ['url' => '/matriculas/estudiantes/observador/valorar_aspectos','method'=>'post', 'class'=>'form-horizontal', 'id' => 'form_create'] ) }}

				<div class="form-group">
					<div class="alert alert-info">
					  <strong>Convenciones!</strong> <br/> 
					  S= Siempre &nbsp;&nbsp;&nbsp;&nbsp;   CS= Casi siempre  &nbsp;&nbsp;&nbsp;&nbsp;      AV= Algunas veces  &nbsp;&nbsp;&nbsp;&nbsp; N= Nunca
					</div>
				</div>

				{{ Form::bsButtonsForm( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}

				{{ Form::bsTextArea('observacion_general', $observacion_general, 'Observación general', []) }}

				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th rowspan="2">No.</th>
								<th rowspan="2">ASPECTOS</th>
								<th colspan="4">Periodos</th>
							</tr>
							<tr>
								<th>1°</th>
								<th>2°</th>
								<th>3°</th>
								<th>4°</th>
							</tr>
						</thead>
						<tbody>
							{{ Form::hidden('id_estudiante',$estudiante->id) }}

							{{ Form::hidden('fecha_valoracion', date('Y-m-d') ) }}

							@foreach ($tipos_aspectos as $tipo_aspecto)
								<tr><td colspan="6"><b>{{ $tipo_aspecto->descripcion }}</b></td></tr>
								@php 
									$aspectos = App\Matriculas\CatalogoAspecto::where('id_tipo_aspecto','=',$tipo_aspecto->id)->orderBy('orden','ASC')->get()
								@endphp
								
								@foreach ($aspectos as $aspecto)
									<?php 
										$val_per1 = "";
										$val_per2 = "";
										$val_per3 = "";
										$val_per4 = "";
										$aspecto_estudiante_id="";
										
										$aspecto_estudiante = App\Matriculas\AspectosObservador::where('id_aspecto','=',$aspecto->id)->where('id_estudiante','=',$estudiante->id)->where('fecha_valoracion','like',date('Y').'%')->get()->first();
										
										if( !is_null($aspecto_estudiante) )
										{
											$val_per1 = $aspecto_estudiante->valoracion_periodo1;
											$val_per2 = $aspecto_estudiante->valoracion_periodo2;
											$val_per3 = $aspecto_estudiante->valoracion_periodo3;
											$val_per4 = $aspecto_estudiante->valoracion_periodo4;
											$aspecto_estudiante_id = $aspecto_estudiante->id;
										}

									?>
									<tr>
										{{ Form::hidden('aspecto_estudiante_id[]',$aspecto_estudiante_id) }}
										{{ Form::hidden('id_aspecto[]',$aspecto->id) }}
										<td>{{ $aspecto->orden }}</td>
										<td>{{ $aspecto->descripcion }}</td>
										<td>{{ Form::text('valoracion_periodo1[]', $val_per1, ['size' => 1]) }}</td>
										<td>{{ Form::text('valoracion_periodo2[]', $val_per2, ['size' => 1]) }}</td>
										<td>{{ Form::text('valoracion_periodo3[]', $val_per3, ['size' => 1]) }}</td>
										<td>{{ Form::text('valoracion_periodo4[]', $val_per4, ['size' => 1]) }}</td>
									</tr>
								@endforeach
							@endforeach
						</tbody>

					</table>
				</div>
				<br/>

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

			{{Form::close()}}
		</div>
	</div>	
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});

		});
	</script>
@endsection