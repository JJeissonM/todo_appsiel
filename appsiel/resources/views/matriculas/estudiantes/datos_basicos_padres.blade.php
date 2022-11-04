<?php 
	$tiene_reponsables = false;
	$listado_responsableestudiantes = [];
	if ($estudiante != null) {
		$listado_responsableestudiantes = $estudiante->responsableestudiantes;
	}
?>	
	<table width="100%">
		<tr>
			@foreach( $listado_responsableestudiantes AS $responsable )
				<?php 
					$tiene_reponsables = true;
				?>
				<td>
					<table class="table table-bordered">
						<tr>
							<td colspan="3" style="background-color: #ddd;"><strong>Datos {{ $responsable->tiporesponsable->descripcion }}</strong></td>
						</tr>
						<tr>
							<td><strong>Nombre: </strong> <br> {{ $responsable->tercero->descripcion }}</td>
							<td><strong>Cédula: </strong> <br> {{ number_format($responsable->tercero->numero_identificacion,0,',','.') }}</td>
							<td><strong>Ocupación: </strong> <br> {{ $responsable->ocupacion }}</td>
						</tr>
						<tr>
							<td><strong>Teléfono: </strong> <br> {{ $responsable->tercero->descripcion }}</td>
							<td colspan="2"><strong>E-mail: </strong> <br> {{ $responsable->tercero->email }}</td>
						</tr>
					</table>
				</td>	
			@endforeach
		</tr>
	</table>
@if( !$tiene_reponsables )
	<span class="text-danger"> <i class="fa fa-warning"></i> Sin datos de padres ni acudiente.</span>
	<br><br>
@endif
	