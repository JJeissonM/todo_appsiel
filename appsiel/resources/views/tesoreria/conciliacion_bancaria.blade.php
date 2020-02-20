@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Importar datos</h4>
		    <hr>

			<div class="alert alert-warning">
				{{ Form::open(['url' => 'tesoreria/procesa_archivo_plano_bancos?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'files' => true, 'id'=>'form_consulta']) }}
					<?php

					  	$empresa_id = Auth::user()->empresa_id;
		                $empresa = App\Core\Empresa::find($empresa_id);
		                $valor = $empresa->descripcion;

					?>

					  <strong>Parámetros de selección</strong>
					  <br/><br/>
						  {{ Form::bsLabel('core_empresa_id',[$valor,$empresa_id],'Empresa', []) }}
						<br/><br/>
						{{ Form::file('archivo', ['class' => 'form-control', 'required' => 'required' ]) }}
						
						<br/><br/>					

					{{ Form::hidden('url_id',Input::get('id')) }}
					{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}

					<!-- <button type="submit">ir</button> -->
					
				{{ Form::close() }}

				<button type="button" class="btn btn-primary btn-xs" id="btn_continuar1"><i class="fa fa-btn fa-forward"></i> Continuar</button>
			</div>

			<div class="alert alert-success" id="alert_verde" style="display: none;">
			  <strong>Conciliación</strong>
			  <br/><br/>
			  <div id="resultado_consulta">

				</div>
			</div>

		</div>
	</div>
<br/><br/>
@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function(){

		$('#btn_continuar1').click(function(event){
			event.preventDefault();
			if (validar_requeridos()) {

				$('#div_cargando').show();					
				$('#form_consulta').submit();
			}
		});

		function validar_requeridos(){
			$( "*[required]" ).each(function() {
						if ( $(this).val() == "" ) {
						  $(this).focus();
						  control = false;
						  alert('Este campo es requerido.');
						  return false;
						}else{
						  control = true;
						}
					});
			return control;
		}
		
	});
</script>
@endsection