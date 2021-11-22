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
			<th>Prom.</th>
			<?php
				$cant_celdas=10;
				for($i=1;$i<=$cant_celdas;$i++){
				echo "<th>&nbsp;</th>";
			 } ?>
			<th>Observaciones</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$j=1;
		?>
		@foreach ($estudiantes as $estudiante){
			<tr>
				<td width="20px" align="center"><?php echo $j; $j++;?></td>
				<td width="200px">
					<div style="font-size: 12px;">
						{{ $estudiante->nombre_completo }}
					</div>
				</td>
				
				@include('calificaciones.incluir.celdas_calificaciones_periodos')

                @for( $i=1; $i<=$cant_celdas; $i++)
                	<td class='celda'>&nbsp;</td>
                @endfor
				<td class='celda2'>&nbsp;</td>
			</tr>	
		@endforeach
		</tbody>
	</table>
</div>