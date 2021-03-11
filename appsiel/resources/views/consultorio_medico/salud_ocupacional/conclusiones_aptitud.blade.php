<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );

	//dd($campos->toArray());
	//dd( [ $campos->toArray(), $datos] );
?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th colspan="2" align="center">CONCEPTO DE APTITUD MÉDICA OCUPACIONAL</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td> {{ strtoupper($datos[0]->valor) }} </td>
			<td>
				<?php 
					$controles = c_get_marcacion( $datos[0]->valor );
				?>
				Sin patología Aparente <div style="display: inline; width: 25px; height: 25px; text-align: center;"> <?php echo $controles->sin_patologia ?>  </div>
				<br><br>
				Con patología Aparente <div style="display: inline; width: 25px; height: 25px; text-align: center;"> <?php echo $controles->con_patologia; ?>  </div>
				<br><br>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<b>{{ $campos[1]['descripcion'] }}</b>: {{ $datos[1]->valor }}
			</td>					
		</tr>
	</tbody>
</table>

<table class="table table-bordered">
	<thead>
		<tr>
			<th colspan="2" align="center">
				{{ $campos[2]['descripcion'] }}
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="2">
				{{ $datos[2]->valor }}
			</td>
		</tr>
	</tbody>
</table>

<?php 
	
	function c_get_marcacion( $valor )
	{
		$marcacion = (object)[ 
								'sin_patologia' => '<input type="checkbox" style="transform: scale(1.5);">',
								'con_patologia' => '<input type="checkbox" style="transform: scale(1.5);" checked="checked">'
								];

		if ( $valor == 'Apto Sin Limitaciones' || $valor == '--' )
		{
			$marcacion = (object)[ 
									'sin_patologia' => '<input type="checkbox" style="transform: scale(1.5);" checked="checked">',
									'con_patologia' => '<input type="checkbox" style="transform: scale(1.5);">'
									];
		}

		return $marcacion;
	}
?>