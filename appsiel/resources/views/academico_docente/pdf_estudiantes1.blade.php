<title>Lista de estudiantes</title>
<style>

	img {
		padding-left:30px;
	}

	table {
		width:100%;
		border-collapse: collapse;
	}

th, td {
    border: 1px solid;
}

th {
	background-color: #CACACA;
}

td.celda {
	width: 25px;
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

/* header { position: fixed; top: -60px; left: 0px; right: 0px; background-color: lightblue; height: 50px; } 
*/
footer { position: fixed; top: 5px; left: 0px; right: 0px; height: 20px; }
    

</style>
<?php
$colegio = App\Core\Colegio::where('id','=',Auth::user()->id_colegio)->get();
?>

<div align="rigth">&nbsp;</div>

<!-- <footer><div align="right">{ { date('d-m-Y H:i:s') }}</div></footer> -->
<div class="container">

		@include('banner_colegio')
		
		<b> Curso     :</b> {{ $curso->descripcion }}  <br/>
		<b> Asignatura:</b> {{ $asignatura->descripcion }} <br/>
		<b> Docente:</b> {{ $docente }} <br/>

		<div align="center" style="font-size: 16px; font-weight: bold;">Listado de estudiantes </div>
		<table class="table table-striped" width="100%" border="1">
		<thead>
		<tr>
		<th>Núm.</th>
		<th>Nombre completo</th>
			<?php
				$cant_celdas=16;
				for($i=1;$i<=$cant_celdas;$i++){
				echo "<th>&nbsp;</th>";
			 } ?>
			<th>Observaciones</th>
		</tr>
		</thead>
		<tbody>
		<?php $j=1;
		foreach ($estudiantes as $estudiante){
			?>
			<tr>
				<td width="20px" align="center"><?php echo $j; $j++;?></td>
				<td><div style="font-size: 11px;">{{ $estudiante->nombre_completo }} </div> </td>
                <?php for($i=1;$i<=$cant_celdas;$i++){
                    echo "<td class='celda'>&nbsp;</td>";
                } ?>
				<td class='celda2'>&nbsp;</td>
			</tr>	
		<?php } ?>
		</tbody>
		</table>
</div>