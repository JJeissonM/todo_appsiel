<table class="encabezado">		
	<tr>
		<td colspan="3" style="text-align: center; font-weight:bold;">INFORME ACADÉMICO</td>
	</tr>
	<tr>									
		@if($colegio->maneja_puesto=="Si")

			@if( !is_null($registro->observacion) )
				
				<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $registro->estudiante->tercero->descripcion }}</td>
				
				@if( $registro->observacion->puesto == "" )
					<td> <b> &nbsp; </b> </td>
				@else
					<td>
						<span class="etiqueta"> Puesto:  </span> 
						{{ $registro->observacion->puesto }}
						&nbsp;&nbsp;
						<span class="etiqueta"> Prom. Acad.:  </span> 
						<?php
							$suma_calificaciones = 0;
							$n_prom_acad = 0;
							foreach ( $lineas_cuerpo_boletin as $datos_linea_prom )
							{
								$suma_calificaciones += $datos_linea_prom->valor_calificacion;
								$n_prom_acad++;
							}

							$prom_academico = 0;
							if ($n_prom_acad != 0) {
								$prom_academico = $suma_calificaciones / $n_prom_acad;

								$escala_valoracion_prom_academico = App\Calificaciones\EscalaValoracion::get_escala_segun_calificacion( $prom_academico, $periodo->periodo_lectivo_id );
							
								$lbl_escala_valoracion_prom_academico = '';
								if ( $escala_valoracion_prom_academico )
								{
									$lbl_escala_valoracion_prom_academico = ' (' . $escala_valoracion_prom_academico->nombre_escala . ')';
								}

							}
						?>
						{{ number_format( $prom_academico, (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.' ) }} {{ $lbl_escala_valoracion_prom_academico }} 
					</td>
				@endif

			@else
				<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $registro->estudiante->tercero->descripcion }}</td>
			@endif

		@else
			<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $registro->estudiante->tercero->descripcion }}</td>
		@endif
		
	</tr>
	<tr>
		<td><span class="etiqueta">Periodo/Año:</span> {{ $periodo->descripcion }} &#47;  {{ explode( "-", $periodo->fecha_desde )[0] }}</td>
		<td><span class="etiqueta">Curso:</span> {{ $curso->descripcion }}</td>
		<td><span class="etiqueta">Ciudad:</span> {{ $colegio->ciudad }}</td>
	</tr>
</table>