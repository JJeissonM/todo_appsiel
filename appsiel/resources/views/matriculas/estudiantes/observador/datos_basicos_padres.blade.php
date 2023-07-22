<?php 

	$tiene_reponsables = false;
	$listado_responsableestudiantes = [];
	if ($estudiante != null) {
		$listado_responsableestudiantes = $estudiante->responsableestudiantes;
	}
?>

	<table width="100%">
			<?php 
				$arr_responsables_terceros_ids = [];
			?>
			@foreach( $listado_responsableestudiantes AS $responsable )
				<?php 
					$tiene_reponsables = true;
					if(in_array($responsable->tercero_id,$arr_responsables_terceros_ids))
					{
						continue;
					}
				?>
				<tr>
					<td>
						<table class="table table-bordered" style="border: #ddd 1px solid; border-collapse: collapse;">
							<tr style="border: #ddd 1px solid; border-collapse: collapse;">
								<td colspan="3" style="background-color: #ddd;"><strong>DATOS {{ $responsable->tiporesponsable->descripcion }}</strong></td>
							</tr>
							<tr style="border: #ddd 1px solid; border-collapse: collapse;">
								<td style="width: 40%;"><strong>Nombre: </strong> <br> {{ $responsable->tercero->descripcion }}</td>
								<td style="width: 20%;"><strong>Cédula: </strong> <br> {{ number_format($responsable->tercero->numero_identificacion,0,',','.') }}</td>
								<td style="width: 40%;"><strong>Ocupación: </strong> <br> {{ $responsable->ocupacion }}</td>
							</tr>
							<tr style="border: #ddd 1px solid; border-collapse: collapse;">
								<td style="width: 40%;"><strong>Teléfono: </strong> <br> {{ $responsable->tercero->telefono1 }}</td>
								<td colspan="2" style="width: 60%;"><strong>E-mail: </strong> <br> {{ $responsable->tercero->email }}</td>
							</tr>
						</table>
					</td>
					<?php 
						$tiene_reponsables = true;
						$arr_responsables_terceros_ids[] = $responsable->tercero_id;
					?>
				</tr>	
			@endforeach
	</table>
@if( !$tiene_reponsables )
	<span class="text-danger"> <i class="fa fa-warning"></i> Sin datos de padres ni acudiente.</span>
	<br><br>
@endif
	