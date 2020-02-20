<style>

	img {
		padding-left:30px;
	}

	table {
		width:100%;
		border-collapse: collapse;
	}

	table.banner{
		border: 0px solid;
		border-collapse: collapse;
		border-spacing:  0;
	}

	table.banner{
		font-size: 1.2em;
	}

th, td {
    border-bottom: 1px solid gray;
}

th {
	background-color: #CACACA;
}

td.cuadrito {
	border-left: 1px solid gray;
	border-right: 1px solid gray;
}

h3 {
	text-align:center;
}

div.recuadro{
	border: 1px solid gray;
}

.matriz_dofa td{
	width: 50%;
}

.matriz_dofa td div{
	height: 300px;
	width: 100%;
}

.matriz_dofa h5 b{
	vertical-align: middle;
}	

.gota1 .lista{
	background-color: yellow;
	/*border-radius: 0 20% 0 20%;*/
}

.gota1 h5{
	background-color: yellow; 
	height: 30px; 
	width: 30%; 
	margin-bottom: -10px; 
	border-radius: 10px 10px 0 0;
	text-align: center;
}

.gota2 .lista{
	background-color: cyan;
	/*border-radius: 20% 0 20% 0;*/
}

.gota2 h5{
	background-color: cyan; 
	height: 30px; 
	width: 30%; 
	margin-bottom: -10px;
	border-radius: 10px 10px 0 0;
	text-align: center;
}

.gota3 .lista{
	background-color: #61de61;
	/*border-radius: 20% 0 20% 0;*/
}

.gota3 h5{
	background-color: #61de61; 
	height: 30px; 
	width: 30%; 
	margin-top: -10px;
	border-radius: 0 0 10px 10px;
	text-align: center;
}

.gota4 .lista{
	background-color: #f97672;
	/*border-radius: 0 20% 0 20%;*/
}

.gota4 h5{
	background-color: #f97672; 
	height: 30px; 
	width: 30%; 
	margin-top: -10px;
	border-radius: 0 0 10px 10px;
	text-align: center;
}

.page-break {
    page-break-after: always;
}
</style>

