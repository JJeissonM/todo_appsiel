<div class="container-fluid" style="font-size: 0.8em !important;">
	@for($k=0;$k < count($estudiantes) ;$k++)

		<!-- ENCABEZADO -->
		@include('banner_colegio')

		<!-- TITULOS -->
		<div align="center"> <b> Listado de estudiantes </b> </div>
		<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
		<b>Curso: </b> {{ $estudiantes[$k]['curso'] }} 
		
		<!-- CONTENIDOS -->
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Núm.</th>
					<th>Nombre completo</th>
					<?php
						$cant_celdas=8;
					?>
					@for( $i=1; $i<=$cant_celdas; $i++)
						<th> &nbsp; </th>
					@endfor
					<th>Observaciones</th>
				</tr>
			</thead>
			<tbody>
				<?php $j=1;
				foreach ($estudiantes[$k]['listado'] as $registro){
						$nombre_completo = $registro->nombre_completo;
					?>
					<tr>
						<td class='celda' width="20px" align="center" style="font-size: {{$tam_letra}}mm;"><?php echo $j; $j++;?></td>
						<td style="font-size: {{$tam_letra}}mm;" width="300px" class='celda'>
							{{ $nombre_completo }}
						</td>
		                @for($i=1;$i<=$cant_celdas;$i++)
		                    <td>&nbsp;</td>
		                @endfor
						<td>&nbsp;</td>
					</tr>	
				<?php } ?>
			</tbody>
		</table>
		@if( isset($estudiantes[$k+1]))
			<div class="page-break"></div>
		@endif
		
	@endfor
</div>