<?php 
	$url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;
?>
<table class="encabezado">
	<tr rowspan="3">
		<td> <img alt="foto.jpg" src="{{ asset( config('configuracion.url_instancia_cliente').'/storage/app/fotos_terceros/'.$estudiante->imagen ) }}" style="width: 30px; height: 80px;" /> </td>
	</tr>
	<tr>											
		<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $nombre_completo }}</td>
	</tr>
	<tr>
		<td><span class="etiqueta">Periodo/Año:</span> {{ $periodo->descripcion }} &#47;  {{ $anio }}</td>
		<td><span class="etiqueta">Curso:</span> {{ $curso->descripcion }}</td>
		<td><span class="etiqueta">Ciudad:</span> {{ $colegio->ciudad }}</td>
	</tr>
</table>