
<?php 
	$tipos_aspectos = App\Matriculas\TiposAspecto::all();
?>

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
				@php 
					$aspectos = App\Matriculas\CatalogoAspecto::where('id_tipo_aspecto','=',$tipo_aspecto->id)->orderBy('orden','ASC')->get();
				@endphp
				@foreach ($aspectos as $aspecto)
					<?php 
						$val_per1 = "";
						$val_per2 = "";
						$val_per3 = "";
						$val_per4 = "";
						$aspecto_estudiante_id="";

						$aspecto_estudiante = App\Matriculas\AspectosObservador::where('id_aspecto','=',$aspecto->id)->where('id_estudiante','=', $estudiante->id )->where('fecha_valoracion','like',date('Y').'%')->get()->first();

						if( !is_null($aspecto_estudiante) )
						{
							$val_per1 = $aspecto_estudiante->valoracion_periodo1;
							$val_per2 = $aspecto_estudiante->valoracion_periodo2;
							$val_per3 = $aspecto_estudiante->valoracion_periodo3;
							$val_per4 = $aspecto_estudiante->valoracion_periodo4;
							$aspecto_estudiante_id = $aspecto_estudiante->id;
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
