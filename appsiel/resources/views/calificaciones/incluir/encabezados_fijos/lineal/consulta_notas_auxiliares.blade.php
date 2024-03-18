
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
        @include('calificaciones.incluir.encabezados_fijos.lineal.encabezados_tabla', ['arr_labels_adicionales'=> ['Def.']])
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
</div>