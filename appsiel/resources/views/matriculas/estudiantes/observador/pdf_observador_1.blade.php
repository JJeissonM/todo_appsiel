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
    border: 1px solid;
}

th {
	background-color: #CACACA;
}

td.cuadrito {
	border: 1px solid;
}

h3 {
	text-align:center;
}

div.recuadro{
	border: 1px solid;
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

	<div class="form-group">
		<div class="alert alert-info">
		  <strong>Convenciones</strong> <br/> 
		  S= Siempre    CS= Casi siempre      AV= Algunas veces   N= Nunca
		</div>
	</div>

	<br/>
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

							$val_per1 = "";
							$val_per2 = "";
							$val_per3 = "";
							$val_per4 = "";
							$aspecto_estudiante_id="";

							$aspecto_estudiante=App\Matriculas\AspectosObservador::where('id_aspecto','=',$aspecto->id)->where('id_estudiante','=',$estudiante->id)->where('fecha_valoracion','like',date('Y').'%')->get();
							if( !is_null($aspecto_estudiante) )
							{
								$val_per1 = $aspecto_estudiante[0]->valoracion_periodo1;
								$val_per2 = $aspecto_estudiante[0]->valoracion_periodo2;
								$val_per3 = $aspecto_estudiante[0]->valoracion_periodo3;
								$val_per4 = $aspecto_estudiante[0]->valoracion_periodo4;
								$aspecto_estudiante_id = $aspecto_estudiante[0]->id;
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

	<div class="page-break"></div>
	
	<h2>Observaciones generales</h2>
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
	<?php
		$fortaleza=0;
		$oportunidad=0;
		$debilidad=0;
		$amenaza=0;
		foreach ($registros_analisis as $registros)
		{
			if($registros->tipo_caracteristica=='Fortaleza'){
				$fortaleza=1;
			}
			if($registros->tipo_caracteristica=='Oportunidad'){
				$oportunidad=1;
			}
			if($registros->tipo_caracteristica=='Debilidad'){
				$debilidad=1;
			}
			if($registros->tipo_caracteristica=='Amenaza'){
				$amenaza=1;
			}								
		}
	?>

	<?php
		if( !is_null($registros_analisis) )
		{
	?>
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable">
			<thead>
				<tr>

					<th>Fecha</th>

					@if($fortaleza==1)
						<th>Fortalezas</th>
					@endif
					@if($oportunidad==1)
						<th>Oportunidades</th>
					@endif
					@if($debilidad==1)
						<th>Debilidades</th>
					@endif
					@if($amenaza==1)
						<th>Amenazas</th>
					@endif
				</tr>
			</thead>
			<tbody>
				@foreach ($registros_analisis as $registro)
					<tr>
						<td>{{ $registro->fecha_novedad }}</td>
						
						@if($fortaleza==1)
							@if($registro->tipo_caracteristica=='Fortaleza') 
								<td>{{ $registro->descripcion }}</td>
							@else
								<td>&nbsp;</td>
							@endif
						@endif

						@if($oportunidad==1)
							@if($registro->tipo_caracteristica=='Oportunidad') 
								<td>{{ $registro->descripcion }}</td>
							@else
								<td>&nbsp;</td>
							@endif
						@endif

						@if($debilidad==1)
							@if($registro->tipo_caracteristica=='Debilidad') 
								<td>{{ $registro->descripcion }}</td>
							@else
								<td>&nbsp;</td>
							@endif
						@endif

						@if($amenaza==1)
							@if($registro->tipo_caracteristica=='Amenaza') 
								<td>{{ $registro->descripcion }}</td>
							@else
								<td>&nbsp;</td>
							@endif
						@endif
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<?php
	
		}
	?>
</div>