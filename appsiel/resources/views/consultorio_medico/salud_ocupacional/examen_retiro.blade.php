<br><br>
<b>8. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );

	//dd($campos->toArray());
	//dd( $datos );
?>

<p style="border: 1px solid; padding: 5px;">
	<b>{{ $campos[0]['descripcion'] }}</b>
	<br>
	{{ $datos[0]->valor }}
	<br><br>
</p>

<p style="border: 1px solid; padding: 5px;">
	<b>{{ $campos[1]['descripcion'] }}</b>
	<br>
	{{ $datos[1]->valor }}
	<br><br>
</p>

<p>
	<b>Ciudad y fecha:</b> &nbsp;&nbsp; {{ $empresa->ciudad->descripcion }}, {{ $consulta->fecha }}
</p>