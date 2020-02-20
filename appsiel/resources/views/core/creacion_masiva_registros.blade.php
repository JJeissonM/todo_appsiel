@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<?php 
		$empresa_id = Auth::user()->empresa_id;
                $empresa = App\Core\Empresa::find($empresa_id);
                $valor = $empresa->descripcion;
	?>

	<div class="container-fluid">
	<div class="marco_formulario">
	    <h4>Creación masiva de registros</h4>
	    <hr>

		{{ Form::open(['url' => 'creacion_masiva_registros_procesar?id='.Input::get('id'), 'files' => true]) }}

			<div class="alert alert-info">
			  <strong>Parámetros de selección</strong>
			  <br/><br/>
				  {{ Form::bsLabel('core_empresa_id',[$valor,$empresa_id],'Empresa', []) }}
				<br/><br/>
				{{ Form::bsSelect('modelo_id', null, 'Modelo', $modelos, ['required' => 'required', 'id' => 'modelo_id']) }}
				<br/><br/>
				{{ Form::file('archivo', ['class' => 'form-control', 'required' => 'required', 'accept' => '.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel' ]) }}
			</div>			

			{{ Form::hidden('url_id',Input::get('id')) }}

			<button type="submit" class="btn btn-primary btn-xs" id="btn_continuar1"><i class="fa fa-btn fa-forward"></i> Continuar</button>
			
		{{ Form::close() }}
	</div>
</div>
<br/><br/>
@endsection