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