<div class="row">
	<div class="col-md-12">
		<div class="table-responsive" id="table_content">
			<table class="table table-bordered table-striped" id="myTable">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Respuesta enviada</th>
                        <th>Fecha envio</th>
                        <th>Archivo adjunto</th>
                        <th>Anotación</th>
                    </tr>
                </thead>
                <tbody>
                	@foreach($estudiantes as $estudiante)

                		<?php

							// Se obtienen las respuestas enviadas de cada estudiante
							$respuestas = App\Cuestionarios\RespuestaCuestionario::where(['actividad_id'=>$actividad->id,'estudiante_id'=>$estudiante->id])->get()->first();

							if( is_null( $respuestas ) )
				            {   
				                $respuestas = (object)['id'=>0,'respuesta_enviada'=>'','calificacion'=>'','adjunto'=>'','updated_at'=>''];
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
								@if( $respuestas->updated_at != '')
									<?php
										$fecha = explode(" ", $respuestas->updated_at);
									?>
									Fecha: {{ $fecha[0] }}
									<br>
									Hora: {{ $fecha[1] }}
								@endif
							</td>
							<td>
								@if( $respuestas->adjunto != '' )
									&nbsp;&nbsp;
									<a href="{{ config('configuracion.url_instancia_cliente').'/storage/app/img/adjuntos_respuestas_estudiantes/'.$respuestas->adjunto }}" class="btn btn-info btn-sm" target="_blank"> <i class="fa fa-file"></i> {{ $respuestas->adjunto }} </a>
								@endif
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
	        <div class="alert alert-warning">
	            NOTA: Cuando se le registra una anotación a un estudiante, este ya no podrá modificar la respuesta enviada.
				<br>
				Mientras la anotación este vacía, el estudiante podrá seguir modificando la respuesta.
	        </div>
	    </div>
				
	</div>

</div>