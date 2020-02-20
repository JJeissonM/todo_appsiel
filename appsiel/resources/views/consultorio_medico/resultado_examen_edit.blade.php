<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		#div_cargando{
			display: none;/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom:0px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index:0;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro - Exámen "{{ $examen->descripcion }}"</h4>
		    <hr>

		    @if( isset($url_action) )
		    	{{ Form::model($registro,['url'=>$url_action,'id'=>'form_create','files' => true]) }}
			@else
				{{ Form::model($registro,['url'=>'web','id'=>'form_create','files' => true]) }}
			@endif
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }

				  //echo base_path();
				?>

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
										{{ VistaController::dibujar_solo_control( $variable->tipo_campo, "campo_variable_organo-".$variable->id."-".$organo->id ) }}
									</td>
								@endforeach
							</tr>
						@endforeach
					</tbody>
				</table>
				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
				
			{{ Form::close() }}

			@if(isset($tabla))

				{!! $tabla !!}
				
				<br/><br/>

			@endif
		</div>
	</div>
	<br/><br/>


	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')

	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection