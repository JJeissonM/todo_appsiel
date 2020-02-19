<style>
	div.contenido1 {
		text-align: center;
	}
	
	table {
		width:100%;
		border: 1px solid #ddd;
	}
	
	th {
		background-color: #CACACA;
	}
	
	table.encabezado {
		background-color: #98A8B5;
	}
	
	table.contenido td {
		border: 1px solid #ddd;
	}
	
	span.etiqueta{
		font-weight: bold;
		display: inline-block;
		width: 100px;
	}
	
	span.warning{
		font-weight: bold;
		display: inline-block;
		width: 100px;
		background-color:orange;
	}
	
	hr {
		border-color: orange;
	}
	
	
</style>

<!-- JQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>


<div class="contenido1">

<h3 align="center">Revisión de boletines</h3>

	<h4 align="center">Se hallaron <?php echo count( $estudiantes->toArray() );?> estudiantes matriculados</h4>
	<?php 
	
		// Se recorre cada estudiante matriculado
		foreach($estudiantes as $estudiante) {
			
				?>
				<table  class="encabezado">
					<tr>
						<td><span class="etiqueta">Estudiante</span> {{ $estudiante->apellido1 }} {{ $estudiante->apellido2 }} {{ $estudiante->nombres }} </td>
						<td><span class="etiqueta">Periodo/Año</span> {{ $periodo->descripcion }} &#47;  {{ $anio }}</td>
					</tr>
					<tr>
						<td><span class="etiqueta">Curso</span> {{ $curso->descripcion }}</td>
						<td><span class="etiqueta">Ciudad</span> {{ $colegio->ciudad }}</td>
					</tr>
					<?php 
						// La tabla observaciones_boletines guarda el puesto calculado para cada estudiante
						$observacion = DB::table('observaciones_boletines')
						->where(['id_colegio'=>$colegio->id,'anio'=>$anio,
								'id_periodo'=>$periodo->id,'curso_id'=>$curso->id,
								'id_estudiante'=>$estudiante->id_estudiante])
						->get(); 
					?>
					@if($colegio->maneja_puesto=="Si")
						@if( !is_null($observacion) )
						<tr>					
							<td <?php if($observacion[0]->puesto==""){echo "style='background-color:#F08282; color:white;'";}?>><span class="etiqueta">Puesto:</span> {{ $observacion[0]->puesto }}</td>
							<td><span class="etiqueta">&nbsp; </span> &nbsp; </td>
						</tr>
						@else
						<tr>					
							<td colspan="2"><span class="warning">Puesto:</span> No calculado. </td>
						</tr>
						@endif
					@endif
				</table>
				
				<table class="contenido">
					<thead>
						<tr>
							<th>Asignaturas</th>
							<th>Calificación</th>
							<th>Logros</th>
						</tr>
					</thead>
					<tbody>
						<?php

							$tbody = '';
							foreach($asignaturas as $asignatura) 
							{
								
								// Se llama a la calificacion de cada asignatura
								$calificacion = App\Calificaciones\Calificacion::where( [ 'id_colegio' => $colegio->id, 'anio' => $anio, 'id_periodo' => $periodo->id, 'curso_id' => $curso->id, 'id_estudiante' => $estudiante->id_estudiante, 'id_asignatura' => $asignatura->id ] )
									->get()->first();
								
								// Se calcula el texto de la calificación
								if ( !is_null($calificacion) ) 
								{
									$escala = DB::table('sga_escala_valoracion')
									->where('calificacion_minima','<=',$calificacion->calificacion)
									->where('calificacion_maxima','>=',$calificacion->calificacion)->get();

									if ( !is_null($escala) ) 
									{
										$escala = $escala[0];
									}else{
										$escala = (object) array('id' => 0, 'nombre_escala' => '<span style="color:red;">No hay escala de valoración para esta calificación.</span>');
									}

									
								}else{
									$calificacion = (object) array('calificacion' => 0);
									$escala = (object) array('id' => 0, 'nombre_escala' => '');
									//$escala = 'NO';
								}

								$desempeno = $escala->nombre_escala;

								$tbody.='<tr>
										<td width="350px">'.$asignatura->descripcion.'</td>';
								
								//if($asignatura->maneja_calificacion==1)
								//{
									$tbody.='<td';
									if( !is_null($calificacion) )
									{
										if($calificacion->calificacion==0)
										{
											$tbody.=' style="background-color:#F08282; color:white;"';
										}
										$tbody.='>'.$calificacion->calificacion.'( '.$desempeno.')</td>';
									}else{
										$tbody.=' style="background-color:#F08282; color:white;">&nbsp;</td>';
									}
								
								/*}else{
									$tbody.='<td> N.A.&nbsp; </td>';
								}*/

								if ( !is_null($escala) ) 
								{
									$logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)->where('periodo_id',$periodo->id)->where('curso_id',$curso->id)->where('asignatura_id',$asignatura->id)->where('estado','Activo')->get();

									$n_nom_logros = 0;

									if ( !is_null($logros) ) 
									{
										$n_nom_logros = count( $logros->toArray() );
									}
								}else{
									$logros = (object) array('descripcion' => '');
									$n_nom_logros = 0;
								}
								
								$tbody.='<td ';if($n_nom_logros==0){ $tbody.='style="background-color:#F08282; color:white;"';}
								$tbody.='>
										<ul>';
										foreach($logros as $un_logro)
										{
											$tbody.='<li>'.$un_logro->descripcion.'</li>';
										}		
								$tbody.='</ul>
										</td>
											</tr>';

							} //fin recorrido de asignaturas del estudiante

							echo $tbody;						
						?>
						<tr> 
							@if( !is_null($observacion) )
							<td colspan="3" <?php if($observacion[0]->observacion==""){echo "style='background-color:#F08282; color:white;'";}?>> 
								<span class="etiqueta" >Observaciones: </span> <br/> {{ $observacion[0]->observacion }} 
							</td>
							@else
							<td colspan="3"> 
								<span class="warning" >Observaciones: </span> <br/> No se han generado observaciones para este estudiante.
							</td>
							@endif
							
						</tr>
					</tbody>
				</table>
				<br/><br/>
				<?php
			
		} //fin foreach estudiantes
	?>	
</div>