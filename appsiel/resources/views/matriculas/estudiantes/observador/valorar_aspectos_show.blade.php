
<?php 
	$tipos_aspectos = App\Matriculas\TiposAspecto::all();
?>

<h3 align="center"> VALORACIÓN POR ASPECTOS </h3>
<div class="form-group">
	<div class="alert alert-info">
	  <strong>Convenciones</strong> <br/> 
	  @include('academico_docente.estudiantes.lbl_convenciones_valorar_aspectos_observador')
	</div>
</div>

<div class="table-responsive">
	@foreach ($tipos_aspectos as $tipo_aspecto)
		<table class="table table-bordered table-striped" width="100%">
			<thead>
				<tr>
					<th rowspan="2" style="background-color:rgba(0, 128, 0, 0.2);">ÍTEM</th>
					<th rowspan="2" style="background-color:rgb(255, 253, 125);"><b>{{ strtoupper($tipo_aspecto->descripcion) }}</b></th>
					<th colspan="4" style="background-color:rgba(0, 102, 255, 0.3);">PERIODOS</th>
				</tr>
				<tr>
					<th style="background-color:rgba(0, 102, 255, 0.3);">1°</th>
					<th style="background-color:rgba(0, 102, 255, 0.3);">2°</th>
					<th style="background-color:rgba(0, 102, 255, 0.3);">3°</th>
					<th style="background-color:rgba(0, 102, 255, 0.3);">4°</th>
				</tr>
			</thead>
			<tbody>
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
						<td style="background-color:rgba(0, 128, 0, 0.2); text-align: center;">{{ $aspecto->orden }}</td>
						<td>{{ $aspecto->descripcion }}</td>
						<td class="cuadrito">{{ $val_per1 }}</td>
						<td class="cuadrito">{{ $val_per2 }}</td>
						<td class="cuadrito">{{ $val_per3 }}</td>
						<td class="cuadrito">{{ $val_per4 }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		<br>
	@endforeach
</div>
