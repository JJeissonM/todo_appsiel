@if( !empty( (array)$preguntas ) )
	<div class="row">
		<div class="col-md-12">
			<?php
				$i = 1; // Para enumerar las preguntas y darles una posición en el listado

				// Las respuestas están almacenadas en formato JSON { "pregunta_id":"respuesta" }

				$respuesta_enviada = json_decode( $respuestas->respuesta_enviada );
				$vec_respuestas = [];
				//dd($respuestas);
				// Crear array con base en el JSON
				if ( !is_null($respuesta_enviada) ) {
					foreach ($respuesta_enviada as $key => $value) {
						// $key corresponde a la pregunta_id
						$vec_respuestas[$key]['respuesta'] = $value;
						$vec_respuestas[$key]['descripcion'] = '';
						$vec_respuestas[$key]['posicion_listado'] = ''; // Número en el listado
						$vec_respuestas[$key]['texto_respuesta'] = '';
					}/**/
				}						
			?>

				<div class="alert alert-success alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				  <strong>Procedimiento!</strong> 
				  <br> Paso 1. Debe contestar la pregunta y presionar el botón "Agregar Respuesta"
				  <br> Paso 2. Debe presionar el botón "Guardar TODAS las respuestas" para que las respuestas sean almacenadas.
				</div>

			<div class="row">

				<div class="col-md-6">
					<h4> Lista de preguntas </h4>
		            <hr>
					@foreach($preguntas as $pregunta)
						<?php
				//dd($pregunta);
							// Si la pregunta_id está en el array de respuestas, se le asigna su valor_respuesta almacenado y se oculta 
							$ocultar = 'block';
							$valor_respuesta = null;
							if ( array_key_exists($pregunta->id, $vec_respuestas) ) {
								$ocultar = 'none';
								$valor_respuesta = $vec_respuestas[$pregunta->id]['respuesta'];
								$vec_respuestas[$pregunta->id]['descripcion'] = $pregunta->descripcion;
								$vec_respuestas[$pregunta->id]['posicion_listado'] = $i;
								$vec_respuestas[$pregunta->id]['texto_respuesta'] = $valor_respuesta;
							}
						?>
						<div class="alert alert-success" id="div_{{ $i }}" style="display: {{ $ocultar }};">
							<div class="row">
								<div class="col-md-12">
									 <strong>{{ $i }}. <div class="pregunta_descripcion" style="display: inline;"> {{ $pregunta->descripcion }} </div> </strong> 
			                      <br/>
			                      <?php
			                      	switch ($pregunta->tipo) {
			                      		case 'Abierta':
			                      			$respuesta = '<br/>'.Form::textarea( 'pregunta_'.$pregunta->id, $valor_respuesta, [ 'id' => 'pregunta_'.$pregunta->id, 'class' => 'form-control' ] );
			                      			break;

			                      		case 'Seleccion multiple única respuesta':
			                      			$respuesta = '';
			                      			$opciones = json_decode($pregunta->opciones,true);

			                      			if ( !is_null($opciones) ) {
			                      				foreach ($opciones as $respuesta_opcion => $value) 
				                      			{
				                      				$checked = "";
				                      				if ( $respuesta_opcion == $valor_respuesta ) 
				                      				{
				                      					$checked = "checked";
				                      					$vec_respuestas[$pregunta->id]['texto_respuesta'] = $respuesta_opcion.') '.$value;
				                      				}
				                      				$respuesta.='<div class="radio">
																  <label><input type="radio" name="pregunta_'.$pregunta->id.'" value="'.$respuesta_opcion.'" '.$checked.'> <div style="display:inline;">'.' '.$respuesta_opcion.') '.$value.'</div></label>
																</div>';
				                      			}
			                      			}				                      			
			                      			break;

			                      		case 'Falso-Verdadero':
			                      			$checkedF = "";
			                      			$checkedV = "";
		                      				if ( $valor_respuesta == "Falso" ) {
		                      					$checkedF = "checked";
			                      				$checkedV = "";
		                      				}
		                      				if ( $valor_respuesta == "Verdadero" ) {
		                      					$checkedF = "";
			                      				$checkedV = "checked";
		                      				}
			                      			$respuesta = '<div class="falso_verdadero">';
			                      			$respuesta.='<div class="radio">
															  <label><input type="radio" name="pregunta_'.$pregunta->id.'" value="Falso" '.$checkedF.'> <div style="display:inline;">Falso</div></label>
															</div>';
											$respuesta.='<div class="radio">
															  <label><input type="radio" name="pregunta_'.$pregunta->id.'" value="Verdadero" '.$checkedV.'> <div style="display:inline;">Verdadero</div></label>
															</div>';
											$respuesta.='</div>';
			                      			break;
			                      		
			                      		default:
			                      			$respuesta = 'Error seleccion.';
			                      			break;
			                      	}

			                      	echo $respuesta;
			                      
			                  		?>
									<button class="btn btn-success btn-sm btn_agregar_respuesta" data-pregunta_id="{{$pregunta->id}}" data-tipo_pregunta="{{$pregunta->tipo}}" data-numero_pregunta="{{ $i }}"> Agregar respuesta</button>
								</div>
							</div>
						</div>                      
		                @php $i++ @endphp
					@endforeach
				</div>
				<div class="col-md-6">
					<?php
						//dd($vec_respuestas);
						$filas = '';
		                foreach ($vec_respuestas as $pregunta_id => $pregunta) 
		                {
		                    $filas .= '<tr>
		                    			<td style="display:none;">'.$pregunta['posicion_listado'].'</td>
		                    			<td style="display:none;">'.$pregunta_id.'</td>
		                    			<td style="display:none;">'.$pregunta['respuesta'].'</td>
		                                <td>'.$pregunta['descripcion'].'</td>
		                                <td>'.$pregunta['texto_respuesta'].'</td>
		                                <td><button type="button" class="btn btn-warning btn-xs btn_eliminar" title="Cambiar respuesta"><i class="glyphicon glyphicon-edit"></i></button></td>
		                                </tr>';
		                }
					?>
					<h4> Hoja de respuestas </h4>
		            <hr>
		            <div class="well">
						<div class="alert alert-warning alert-dismissible">
							<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						  <strong>Advertencia!</strong> 
						  <br>Si no presiona el botón "Guardar respuestas" ninguna respuesta será guardada.
						</div>

		                <table class="table table-striped table-bordered" id="ingreso_registros">
		                    <thead>
		                        <tr>
		                            <th style="display:none;">No. Pregunta</th>
		                            <th style="display:none;">ID Pregunta</th>
		                            <th style="display:none;">Respuesta</th>
		                            <th>Pregunta</th>
		                            <th>Respuesta</th>
		                            <th>Corregir</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                    	{!! $filas !!}
		                    </tbody>
		                </table>

						{{ Form::open(['url'=>'actividades_escolares/guardar_respuesta','id'=>'formulario_respuesta']) }}

							{{ Form::hidden('estudiante_id', $estudiante->id ) }}
							{{ Form::hidden('actividad_id', $actividad->id ) }}
							{{ Form::hidden('cuestionario_id', $cuestionario->id ) }}
							{{ Form::hidden('respuesta_enviada', $respuestas->respuesta_enviada, ['id'=>'respuesta_enviada', 'required'=>'required'] ) }}
							{{ Form::hidden('respuesta_id', $respuestas->id, ['id'=>'respuesta_enviada', 'required'=>'required'] ) }}
							
							<button class="btn btn-primary btn-sm btn_guardar_respuestas"><i class="fa fa-save"></i> Guardar TODAS las respuestas</button>

						{{ Form::close() }}
					</div>
				</div>

			</div>

			<div class="alert alert-danger alert-dismissible">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Estado!</strong> 
			  <br> Esperando la publicación de resultados.
			</div>
		</div>
	</div>
@endif