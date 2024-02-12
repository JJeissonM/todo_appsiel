<table class="table table-striped">
	<tr>
		<td>
			<?php  
				$unwanted_array = array('À'=>'A', 'Á'=>'A', 'È'=>'E', 'É'=>'E',
                                'Ì'=>'I', 'Í'=>'I', 'Ò'=>'O', 'Ó'=>'O', 'Ù'=>'U',
                                'Ú'=>'U', 'à'=>'a', 'á'=>'a', 'è'=>'e', 'é'=>'e', 'ì'=>'i', 'í'=>'i', 'Ñ'=>'N', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ù'=>'u', 'ú'=>'u' );
			?>
			<div class="container-fluid">
	
			@for($k=0;$k < count($estudiantes) ;$k++)
				<!-- TITULOS -->
				<div align="center"> <b> Fichas de datos básicos de estudiantes </b> </div>
				<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
				<b>Curso: </b> {{ $estudiantes[$k]['curso'] }} 


				<?php 
				$i=0; // Contador de formatos por página
				foreach ($estudiantes[$k]['listado'] as $registro){
						$nombre_completo = $registro->nombre_completo;
					?>
					<div class="recuadro">
						<table class="table">
								<tr>
									<td class="celda1" colspan="2"><strong><h3>Datos básicos del estudiante</h3></strong></td>
								</tr>	
								<tr>
									<td class="celda1" colspan="2"><strong>Nombre: </strong> {{ $registro->nombre_completo }}</td>
								</tr>
								<tr>
									<td class="celda1"><strong>Doc. Identidad: </strong>{{ $registro->tipo_y_numero_documento_identidad }}</td>
									<td class="celda2"><strong>Fecha nacimiento: </strong>{{ $registro->fecha_nacimiento }} <?php echo " (".calcular_edad($registro->fecha_nacimiento).")";?></td>
								</tr>
								<tr>
									<td class="celda1"><strong>Género: </strong>{{ $registro->genero }}</td>
									<td class="celda2"><strong>Teléfono: </strong>{{ $registro->telefono1 }}</td>
								</tr>
								<tr>
									<td class="celda1" colspan="2"><strong>Dirección: </strong>{{ $registro->direccion1 }}</td>
								</tr>
								<tr>
									<td class="celda1"><strong>Curso: </strong>{{ $registro->curso_descripcion }}</td>
									<td class="celda2"><strong>Acudiente: </strong>{{ $registro->acudiente }}</td>
								</tr>
								<tr>
									<td class="celda1" colspan="2"><strong>Papá: </strong>{{ $registro->papa }}</td>
								</tr>
								<tr>
									<td class="celda1" colspan="2"><strong>Mamá: </strong>{{ $registro->mama }}</td>
								</tr>
						</table>
						<br/>
					</div>
					<br/><br/>
				<?php 
					$i++;
					if($i==3){
						echo '<div class="page-break"></div>';
						$i=0;
					}
				} ?>
				<div class="page-break"></div>
			@endfor
		</div>
	</td>
</tr>
</table>

<?php
	function calcular_edad($fecha_nacimiento){
		$datetime1 = new DateTime($fecha_nacimiento);
		$datetime2 = new DateTime('now');
		$interval = $datetime1->diff($datetime2);
		$edad=$interval->format('%R%a');
		return floor($edad/365)." Años";
	}
?>