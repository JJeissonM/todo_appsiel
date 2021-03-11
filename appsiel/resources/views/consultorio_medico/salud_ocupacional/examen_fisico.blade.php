<br>
<b>3. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$url_imagen = asset('assets/images/icono-check.png');

	//dd( $datos );
?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th width="25px">COD.</th>
			<th colspan="2">BIOMETRÍA</th>
			<th width="25px">COD.</th>
			<th colspan="2">BIOMETRÍA</th>
		</tr>
	</thead>
	<tbody>			
		<tr>
			<td> 30 </td>
			<td> {{ $campos[65]['descripcion'] }} </td>
			<td> {{ $datos[65]->valor }} </td>
			<td> 33 </td>
			<td> {{ $campos[68]['descripcion'] }} </td>
			<td> {{ $datos[68]->valor }} </td>
		</tr>
		<tr>
			<td> 31 </td>
			<td> {{ $campos[66]['descripcion'] }} </td>
			<td> {{ $datos[66]->valor }}</td>
			<td> 34 </td>
			<td> {{ $campos[69]['descripcion'] }} </td>
			<td> {{ $datos[69]->valor }} </td>
		</tr>
		<tr>
			<td> 32 </td>
			<td> {{ $campos[67]['descripcion'] }} </td>
			<td> {{ $datos[67]->valor }} </td>
			<td> 35 </td>
			<td> {{ $campos[70]['descripcion'] }} </td>
			<td> {{ $datos[70]->valor }} </td>
		</tr>
	</tbody>
</table>

<div class="page-break"></div>

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
			<td rowspan="7" colspan="2">Aspecto General</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td rowspan="4">Corazón</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td rowspan="4">Abdomen</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="13">Órganos de los sentidos</td>
			<td rowspan="5">Ojos</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td rowspan="3">Génito <br> Urinario</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td rowspan="4">Columna</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="2">Oído</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="2">Nariz</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td rowspan="6">Extremidades</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="4">Boca</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="2" colspan="2">Cuello</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td rowspan="4">Neurológico</td>
			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td rowspan="3" colspan="2">Tórax y Pulmones</td>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
		<?php $h++; $i++; $j++; $k++; ?>
		<tr>
			<td>{{$h}}</td>
			<td>{{ $campos[$i]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$i]->valor, $url_imagen ); ?>

			<td>{{$j}}</td>
			<td>{{ $campos[$k]['descripcion'] }}</td>
			<?php echo get_celdas_si_no( $datos[$k]->valor, $url_imagen ); ?>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="11">
				{{ $campos[71]['descripcion'] }}:
				<br>
				{{ $datos[71]->valor }}
			</td>
		</tr>
	</tfoot>
</table>
<table class="table table-bordered">
	<thead>
		<tr>
			<th colspan="6">Columna Vertebral</th>
		</tr>
		<tr>
			<th colspan="2">&nbsp;</th>
			<th>COLUMNA CERVICAL</th>
			<th>COLUMNA DORSAL</th>
			<th>COLUMNA LUMBAR</th>
			<th>OBSERVACIONES</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="2">CURVA</td>
			<td align="center"> {{ $datos[50]->valor }} </td>
			<td align="center"> {{ $datos[51]->valor }} </td>
			<td align="center"> {{ $datos[52]->valor }} </td>
			<td align="center"> {{ $datos[53]->valor }} </td>
		</tr>
		<tr>
			<td colspan="2">LORDOSIS</td>
			<td align="center"> {{ $datos[54]->valor }} </td>
			<td align="center"> {{ $datos[55]->valor }} </td>
			<td align="center"> {{ $datos[56]->valor }} </td>
			<td align="center"> {{ $datos[57]->valor }} </td>
		</tr>
		<tr>
			<td colspan="2">CIFOSIS</td>
			<td align="center"> {{ $datos[58]->valor }} </td>
			<td align="center"> {{ $datos[59]->valor }} </td>
			<td align="center"> {{ $datos[60]->valor }} </td>
			<td align="center"> {{ $datos[61]->valor }} </td>
		</tr>
		<tr>
			<td rowspan="2">ESCOLIOSIS</td>
			<td width="15px"> DER</td>
			<td align="center"> {{ $datos[62]->valor }} </td>
			<td align="center"> {{ $datos[63]->valor }} </td>
			<td align="center"> {{ $datos[64]->valor }} </td>
			<td align="center"> {{ $datos[65]->valor }} </td>
		</tr>
		<tr>
			<td width="15px"> IZQ</td>
			<td align="center"> {{ $datos[66]->valor }} </td>
			<td align="center"> {{ $datos[67]->valor }} </td>
			<td align="center"> {{ $datos[68]->valor }} </td>
			<td align="center"> {{ $datos[69]->valor }} </td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="6">
				N. NORMAL &nbsp;&nbsp;&nbsp; A. AUMENTADA &nbsp;&nbsp;&nbsp; D. DISMINUIDA
			</td>
		</tr>
	</tfoot>
</table>

<?php
	function get_celdas_si_no( $valor, $url_imagen )
	{
		$celdas = '<td align="center"><img src="' . $url_imagen . '"  height="15" style="margin-left:-15px;"></td><td></td>';
		if ( $valor == 'No' )
		{
			$celdas = '<td></td><td><img src="' . $url_imagen . '"  height="15" style="margin-left:-15px;"></td>';
		}
		return $celdas;
	}
?>