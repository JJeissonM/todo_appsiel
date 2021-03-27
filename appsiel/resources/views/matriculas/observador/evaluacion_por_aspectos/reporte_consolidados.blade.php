<div class="container-fluid">
	<div class="marco_formulario">
	    <h4 style="text-align: center;">
	    	CONSOLIDADO DE OBSERVACIÓN ACADEMICA - COMPORTAMENTAL 
			<br> <br> 
            <div class="col-sm-12">
                <b>Periodo de evaluacion:</b><code> {{ $semana_calendario->descripcion }} </code>
                <b>Curso:</b><code>{{ $curso->descripcion }}</code>
                <b>Asignatura:</b><code>{{ $descripcion_asignatura }}</code>
            </div>  
	    </h4>
	    <hr>

		<div class="row">
									
		</div>

		<div class="row">
			<div class="col-sm-12">
				<div class="table-responsive">

					<table class="table table-striped" id="tabla_registros">
						<thead>
							<tr>
								<th>&nbsp;</th>
								@foreach( $tipos_aspectos AS $tipo_aspecto )
									<?php $cant_items_del_tipo = $items_aspectos->where('id_tipo_aspecto', $tipo_aspecto->id )->count() ?>
									<th colspan="{{$cant_items_del_tipo}}" align="center">{{$tipo_aspecto->descripcion}}</th>
								@endforeach
								<th> &nbsp; </th>
								<th> &nbsp; </th>
							</tr>
							<tr>
								<th>Estudiantes</th>
								@foreach( $items_aspectos AS $item_aspecto )
									<th class="celda_C{{$item_aspecto->id}}" align="center" title="{{ $item_aspecto->descripcion }}">
										{{$item_aspecto->descripcion}}
									</th>
								@endforeach
								<th title="Frecuencia"> Frecuencia </th>
								<th> Observación </th>
							</tr>
						</thead>
						<tbody>
							<?php 

								$linea=1;
							?>

							@for( $k = 0; $k < $cantidad_estudiantes; $k++)

								<tr valign="{{$linea}}" title="{{$vec_estudiantes[$k]['nombre']}}">									

									<td width="250px" style="font-size:12px">
										<b> {{$linea}} {{ $vec_estudiantes[$k]['nombre'] }}</b>
									</td>
									
									@for ( $c=1; $c <= $cantidad_items_aspectos; $c++ )
										<td class="celda_C{{$c}}">
											{!! $vec_estudiantes[$k]['valoraciones_aspectos']['valores_item_'.$c] !!}
										</td>
									@endfor

									<td>
										{!! $vec_estudiantes[$k]['valoraciones_aspectos_ids'] !!}
									</td>
									<td>
										{{ $vec_estudiantes[$k]['observacion_descripcion'] }}
									</td>
								</tr>
								<?php $linea++; ?>
							@endfor
							
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="page-break"></div>