<div class="container" style="width: 100%;">

	<table class="banner" width="100%" >
	    <tr>
	        <td width="250px">
	            <img src="{{ asset(config('configuracion.url_instancia_cliente').'/storage/app/escudos/'.$colegio->imagen.'?'.rand(1,1000)) }}" width="160px" height="160px" />
	        </td>

	        <td align="center">
	            <b>{{ $colegio->descripcion }}</b><br/>
	            <b>{{ $colegio->slogan }}</b><br/>
	            Resolución No. {{ $colegio->resolucion }}<br/>
	            {{ $colegio->direccion }}<br/>
	            Teléfonos: {{ $colegio->telefonos }}<br/>
	        </td>
	    </tr>
	</table>
	
	<h2 align="center">OBSERVADOR Y CONVIVENCIA DEL ALUMNO</h2>

	@include('matriculas.estudiantes.ficha_estudiante')

	<div class="page-break"></div>

	<br/>
	<h2> Evaluación por aspectos </h2>
	<hr>
	<div class="form-group">
		<div class="alert alert-info">
		  <strong>Convenciones</strong> <br/> 
		  S= Siempre    CS= Casi siempre      AV= Algunas veces   N= Nunca
		</div>
	</div>

	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable" width="100%">
			<thead>
				<tr>
					<th rowspan="2">No.</th>
					<th rowspan="2">ASPECTOS</th>
					<th colspan="4">Periodos</th>
				</tr>
				<tr>
					<th>1°</th>
					<th>2°</th>
					<th>3°</th>
					<th>4°</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($tipos_aspectos as $tipo_aspecto)
					<tr><td colspan="6"><b>{{ $tipo_aspecto->descripcion }}</b></td></tr>
					@php $aspectos=App\Matriculas\CatalogoAspecto::where('id_tipo_aspecto','=',$tipo_aspecto->id)->orderBy('orden','ASC')->get() @endphp
					@foreach ($aspectos as $aspecto)
						<?php 
							$aspecto_estudiante=App\Matriculas\AspectosObservador::where('id_aspecto','=',$aspecto->id)->where('id_estudiante','=',$estudiante->id)->where('fecha_valoracion','like',date('Y').'%')->get();
							if(count($aspecto_estudiante)>0){
								$val_per1 = $aspecto_estudiante[0]->valoracion_periodo1;
								$val_per2 = $aspecto_estudiante[0]->valoracion_periodo2;
								$val_per3 = $aspecto_estudiante[0]->valoracion_periodo3;
								$val_per4 = $aspecto_estudiante[0]->valoracion_periodo4;
								$aspecto_estudiante_id = $aspecto_estudiante[0]->id;
							}else{
								$val_per1 = "";
								$val_per2 = "";
								$val_per3 = "";
								$val_per4 = "";
								$aspecto_estudiante_id="";
							}
						?>
						<tr>
							<td>{{ $aspecto->orden }}</td>
							<td>{{ $aspecto->descripcion }}</td>
							<td class="cuadrito">{{ $val_per1 }}</td>
							<td class="cuadrito">{{ $val_per2 }}</td>
							<td class="cuadrito">{{ $val_per3 }}</td>
							<td class="cuadrito">{{ $val_per4 }}</td>
						</tr>
					@endforeach
				@endforeach
			</tbody>

		</table>
	</div>

	<br/><br/>
	
	<div class="page-break"></div>
	
	<h2> Novedades y anotaciones </h2>
	<hr>
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable" width="100%">
			<thead>
				<tr>
					<th>Fecha</th>
					<th>Periodo</th>
					<th>Novedad</th>
					<th>Profesor</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($novedades as $novedad)
					@php $periodo = App\Calificaciones\Periodo::find($novedad->id_periodo) @endphp
					@php $usuario = App\User::where('email',$novedad->creado_por)->value('name') @endphp
					<tr>
						<td>{{ $novedad->fecha_novedad }}</td>
						<td>{{ $periodo->descripcion }}</td>
						<td>{{ $novedad->descripcion }}</td>
						<td>{{ $usuario }}</td>
					</tr>
				@endforeach
			</tbody>

		</table>
	</div>

	<br/><br/>
	<div class="page-break"></div>
	
	<h2>Análisis DOFA</h2>
	<hr>
	@include('terceros.analisis_dofa.matriz')

	<br/><br/>

	<div class="page-break"></div>
	
	<h2>Control Académico y disciplinario</h2>
	<hr>
	<br>
	<div class="alert alert-info alert-dismissible">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	  Pase el mouse por encima de cada código de abajo para leer su descripción.
	</div>
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable" width="100%">
			<thead>
				<tr>
					<th>Semana</th>
					<th>Registros</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($control_disciplinario as $fila)

					@php
						$semana = App\Core\SemanasCalendario::find($fila->semana_id);
					@endphp
					
					@php
						$registros = App\Matriculas\ControlDisciplinario::where([
																'estudiante_id' => $fila->estudiante_id,
																'semana_id' => $fila->semana_id,
																'curso_id' => $fila->curso_id])
														->get();
					@endphp

					<tr>
						<td>{{ $semana->descripcion }}</td>
						<td>
							@foreach($registros as $fila)
								@php
									$asignatura = App\Calificaciones\Asignatura::find($fila->asignatura_id);
								@endphp
								{{ $asignatura->descripcion }}: {!! imprimir_codigos($fila) !!}
							@endforeach
						</td>
					</tr>
				@endforeach
			</tbody>

		</table>

		<!--
		<h4> Calificación general </h4>
		Positivo: { { App\Matriculas\CodigoDisciplinario::where( ['estudiante_id' => $fila->estudiante_id, 'tipo_codigo' => 'positivo'] )->count( ) }}
		<br>
		Negativo: { { $negativo }}
	-->
	</div>

	<br/><br/>
</div>

<?php

	function imprimir_codigos($fila)
	{
		$mostrar = '';
		if ( count($fila) > 0 ) 
		{
			//$fila = $fila[0];
		}else{
			$fila = (object)['codigo_1_id' => 0, 'codigo_2_id' => 0, 'codigo_3_id' => 0, 'observacion_adicional' => ''];
		}

		// Si hay al menos un código
		if( ($fila->codigo_1_id + $fila->codigo_2_id + $fila->codigo_3_id) > 0 )
		{
			$mostrar = '<ul>';
		}

		if ( $fila->codigo_1_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($fila->codigo_1_id);
			$mostrar .= '<li> '.$el_codigo->id.': '.$el_codigo->descripcion.'</li>';
		}

		if ( $fila->codigo_2_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($fila->codigo_2_id);
			$mostrar .= '<li> '.$el_codigo->id.': '.$el_codigo->descripcion.'</li>';
		}

		if ( $fila->codigo_3_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($fila->codigo_3_id);
			$mostrar .= '<li> '.$el_codigo->id.': '.$el_codigo->descripcion.'</li>';
		}

		if ( $fila->observacion_adicional != '') {

			$mostrar .= '<li><code>'.$fila->observacion_adicional.'</code></li>';

		}

		return $mostrar.'</ul>';
	}
?>