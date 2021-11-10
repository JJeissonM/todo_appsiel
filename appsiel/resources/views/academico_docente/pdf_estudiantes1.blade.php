<div class="container-fluid" style="font-size: 0.8em !important;">
	<!-- ENCABEZADO -->
	@include('banner_colegio')
	
	<table class="table table-bordered" width="100%" border="1">
		<tr>
			<td><b> Curso     :</b> {{ $curso->descripcion }}</td>
			<td><b> Asignatura:</b> {{ $asignatura->descripcion }}</td>
			<td><b> Docente:</b> {{ $docente }}</td>
		</tr>
	</table>

	<div align="center" style="font-size: 16px; font-weight: bold;">Listado de estudiantes </div>
	
	<table class="table table-striped" width="100%" border="1">
		<thead>
		<tr>
			<th>Núm.</th>
			<th>Nombre completo</th>
	        @foreach($periodos as $periodo)
	            <th>
	                <div class="checkbox">
	                  <label>P{{$periodo->numero}}</label>
	                </div>
	            </th>
	        @endforeach
			<?php
				$cant_celdas=10;
				for($i=1;$i<=$cant_celdas;$i++){
				echo "<th>&nbsp;</th>";
			 } ?>
			<th>Observaciones</th>
		</tr>
		</thead>
		<tbody>
		<?php $j=1;
		foreach ($estudiantes as $estudiante){

				$prom_final=0;
                $n = 0;
			?>
			<tr>
				<td width="20px" align="center"><?php echo $j; $j++;?></td>
				<td width="200px"><div style="font-size: 12px;">{{ $estudiante->nombre_completo }} </div> </td>
				@foreach($periodos as $periodo)
                    <td width="20px" align="center">
                        @php 
                            // Calcular calificacion promedio del estudiante en la Collection calificaciones
                            $prom = $calificaciones->whereLoose('estudiante_id',$estudiante->id_estudiante)
                            			->whereLoose('periodo_id',$periodo->id)
                            			->avg('calificacion');

                            //dd( $calificaciones, $estudiante->id_estudiante, $periodo->id,  $prom );
                            $text_prom = '';
                            $color_text = 'black';
                            /**/if ( !is_null($prom) ) 
                            {
                                $prom_final += $prom;
                                $text_prom = number_format($prom, config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
                                $n++;

                                if ( $prom <= $tope_escala_valoracion_minima ) {
                                    $color_text = 'red';
                                }
                            }                               
                        @endphp
                        <span style="color: {{$color_text}};font-size: 12px; padding: 1px;"> {{ $text_prom }}</span>
                    </td>
                @endforeach
                <?php for($i=1;$i<=$cant_celdas;$i++){
                    echo "<td class='celda'>&nbsp;</td>";
                } ?>
				<td class='celda2'>&nbsp;</td>
			</tr>	
		<?php } ?>
		</tbody>
	</table>
</div>