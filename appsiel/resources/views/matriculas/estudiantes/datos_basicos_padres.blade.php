<?php 

	$tiene_reponsables = false;
	$listado_responsableestudiantes = [];
	if ($estudiante != null) {
		$listado_responsableestudiantes = $estudiante->responsableestudiantes;
	}
?>	
	@if ($estudiante != null && $vista == 'show')
		<a class="btn btn-info btn-xs" href="{{url('matriculas/estudiantes/gestionresponsables/estudiante_id?id=1&id_modelo='.Input::get('id_modelo').'&estudiante_id=' . $estudiante->id )}}"><i class="fa fa-plus"></i> Gestionar Responsables</a>
		<br><br>
	@endif

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
						<table class="table table-bordered">
							<tr>
								<td colspan="3" style="background-color: #ddd;"><strong>DATOS {{ $responsable->tiporesponsable->descripcion }}</strong></td>
							</tr>
							<tr>
								<td><strong>Nombre: </strong> <br> {{ $responsable->tercero->descripcion }}</td>
								<td><strong>Cédula: </strong> <br> {{ number_format($responsable->tercero->numero_identificacion,0,',','.') }}</td>
								<td><strong>Ocupación: </strong> <br> {{ $responsable->ocupacion }}</td>
							</tr>
							<tr>
								<td><strong>Teléfono: </strong> <br> {{ $responsable->tercero->telefono1 }}</td>
								<td colspan="2"><strong>E-mail: </strong> <br> {{ $responsable->tercero->email }}</td>
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
	