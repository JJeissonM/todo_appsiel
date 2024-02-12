<table class="table table-striped">
	<tr>
		<td>
			<?php  
				$unwanted_array = array('À'=>'A', 'Á'=>'A', 'È'=>'E', 'É'=>'E',
                                'Ì'=>'I', 'Í'=>'I', 'Ò'=>'O', 'Ó'=>'O', 'Ù'=>'U',
                                'Ú'=>'U', 'à'=>'a', 'á'=>'a', 'è'=>'e', 'é'=>'e', 'ì'=>'i', 'í'=>'i', 'Ñ'=>'N', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ù'=>'u', 'ú'=>'u' );
			?>
			<div class="container-fluid" style="font-size: 0.8em !important;">
				@for($k=0;$k < count($estudiantes) ;$k++)

					<!-- ENCABEZADO -->
					@include('banner_colegio')

					<!-- TITULOS -->
					<div align="center"> <b> Listado de estudiantes </b> </div>
					<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
					<b>Curso: </b> {{ $estudiantes[$k]['curso'] }} 
					
					<!-- CONTENIDOS -->
					<table id="myTable" class="table table-striped">
						<thead>
							<tr>
								<th style="border: solid 1px;">Num.</th>
								<th style="border: solid 1px;">Nombre completo</th>
								<?php
									$cant_celdas=8;
								?>
								@for( $i=1; $i<=$cant_celdas; $i++)
									<th style="border: solid 1px;">  </th>
								@endfor
								<th style="border: solid 1px;">Observaciones</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$j=1;
								foreach ($estudiantes[$k]['listado'] as $registro){
									$nombre_completo = $registro->nombre_completo;

    								$nombre_completo = strtr( $nombre_completo, $unwanted_array );
							?>
								<tr>
									<td class='celda' width="20px" align="center" style="font-size: {{$tam_letra}}mm; border: solid 1px;">
										<?php echo $j; $j++;?>
									</td>
									<td style="font-size: {{$tam_letra}}mm; border: solid 1px;" width="300px" class='celda' style="border: solid 1px;">
										{{ $nombre_completo }}
									</td>
									@for($i=1;$i<=$cant_celdas;$i++)
										<td style="border: solid 1px;"></td>
									@endfor
									<td style="border: solid 1px;"></td>
								</tr>	
							<?php } ?>
						</tbody>
					</table>
					@if( isset($estudiantes[$k+1]))
						<div class="page-break"></div>
					@endif
					
				@endfor
			</div>
		</td>
	</tr>
</table>