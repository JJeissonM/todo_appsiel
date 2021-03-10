<br>
<b>3. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );

	//dd($campos->toArray());
?>

<table class="table table-bordered">
	<tbody>
		<thead>
			<tr>
				<th>COD.</th>
				<th colspan="2">BIOMETRÍA</th>
				<th>COD.</th>
				<th colspan="2">BIOMETRÍA</th>
			</tr>
		</thead>
			
		<tr>
			<td> 30 </td>
			<td> {{ $campos[65]['descripcion'] }} </td>
			<td> </td>
			<td> 33 </td>
			<td> {{ $campos[68]['descripcion'] }} </td>
			<td> </td>
		</tr>
		<tr>
			<td> 31 </td>
			<td> {{ $campos[66]['descripcion'] }} </td>
			<td> </td>
			<td> 34 </td>
			<td> {{ $campos[69]['descripcion'] }} </td>
			<td> </td>
		</tr>
		<tr>
			<td> 32 </td>
			<td> {{ $campos[67]['descripcion'] }} </td>
			<td> </td>
			<td> 35 </td>
			<td> {{ $campos[70]['descripcion'] }} </td>
			<td> </td>
		</tr>
	</tbody>
</table>

<table class="table table-bordered">
	<thead>
		<tr>
			<th colspan="4" rowspan="2">ÓRGANO O SISTEMA</th>
			<th colspan="2"> NORMAL </th>
			<th colspan="3" rowspan="2">ÓRGANO O SISTEMA</th>
			<th colspan="2"> NORMAL </th>
		</tr>
		<tr>
			<th>SÍ</th>
			<th>NO</th>
			<th>SÍ</th>
			<th>NO</th>
		</tr>
	</thead>
	<tbody>
		<?php $h = 37; $i= 0; $j = 62; $k = 25; ?>
		<tr>
			<td rowspan="7">Aspecto General</td>
			<td>{{$h}}</td>
			<td colspan="2">{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td rowspan="4">Corazón</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td colspan="2">{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td colspan="2">{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td colspan="2">{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td colspan="2">{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td rowspan="4">Abdomen</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td colspan="2">{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td colspan="2">{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="13">Órganos de los sentidos</td>
			<td rowspan="5">Ojos</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td rowspan="3">Génito <br> Urinario</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td rowspan="4">Columna</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="2">Oído</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="2">Nariz</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td rowspan="6">Extremidades</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="4">Boca</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<td></td>
			<td></td>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<td></td>
			<td></td>
		</tr>
	</tbody>
</table>