<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

<div class="container-fluid">
	<div class="marco_formulario">
	    <h4>Nuevo registro - Exámen "{{ $examen->descripcion }}"</h4>
	    <hr>

	    {{ Form::open(['url'=>$url_action,'id'=>'form_create','files' => true]) }}

			{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

			<table class="table table-bordered">
				<thead>
					<tr>
						<th>&nbsp;</th>
						@foreach($variables as $variable)
							<th>
								{{ $variable->descripcion }}
							</th>
						@endforeach								
					</tr>
				</thead>
				<tbody>	
					@foreach($organos as $organo)
						<tr>
							<td>
								{{ $organo->descripcion }}
							</td>
							@foreach($variables as $variable)
								<td>
									{{ VistaController::dibujar_solo_control( $variable->tipo_campo, "campo_variable_organo-".$variable->id."-".$organo->id, null ) }}
								</td>
							@endforeach
						</tr>
					@endforeach
				</tbody>
			</table>
			{{ Form::hidden('url_id',Input::get('id'))}}
			{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
			
		{{ Form::close() }}

	</div>
</div>