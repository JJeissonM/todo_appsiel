<?php
	use App\Http\Controllers\Core\ConfiguracionController;
	$salida="";
	$contenido=$seccion->contenido;

	$contenido = str_replace("numero_dia_actual", date('d'),$contenido);
	$contenido = str_replace("numero_mes_actual", ConfiguracionController::nombre_mes(date('m')), $contenido);
	$contenido = str_replace("anio_actual", date('Y'),$contenido);

	$espacios_antes = str_repeat("<br/>",$seccion->cantidad_espacios_antes);
	$espacios_despues = str_repeat("<br/>",$seccion->cantidad_espacios_despues);

	switch ($seccion->presentacion) {
		case 'div':

			$estilos='text-align:'.$seccion->alineacion.';font-weight:'.$seccion->estilo_letra.';';
			$salida.=$espacios_antes.'<div style="'.$estilos.'">'.$contenido.'</div>'.$espacios_despues;
			break;

		case 'tabla':
			$contenido.='<table class="contenido" style="border: 1px solid grey;width:90%;" align="center">
		<thead>
			<tr>
				<th style="border: 1px solid grey;background-color: #E0E0E0;">Asignaturas</th>
				<th style="border: 1px solid grey;background-color: #E0E0E0;">I.H.</th>
				<th colspan="3" style="border: 1px solid grey;background-color: #E0E0E0;">Calificaciones</th>
			</tr>
		</thead>
		<tbody>';
			foreach($asignaturas as $asignatura){
				// Se llama a la calificacion de cada asignatura
				$calificacion = App\Calificacion::where(['id_colegio'=>$colegio->id,'anio'=>$anio,
													'id_periodo'=>$periodo->id,'id_grado'=>$id_curso,
													'id_estudiante'=>$estudiante->id_estudiante,'id_asignatura'=>$asignatura->id])
													->get()->first();
				
				// Se calcula el texto de la calificación
				if(count($calificacion)!=0){
					$nota=$calificacion->calificacion;
					if($nota>95){
						$desempeno=["S","Desempeño Superior"];
					}elseif($nota>75){
						$desempeno=["A","Desempeño Alto"];
					}elseif($nota>59){
						$desempeno=["B","Desempeño Básico"];
					}else{
						$desempeno=["I","Desempeño Bajo"];
					}
				}else{
					$desempeno=["I","Desempeño Bajo"];
				}
				
			if(count($calificacion)!=0){
				$contenido.='<tr style="font-size: '.$tam_letra.'mm;">
						<td style="border: 1px solid grey;"> '.$asignatura->descripcion .'</td>
						<td align="center" style="border: 1px solid grey;"> '. $asignatura->intensidad_horaria .' </td>
						<td align="center" style="border: 1px solid grey;"> '. $calificacion->calificacion .' </td>
						<td align="center" style="border: 1px solid grey;"> '. $desempeno[0] .' </td>
						<td align="center" style="border: 1px solid grey;"> '. $desempeno[1] .' </td>
					</tr>';
			}else{
				$contenido.='<tr style="font-size: '.$tam_letra.'mm;">
						<td style="border: 1px solid grey;" width="300px"> '.$asignatura->descripcion .'</td>
						<td align="center" style="border: 1px solid grey;"> '. $asignatura->intensidad_horaria .' </td>
						<td align="center" style="border: 1px solid grey;"> '. 0 .' </td>
						<td align="center" style="border: 1px solid grey;"> '. $desempeno[0] .' </td>
						<td align="center" style="border: 1px solid grey;"> '. $desempeno[1] .' </td>
					</tr>';
			}
			}
		$contenido.='</tbody>
	</table>';

			$salida.=$espacios_antes.'<div style="'.$estilos.'">'.$contenido.'</div>'.$espacios_despues;
			break;
		
		default:
			# code...
			break;
	}
	echo $salida;
?>