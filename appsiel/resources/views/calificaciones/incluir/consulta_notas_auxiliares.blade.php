
<?php 
	$cantidad_calificaciones = 16;
?>	
<p style="text-align: center; font-size: 15px; font-weight: bold;">
    
    Consulta de calificaciones auxiliares por asignatura
    <br/>  
    Año Lectivo: {{ $periodo_lectivo->descripcion }} 
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Curso: {{ $curso->descripcion }}
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Periodo: {{ $periodo->descripcion }}
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Asignatura: {{ $asignatura->descripcion }}
 </p>
<hr>

<div class="table-responsive">
	<table class="table table-striped" id="tabla_registros">
		<thead>
			<tr>
				<th>Estudiantes</th>
				@for($k=1; $k < $cantidad_calificaciones; $k++)
					<th class="celda_C{{$k}}">
						<button class="btn btn-default btn-xs encabezado_calificacion" value="C{{$k}}">C{{$k}}</button>
					</th>
				@endfor
				<th>
					Def.
				</th>
			</tr>
		</thead>
		<tbody>
			<?php 

				$linea = 1;
			?>

			@for( $k = 0; $k < $cantidad_estudiantes; $k++)

				<tr>

					<td width="250px" style="font-size:12px">
						<b> {{$linea}} {{ $vec_estudiantes[$k]['nombre'] }}</b>
					</td>				
					
					@for ($c=1; $c < $cantidad_calificaciones; $c++)
						<td class="celda_C{{$c}}">
							{{$vec_estudiantes[$k]['C'.$c]}}
						</td>
					@endfor

					<td>
						{{ $vec_estudiantes[$k]['calificacion'] }}
					</td>
				</tr>
				<?php $linea++; ?>
			@endfor
			
		</tbody>
	</table>

	<h4>Detalle de encabezados</h4>
	<hr>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>
					Encabezado
				</th>
				<th>
					Descripción actividad
				</th>
				<th>
					Fecha
				</th>
				<th>
					Peso (%)
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach($encabezados_calificaciones AS $encabezado)
				<tr>
					<td align="center">{{ $encabezado->columna_calificacion }}</td>
					<td align="center">{{ $encabezado->descripcion }}</td>
					<td align="center">{{ $encabezado->fecha }}</td>
					<td align="center">{{ $encabezado->peso }}</td>
				</tr>
			@endforeach
		</tbody>		
	</table>
</div>