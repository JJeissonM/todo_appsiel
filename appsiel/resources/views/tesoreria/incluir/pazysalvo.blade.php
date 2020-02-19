<table style="border: 1px solid gray; border-radius: 6px; margin-left: 5px;">
	<tr> 
		<td align="center"> 
			<?php
				$url = '../storage/app/logos_empresas/'.$empresa->imagen;
			?>
			<img alt="escudo.jpg" src="{{ $url.'?'.rand(1,1000) }}" style="width: 90px; height: 90px;" /> 
		</td> 
		<td align="center" style="font-size: 12px;">
			{{ $empresa->descripcion }} <br/>
			NIT: {{ number_format($empresa->numero_identificacion, 0, ',', '.') }} - {{ $empresa->digito_verificacion }}<br/>
			{{ $colegio->slogan }} <br/>
			Resolución {{ $colegio->resolucion }} <br/>
			{{ $colegio->direccion }} {{ $colegio->telefonos }} {{ $colegio->ciudad }}
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div align="center">
				<br>
				<b style="font-size: 1.2em;">P A Z &nbsp;&nbsp;&nbsp; Y &nbsp;&nbsp;&nbsp;    S A L V O</b>
			</div>
		</td>
	</tr>
	<tr>
		<?php 
			$anio = '20'.substr($codigo_matricula, 0, 2);
		?>
		<td colspan="2">
			<div style="line-height: 1.5; text-align: justify; text-justify: inter-word; font-size: 17px;">
				<b>Nombre del alumno:</b> &nbsp;&nbsp; {{ $nombre_completo }}
				<br>
				<b>Curso:</b>&nbsp;&nbsp;{{ $nom_curso }}&nbsp;&nbsp;
				<b>Jornada:</b>&nbsp;&nbsp; Mañana &nbsp;&nbsp;
				<br>
				<b>Matrícula No.:</b>&nbsp;&nbsp;{{ $codigo_matricula }}&nbsp;&nbsp;
				<b>Año:</b>&nbsp;&nbsp;{{ $anio }}&nbsp;&nbsp;&nbsp;
				<br><br>
				<b>Fecha:</b>&nbsp;&nbsp;______________&nbsp;&nbsp;
				<br>
				<b>Diligenciado por:</b>&nbsp;&nbsp;________________________&nbsp;&nbsp;
			</div>
		</td>
	</tr>
</table>
             