@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-12">
				<div class="list-group">
					<a style="font-size: 18px;" href="#" class="list-group-item list-group-item-action active">
						Información del Vehículo, Propietario y Período
					</a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{"PLACA: ".$v->placa." - MARCA: ".$v->marca." - CLASE: ".$v->clase." - MODELO: ".$v->modelo." - INTERNO: ".$v->int." - NÚMERO VINCULACIÓN: ".$v->numero_vin}}</b></a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{"PROPIETARIO: ".$v->propietario->tercero->numero_identificacion." - ".$v->propietario->tercero->descripcion." ".$v->propietario->tercero->razon_social}}</b></a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{"PERÍODO: ".$ap->inicio." - ".$ap->fin." (".$ap->anio->anio.")"}}</b></a>
				</div>
			</div>
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">Crear Mantenimiento</div>
					<div class="panel-body">
						{{ Form::open(['route'=>'mantenimiento.store','method'=>'post','class'=>'form-horizontal']) }}
						<h4>Datos del Mantenimiento</h4>
						<input type="hidden" name="vehiculo_id" value="{{$v->id}}" />
						<input type="hidden" name="anioperiodo_id" value="{{$ap->id}}" />
						<input type="hidden" name="variables_url" value="{{$variables_url}}" />
						<div class="form-group">
							<div class="col-md-4">
								<label class="control-label">Fecha Mantenimiento</label>
								<input type="date" class="form-control" name="fecha" required />
							</div>
							<div class="col-md-4">
								<label class="control-label">Lugar Realización (Sede)</label>
								<input type="text" class="form-control" name="sede" required />
							</div>
							<div class="col-md-4">
								<label class="control-label">Revisado (SI, NO, OK, valores según entidad)</label>
								<input type="text" class="form-control" name="revisado" required />
							</div>
						</div>
						<div class="table-responsive col-md-6" id="table_content">
							<h4>Reportes del Mantenimiento</h4>
							<a onclick="addRow('reportes')" class="btn btn-danger btn-xs"><i class="fa fa-plus"></i> Agregar Reporte</a>
							<table id="reportes" class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>Fecha (opcional)</th>
										<th>Reporte</th>
										<th>Quitar</th>
									</tr>
								</thead>
								<tbody>

								</tbody>
							</table>
						</div>
						<div class="table-responsive col-md-6" id="table_content">
							<h4>Observaciones del Mantenimiento</h4>
							<a onclick="addRow('obs')" class="btn btn-danger btn-xs"><i class="fa fa-plus"></i> Agregar Observación</a>
							<table id="obs" class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>Fecha (opcional)</th>
										<th>Observación</th>
										<th>Quitar</th>
									</tr>
								</thead>
								<tbody>

								</tbody>
							</table>
						</div>
						<div class="form-group">
							<div class="col-md-12">
								<button type="submit" class="btn btn-primary">Guardar todo y regresar</button>
							</div>
						</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script>
	$(document).on('click', '.delete', function(event) {
		event.preventDefault();
		$(this).closest('tr').remove();
	});

	function addRow(tabla) {
		var html = "<tr>";
		if (tabla == 'obs') {
			html = html + "<td><input type='date' class='form-control' name='obs_fecha_suceso[]' /></td><td><input type='text' class='form-control' name='obs_observacion[]' required /></td><td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
		} else {
			html = html + "<td><input type='date' class='form-control' name='reporte_fecha_suceso[]' /></td><td><input type='text' class='form-control' name='reporte_reporte[]' required /></td><td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
		}
		html = html + "</tr>";
		$('#' + tabla + ' tr:last').after(html);
	}
</script>
@endsection