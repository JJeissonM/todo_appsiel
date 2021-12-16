@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	<br><br>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<div class="container-fluid">

				@include('nomina.reportes.tabla_datos_basicos_empleado',['empleado'=>$contrato])
                
				@include('nomina.prestaciones_sociales.show_prestaciones_consolidadas_empleado_tabla', ['titulo'=>'Vacaciones','lista_consolidados'=>$data['vacaciones']])

				<br>

				@include('nomina.prestaciones_sociales.show_prestaciones_consolidadas_empleado_tabla', ['titulo'=>'Prima de servicios','lista_consolidados'=>$data['prima_legal']])

				<br>

				@include('nomina.prestaciones_sociales.show_prestaciones_consolidadas_empleado_tabla', ['titulo'=>'Cesantías','lista_consolidados'=>$data['cesantias']])

				<br>

				@include('nomina.prestaciones_sociales.show_prestaciones_consolidadas_empleado_tabla', ['titulo'=>'Intereses de Cesantías','lista_consolidados'=>$data['intereses_cesantias']])

			</div>
		</div>
	</div>

@endsection