<style>

	img {
		padding-left:30px;
	}

	table {
		width:100%;
	}

	table.encabezado{
		padding:5px;
		border: 1px solid;
	}

	table.banner{
		font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
		font-style: italic;
		font-size: larger;
		border: 1px solid;
	}

	table.contenido td {
		border: 1px solid;
	}

	th {
		background-color: #E0E0E0;
		border: 1px solid;
	}

	ul{
		padding:0px;
		margin:0px;
	}

	li{
		list-style-type: none;
	}

	span.etiqueta{
		font-weight: bold;
		display: inline-block;
		width: 100px;
		text-align:right;
	}

	.page-break {
		page-break-after: always;
	}
</style>

@if( !is_null($estudiantes) )
	@foreach($estudiantes as $estudiante)

		{!! $banner !!}
				
		<table class="encabezado">
			<tr>
				<?php 
					$observacion = DB::table('observaciones_boletines')
											->where(['id_colegio'=>$colegio->id,'anio'=>$anio,
													'id_periodo'=>$periodo->id,'curso_id'=>$curso->id,
													'id_estudiante'=>$estudiante->id_estudiante])
											->get(); 
				?>
													
				@if($colegio->maneja_puesto=="S")
					@if($curso->maneja_calificacion==1)
						@if( !is_null($observacion) )
							@if($observacion[0]->puesto=="")
								<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombres }} {{ $estudiante->apellido1 }} {{ $estudiante->apellido2 }}</td>
								<td><span class="etiqueta"> ¡¡Puesto </span> No calculado!! </td>
							@else
								<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombres }} {{ $estudiante->apellido1 }} {{ $estudiante->apellido2 }}</td>
								<td><span class="etiqueta"> {{ $observacion[0]->puesto }} </span> &nbsp; </td>
							@endif
						@else
							<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombres }} {{ $estudiante->apellido1 }} {{ $estudiante->apellido2 }}</td>
						@endif
					@else
						<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombres }} {{ $estudiante->apellido1 }} {{ $estudiante->apellido2 }}</td>
					@endif
				@else
					<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombres }} {{ $estudiante->apellido1 }} {{ $estudiante->apellido2 }}</td>
				@endif
				
			</tr>
			<tr>
				<td><span class="etiqueta">Periodo/A&ntilde;o:</span> {{ $periodo->descripcion }} &#47;  {{ $anio }}</td>
				<td><span class="etiqueta">Curso:</span> {{ $curso->descripcion }}</td>
				<td><span class="etiqueta">Ciudad:</span> {{ $colegio->ciudad }}</td>
			</tr>
		</table>
				
		<table class="contenido">
			<thead>
				<tr>
					<th>Asignaturas</th>
					<th>I.H.</th>
					@if($curso->maneja_calificacion==1)
						<th>Calificación</th>
					@endif
					<th>Logros</th>
				</tr>
			</thead>
			<tbody>
				@foreach($asignaturas as $asignatura)
					<?php
					// Se llama a la calificacion de cada asignatura
					$calificacion = App\Calificaciones\Calificacion::where(['id_colegio'=>$colegio->id,'anio'=>$anio,
														'id_periodo'=>$periodo->id,'curso_id'=>$curso->id,
														'id_estudiante'=>$estudiante->id_estudiante,'id_asignatura'=>$asignatura->id])
														->get()->first();
					
					// Se obtiene el texto de la calificación
					$desempeno="";
					$n_nom_logros=0;
					$vec_logros[0] = 0;
					if( !is_null($calificacion) )
					{
						$desempeno = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id', $periodo->periodo_lectivo_id)
															->where('calificacion_minima','<=',$calificacion->calificacion)
															->where('calificacion_maxima','>=',$calificacion->calificacion)
															->value('nombre_escala');
															
						$n_nom_logros=strlen($calificacion->logros); //Longitud de la cadena de logros
									$vec_logros = explode(",",$calificacion->logros);
					}
					

					?>
					<tr style="font-size: {{$tam_letra}}mm;">
						<td> {{ $asignatura->descripcion }}</td>
						<td align="center"> {{ $asignatura->intensidad_horaria }}</td>
						@if($curso->maneja_calificacion==1)
							@if($asignatura->maneja_calificacion==1)
								@if( !is_null($calificacion) )
									<td align="center"> {{ $calificacion->calificacion }} ({{ $desempeno }}) </td>
								@else
									<td align="center"> &nbsp; </td>
								@endif
							@else
								<td align="center"> &nbsp; </td>
							@endif
						@endif							
						<td>
							<ul>
							<?php
								for($j=0;$j<count($vec_logros);$j++){
									$logro = App\Calificaciones\Logro::where(['codigo'=>$vec_logros[$j],'id_colegio'=>$colegio->id])->get()->first();
									if( is_null($logro) )
									{
										?><li style="text-align: justify;">{{ $vec_logros[$j] }} </li><?php
									}else{
										?><li style="text-align: justify;">{{ $logro->descripcion }} </li><?php
									}
								}
							?>
							</ul>
						</td>
					</tr>
				@endforeach {{--  Asignaturas --}}
				<tr style="font-size: {{$tam_letra}}mm;"> 
					@if($curso->maneja_calificacion)
						<td colspan="4">
					@else
						<td colspan="3">
					@endif
						<b> Observaciones: </b>
						<br/>&nbsp;&nbsp;
						@if( !is_null($observacion) )
							{{ $observacion[0]->observacion }}
						@endif
						</td>
				</tr>
			</tbody>
		</table>

		<br/><br/><br/>

		<table border="0">
			<tr>
				<td width="50px"> &nbsp; </td>
				<td align="center">	_____________________________ </td>
				<td align="center"> &nbsp;	</td>
				<td align="center">	_____________________________ </td>
				<td width="50px">&nbsp;</td>
			</tr>
			<tr>
				<td width="50px"> &nbsp; </td>
				<td align="center">	{{ $colegio->piefirma1 }} </td>
				<td align="center"> &nbsp;	</td>
				<td align="center">	{{ $colegio->piefirma2 }} </td>
				<td width="50px">&nbsp;</td>
			</tr>
		</table>

		
		<div class="page-break"></div>


	@endforeach {{-- Estudiante --}}
@else
	No existen datos.
@endif