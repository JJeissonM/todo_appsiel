

<?php
	$empresa = App\Core\Empresa::find( Auth::user()->empresa_id );
    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
?>
<table border="0" width="100%">
	<tr>
		<td width="20%">
			<img src="{{ $url }}" height="{{ config('configuracion.alto_logo_formatos') }}" width="{{ config('configuracion.ancho_logo_formatos') }}" style="padding: 2px 10px;" />
		</td>
		<td>
			<div style="font-size: 15px; text-align: center;">
				<br/>
				<b>{{ $empresa->descripcion }}</b>
				<br/>
				<b>{{ config("configuracion.tipo_identificador") }}: </b>
				@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
				{{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} <br/>
				Teléfono(s): {{ $empresa->telefono1 }}
				@if( $empresa->pagina_web != '' )
					<br/>
					<b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
				@endif
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="marco_formulario">
				<div class="container-fluid">		
					<h4 style="width: 100%;text-align: center;">
						<strong> Consolidado de prestaciones sociales </strong>
						<br>
						<b> Fecha: </b> {{ $fecha_final_mes }}
					</h4>

					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th> Grupo Empleados </th>
								<th> Prestación </th>
								<th> Vlr. consol. mes </th>
								<th> Vlr. acumulado </th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$total_valor_consolidado_mes = 0;
								$total_valor_acumulado = 0;
								$grupo_empleado_anterior = '';
								$es_primer_registro = true;
								$hay_mas_registros = true;
								$iteracion = 1;
								$cantidad_registros = count( $movimiento );
								foreach( $movimiento AS $registro )
								{
									$grupo_empleado_actual = $registro->grupo_empleado;

									if( $grupo_empleado_anterior != $grupo_empleado_actual )
									{

										if ( !$es_primer_registro ) 
										{
											echo dibujar_totales2( $total_valor_consolidado_mes, $total_valor_acumulado );
											$total_valor_consolidado_mes = 0;
											$total_valor_acumulado = 0;
										}
										
										$es_primer_registro = false;
										$grupo_empleado_anterior = $grupo_empleado_actual;
										
										//if ( $hay_mas_registros )
										//{
											echo dibujar_etiquetas2( $registro );
										//}
										
										$total_valor_consolidado_mes += (float)$registro->valor_consolidado_mes;
										$total_valor_acumulado += (float)$registro->valor_acumulado;

									}else{
										echo dibujar_etiquetas2( $registro );
										$total_valor_consolidado_mes += (float)$registro->valor_consolidado_mes;
										$total_valor_acumulado += (float)$registro->valor_acumulado;
										$grupo_empleado_anterior = $grupo_empleado_actual;
										$es_primer_registro = false;
									}

									if ( $iteracion == $cantidad_registros )
									{
										$hay_mas_registros = false;
									}

									$iteracion++;

								}

								echo dibujar_totales2( $total_valor_consolidado_mes, $total_valor_acumulado );
							?>
						</tbody>
					</table>
				</div>
			</div>
		</td>
	</tr>
</table>

<?php 
	
	/*

			(object)[ 
                                        'grupo_empleado' => $registro_grupo_empleado->grupo_empleado->descripcion,
                                        'tipo_prestacion' => $registro_prestacion['tipo_prestacion'],
                                        'valor_acumulado_mes_anterior' => $registro_prestacion['valor_acumulado_mes_anterior'],
                                        'valor_consolidado_mes' => $registro_prestacion['valor_consolidado_mes'],
                                        'valor_pagado_mes' => $registro_prestacion['valor_pagado_mes'],
                                        'valor_acumulado' => $registro_prestacion['valor_acumulado']
                                    ];


	*/
	function dibujar_etiquetas2( $registro )
	{
		return '<tr>
					<td>
						' . $registro->grupo_empleado . '
					</td>
					<td>
						' . $registro->tipo_prestacion . '
					</td>
					<td style="text-align:right;">
						' . number_format( $registro->valor_consolidado_mes, 0,',','.') . '
					</td>
					<td style="text-align:right;">
						' . number_format( $registro->valor_acumulado, 0,',','.') . '
					</td>
				</tr>';
	}
	
	function dibujar_totales2( $total_valor_consolidado_mes, $total_valor_acumulado )
	{
		return '<tr>
					<td colspan="2"> </td>
					<td style="text-align:right;"> ' . number_format( $total_valor_consolidado_mes, 0,',','.') . ' </td> 
					<td style="text-align:right;"> ' . number_format( $total_valor_acumulado, 0,',','.') . ' </td> 
				</tr>
				<tr><td colspan="4"></td></tr>';
	}

?>