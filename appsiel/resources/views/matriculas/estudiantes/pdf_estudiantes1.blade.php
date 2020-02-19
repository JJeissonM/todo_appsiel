<style>

	img {
		padding-left:30px;
	}

	table {
		width:100%;
		border-collapse: collapse;
	}

	table.banner{
		border: 0px;
		border-spacing:  0;
	}

th {
	background-color: #CACACA;
}

td.celda {
	border: 1px solid;
}

td.celda2 {
	width: 150px;
	border: 1px solid;
}

h3 {
	text-align:center;
}

.page-break {
    page-break-after: always;
}
</style>
<?php
	$colegio = App\Core\Colegio::where('empresa_id','=', Auth::user()->empresa_id )
                    ->get();
?>
<div class="container">
	@for($k=0;$k < count($estudiantes) ;$k++)

		<!-- ENCABEZADO -->
		@include('banner_colegio')

		<!-- TITULOS -->
		<div align="center"> <b> Listado de estudiantes </b> </div>
		<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
		<b>Curso: </b> {{ $estudiantes[$k]['curso'] }} 
		
		<!-- CONTENIDOS -->
		<table class="principal_cuerpo" width="100%">
		<thead>
		<tr>
		<th>Núm.</th>
		<th>Nombre completo</th>
			<?php
				$cant_celdas=8;
				for($i=1;$i<=$cant_celdas;$i++){
				echo "<th>&nbsp;</th>";
			 } ?>
			<th>Observaciones</th>
		</tr>
		</thead>
		<tbody>
		<?php $j=1;
		foreach ($estudiantes[$k]['listado'] as $registro){
				$nombre_completo = $registro->nombre_completo;
			?>
			<tr>
				<td class='celda' width="20px" align="center"><?php echo $j; $j++;?></td>
				<td style="font-size: {{$tam_letra}}mm;" width="300px" class='celda'>
					{{ $nombre_completo }}
				</td>
                @for($i=1;$i<=$cant_celdas;$i++)
                    <td class='celda'>&nbsp;</td>
                @endfor
				<td class='celda2'>&nbsp;</td>
			</tr>	
		<?php } ?>
		</tbody>
		</table>
		<div class="page-break"></div>
		
	@endfor
</div>