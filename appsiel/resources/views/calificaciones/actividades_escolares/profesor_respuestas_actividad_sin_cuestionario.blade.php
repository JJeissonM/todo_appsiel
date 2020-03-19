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
								<div title="Doble click para modificar." class="elemento_modificar" data-estudiante_id="{{$estudiante->id}}" data-actividad_id="{{$actividad->id}}" data-respuesta_id="{{$respuestas->id}}">
								{{ $respuestas->calificacion }} </div>
							</td>
						</tr>

					@endforeach <!-- Por cada estudiante -->
                </tbody>
            </table>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="container-fluid">
	        <div class="alert alert-success">
	            NOTA: Cuando se le asigna una calificación a un estudiante, este ya no podrá modificar la respuesta enviada.
				<br>
				Mientras la calificación sea cero (0) ó está vacía, el estudiante podrá seguir modificando la respuesta.
	        </div>
	    </div>
				
	</div>

</div>