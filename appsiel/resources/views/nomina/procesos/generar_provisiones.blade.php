@extends('core.procesos.layout')

@section( 'titulo', 'Generar consolidados del mes' )

@section('detalles')
	<p>
		Este proceso recorre todos los movimientos del mes y acumula los valores para liquidar las prestaciones sociales de cada empleado.
	</p>
	
	<br>
@endsection

@section('formulario')
	<div class="row">

		<div class="row" style="padding:5px;">
			<label class="control-label col-sm-4" > <b> Fecha final Lapso: </b> </label>

			<div class="col-sm-8">
				<input type="date" name="fecha_final_mes" class="form-control">
			</div>
		</div>

		<div class="row" style="padding:5px;">
			&nbsp;				 
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