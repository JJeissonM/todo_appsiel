<div class="row">
	<div class="col-md-12">
        <div>
            <table class="table table-striped table-bordered" id="ingreso_registros">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Respuesta enviada</th>
                        <th>Calificación asignada</th>
                    </tr>
                </thead>
                <tbody>
                	@foreach($estudiantes as $estudiante)

                		<?php

							// Se obtienen las respuestas enviadas de cada estudiante
							$respuestas = App\Cuestionarios\RespuestaCuestionario::where(['actividad_id'=>$actividad->id,'estudiante_id'=>$estudiante->id])->get()->first();

							if( is_null( $respuestas ) )
				            {   
				                $respuestas = (object)['id'=>0,'respuesta_enviada'=>'','calificacion'=>''];
				            }

						?>

						<tr>
							<td> 
								{{ $estudiante->nombre_completo }}
							</td>
							<td> 
								{!! $respuestas->respuesta_enviada !!}
							</td>
							<td> 
								<div title="Doble click para modificar." class="elemento_modificar" data-respuesta_id="{{$respuestas->id}}">
								{{ $respuestas->calificacion }} </div>
							</td>
						</tr>

					@endforeach <!-- Por cada estudiante -->
                </tbody>
            </table>
		</div>
	</div>
</div>