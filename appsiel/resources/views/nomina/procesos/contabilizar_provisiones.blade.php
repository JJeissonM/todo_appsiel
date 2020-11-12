@extends('core.procesos.layout')

@section( 'titulo', 'Contabilizar consolidados mensuales' )

@section('detalles')
	<p>
		Este proceso permiter ...
	</p>

	Luego ...
	
	<br>
@endsection

@section('formulario')
	<div class="row">

		<div class="row" style="padding:5px;">					
			<label class="control-label col-sm-4" > <b> Documento de liquidación: </b> </label>

			<div class="col-sm-8">
				{{ Form::select('nom_doc_encabezado_id',App\Nomina\NomDocEncabezado::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'nom_doc_encabezado_id' ]) }}
			</div>					 
		</div>

		<div class="row" style="padding:5px;">					
			<label class="control-label col-sm-4" > <b> Archivo plano: </b> </label>

			<div class="col-sm-8">
				{{ Form::file('archivo_plano', [ 'class' => 'form-control', 'id' => 'archivo_plano', 'accept' => 'text/plain' ]) }}
			</div>					 
		</div>

		<div class="col-md-4">
			<button class="btn btn-success" id="btn_cargar" disabled="disabled"> <i class="fa fa-calculator"></i> Cargar </button>
		</div>    				
	</div>
@endsection

@section('javascripts')
	<script type="text/javascript">

	</script>
@endsection