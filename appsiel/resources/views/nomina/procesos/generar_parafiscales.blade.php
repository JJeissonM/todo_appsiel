@extends('core.procesos.layout')

@section( 'titulo', 'Generar acumulados dea seguridad social y parafiscales' )

@section('detalles')
	<p>
		Este proceso recorre todos los registros de documentos de nómina realizados en un mes y  acumula los valores de la carga prestacional que la empresa debe pagar a las distintas entidades gubernamentales.
	</p>

	<p>
		<b>Seguridad social</b>
		<br>
		El sistema tomas los valores descontados a los empleados en Salu y Pensión y los suma con los valores que están a cargo de la empresa según las normas vigentes. También calcula el valor de a pagar por parte de la empresa por concepto de riesgos laborales.
	</p>

	<p>
		<b>Parafiscales</b>
		<br>
		El sistema calcula los valores de los devengos de la nómina y sobre estos aplica los porcentajes para conocer el valor a pagar por Caja de compensación, SENA e ICBF.
	</p>
	
	<br>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">
		{{ Form::open(['url'=>'nom_calcular_acumulados_seguridad_social_parafiscales','id'=>'formulario_inicial','files' => true]) }}
			
			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" > <b> Fecha último día del mes: </b> </label>

				<div class="col-sm-8">
					<input type="date" name="fecha" class="form-control">
				</div>
			</div>

			<div class="col-md-12" style="text-align: center;">
				<button class="btn btn-success" id="btn_calcular"> <i class="fa fa-calculator"></i> Calcular </button>
			</div>
		{{ Form::close() }}
	</div>
@endsection

@section('javascripts')
	<script type="text/javascript">

	</script>
@endsection