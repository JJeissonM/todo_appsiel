
<?php
	if($estudiante->matricula_activa() == null )
	{
		dd('El estudiante ' . $estudiante->tercero->descripcion . ' NO tiene una matrícula activa en el sistema. Por favor revisar.');
	}

	$cod_matricula = $estudiante->matricula_activa()->codigo;
	$descripcion_curso = $estudiante->matricula_activa()->curso->descripcion;
	if ( isset($matricula) )
	{
		$cod_matricula = $matricula->codigo;
		$descripcion_curso = $matricula->curso->descripcion;
	}
?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th> Datos del estudiante </th>
			<th> Datos del responsable financiero </th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<b> Nombre: </b> {{ $estudiante->tercero->descripcion }}
				<br>
				<b> Cod. Matrícula: </b> {{ $cod_matricula }}
				<br>
				<b> Curso: </b>{{ $descripcion_curso }}
			</td>
			<td>
				@if( !is_null( $estudiante->responsable_financiero() ) )
					<b> Nombre: </b> {{ $estudiante->responsable_financiero()->tercero->descripcion }}
					<br>
					<b> Cédula: </b> {{ number_format( $estudiante->responsable_financiero()->tercero->numero_identificacion, 0, ',', '.' ) }}
					<br>
					<b> Dirección: </b> {{ $estudiante->responsable_financiero()->tercero->direccion1 }}
					<br>
					<b> Teléfono: </b> {{ $estudiante->responsable_financiero()->tercero->telefono1 }}
					@can('Libreta pagos')
						@if ($estudiante->responsable_financiero()->cliente() == null)
							<p class="text-danger">
								Responsable financiero no esta creado como cliente. 
								<a class="btn btn-success btn-xs" href="{{ url( '/vtas_tercero_a_cliente_create_direct' . '/' .  $estudiante->responsable_financiero()->tercero->id . '/' . 'tesoreria-ver_plan_pagos-' . $libreta->id . '-id=3-id_modelo=31-id_transaccion=' ) }}"><i class="fa fa-plus"></i> Crear como cliente</a>
							</p>						
						@endif
					@endcan
				@else
					<span style="color: red;"> NOTA: El estudiante no tiene responsable financiero asociado. </span>
				@endif
			</td>
		</tr>
	</tbody>
</table>
