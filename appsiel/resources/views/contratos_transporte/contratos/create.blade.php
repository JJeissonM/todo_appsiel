@extends('layouts.principal')

@section('webstyle')
<style>
	.page {
		padding: 50px;
		-webkit-box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		-moz-box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		font-size: 16px;
	}

	.border {
		border: 1px solid;
		padding: 5px;
	}
</style>
@endsection

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-12">
				<div class="panel panel-primary">
					<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Crear Contrato</h4>
					<div class="panel-body">
						<div class="col-md-12 page">
							{{ Form::open(['route'=>'cte_contratos.store','method'=>'post','class'=>'form-horizontal']) }}
							<input type="hidden" name="variables_url" value="{{$variables_url}}" />
							<input type="hidden" name="source" value="{{$source}}" />
							<input type="hidden" name="plantilla_id" value="{{$v->id}}" />
							<div class="col-md-12" style="padding: 30px;">
								<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Información del Contrato</h4>
							</div>
							<div class="col-md-6" style="padding: 30px;">
								<div class="form-group">
									<label>Representante Legal (CONTRATISTA)</label>
									<input type="text" name="rep_legal" class="form-control" required value="HUBER PARADA QUINTERO">
								</div>
								<div class="form-group">
									<label>Contratante</label>
									<select class="form-control select2" id="contratante" name="contratante_id" onchange="manual()" required>
										<option value="MANUAL">-- Seleccione una opción --</option>
										<option value="MANUAL">INTRODUCCIÓN MANUAL</option>
										@if($contratantes!=null)
										@foreach($contratantes as $key=>$value)
										<option value="{{$key}}">{!!$value!!}</option>
										@endforeach
										@endif
									</select>
									<input type="text" name="contratanteText" id="contratanteText" class="form-control" style="display: none; margin-top: 20px; margin-bottom: 20px;" placeholder="Nombres y apellidos del contratante">
									<input type="text" name="contratanteIdentificacion" id="contratanteIdentificacion" class="form-control" style="display: none; margin-top: 20px; margin-bottom: 20px;" placeholder="Identificación del contratante">
									<input type="text" name="contratanteDireccion" id="contratanteDireccion" class="form-control" style="display: none; margin-top: 20px; margin-bottom: 20px;" placeholder="Dirección del contratante">
									<input type="text" name="contratanteTelefono" id="contratanteTelefono" class="form-control" style="display: none; margin-top: 20px; margin-bottom: 20px;" placeholder="Teléfono del contratante">
								</div>
								<div class="form-group">
									<label>En Representación de...</label>
									<textarea required class="form-control" name="representacion_de">PARA UN GRUPO ESPECIFICO DE USUARIOS DE TRANSPORTE DE PERSONAL (TRANSPORTE PARTICULAR)</textarea>
									<!-- <input type="text" required class="form-control" name="representacion_de" value="PARA UN GRUPO ESPECIFICO DE USUARIOS DE TRANSPORTE DE PERSONAL (TRANSPORTE PARTICULAR)"> -->
								</div>
								<div class="form-group">
									<label>Objeto del Contrato</label>
									<textarea class="form-control" required name="objeto">Prestacion del servicio transporte especial para un grupo especifico de usuarios de transporte de personal (transporte particular).</textarea>
									<!-- <input type="text" class="form-control" required name="objeto" value="el objeto del contrato consiste en el transporte terrestre de pasajeros, mediante un servicio expreso al grupo específico descrito anteriormente" /> -->
								</div>
								<div class="form-group">
									<label>Vehículo</label>
									<select class="form-control select2" name="vehiculo_id" id="vehiculo_id" required onchange="conductores()">
										@if($vehiculos!=null)
										<option value="0">-- Seleccione vehículo --</option>
										@foreach($vehiculos as $key=>$value)
										<option value="{{$key}}">{!!$value!!}</option>
										@endforeach
										@else
										<option value="0">No hay vehículos con documentos en regla habilitados. Si continúa, el contrato no será guardado.</option>
										@endif
									</select>
								</div>
								<div class="form-group">
									<label>Tipo de Servicio</label>
									<select class="form-control" name="tipo_servicio" required>
										<option value="IDA-REGRESO">IDA Y REGRESO</option>
										<option value="IDA">SOLO IDA</option>
										<option value="REGRESO">SOLO REGRESO</option>
									</select>
								</div>
								<div class="form-group">
									<label>Disponibilidad</label>
									<select class="form-control" name="disponibilidad" required>
										<option value="SI">SI</option>
										<option value="NO">NO</option>
									</select>
								</div>
							</div>
							<div class="col-md-6" style="padding: 30px;">
								<div class="form-group">
									<label>Nro. de Personas a Movilizar</label>
									<input type="number" class="form-control" name="nro_personas" required>
								</div>
								<div class="form-group">
									<label>Origen</label>
									<input type="text" class="form-control" name="origen" required />
								</div>
								<div class="form-group">
									<label>Destino</label>
									<input type="text" class="form-control" name="destino" required />
								</div>
								<div class="form-group">
									<label>Fecha de Inicio</label>
									<input type="date" class="form-control" name="fecha_inicio" required />
								</div>
								<div class="form-group">
									<label>Fecha de Terminación</label>
									<input onchange="validar()" class="form-control" type="date" name="fecha_fin" id="fecha_fin" required />
								</div>
								<div class="form-group">
									<label>Fecha Firma</label>
									<input type="number" class="form-control" placeholder="Día" required name="dia_contrato" />
									<input type="text" class="form-control" placeholder="Mes" name="mes_contrato" required />
									<input type="number" class="form-control" placeholder="Año" required name="anio_contrato" />
								</div>
								<div class="form-group">
									<label>Convenio Consorcio Unión Temporarl Con</label>
									<input type="text" class="form-control" name="convenio">
								</div>
							</div>
							<div class="col-md-12" style="padding: 30px;">
								<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Información de la Planilla FUEC</h4>
								<div class="form-group">
									<label>Conductor 1</label>
									<select name="conductor_id[]" id="conductor1" class="form-control select2">
										<option value="">-- Seleccione opción --</option>
									</select>
								</div>
								<div class="form-group">
									<label>Conductor 2</label>
									<select name="conductor_id[]" id="conductor2" class="form-control select2">
										<option value="">-- Seleccione opción --</option>
									</select>
								</div>
								<div class="form-group">
									<label>Conductor 3</label>
									<select name="conductor_id[]" id="conductor3" class="form-control select2">
										<option value="">-- Seleccione opción --</option>
									</select>
								</div>
							</div>

							<!--<div class="row" style="margin-top: 20px;">
										<div class="table-responsive col-md-12" id="table_content">
											<h4>DESCRIPCIÓN DEL GRUPO DE USUARIOS</h4>
											<a onclick="addRow('usuarios')" class="btn btn-danger btn-xs"><i class="fa fa-plus"></i> Agregar Usuario</a>
											<table id="usuarios" class="table table-bordered table-striped">
												<thead>
													<tr>
														<th>Identificación</th>
														<th>Persona</th>
														<th>Quitar</th>
													</tr>
												</thead>
												<tbody>

												</tbody>
											</table>
										</div>
								</div>-->
							<div class="form-group">
								<div class="col-md-12" style="margin-top: 50px; text-align: center;">
									<button type="submit" class="btn btn-primary" title="Guardar Contrato y FUEC"><i class="fa fa-save"></i> Guardar Contrato y FUEC</a>
								</div>
							</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$('.select2').select2();
	});

	$(document).on('click', '.delete', function(event) {
		event.preventDefault();
		$(this).closest('tr').remove();
	});

	function addRow(tabla) {
		var html = "<tr><td><input type='text' class='form-control' name='identificacion[]' required /></td><td><input type='text' class='form-control' name='persona[]' required /></td><td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td></tr>";
		$('#' + tabla + ' tr:last').after(html);
	}

	function manual() {
		if ($("#contratante").val() == 'MANUAL') {
			$("#contratanteText").fadeIn();
			$("#contratanteIdentificacion").fadeIn();
			$("#contratanteDireccion").fadeIn();
			$("#contratanteTelefono").fadeIn();
		} else {
			$("#contratanteText").fadeOut();
			$("#contratanteIdentificacion").fadeOut();
			$("#contratanteDireccion").fadeOut();
			$("#contratanteTelefono").fadeOut();
		}
	}


	function validar() {
		var f = $("#fecha_fin").val();
		var v = f.split("-");
		var hoy = new Date();
		var mes = hoy.getMonth() + 1;
		if (mes != parseInt(v[1])) {
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha final no puede ser de un mes diferente al actual, si continua sin corregir el contrato no será guardado y perderá los datos',
				footer: '<a href>¿Desea continuar?</a>'
			});
		}
	}

	function conductores() {
		var id = $("#vehiculo_id").val();
		limpiarselect();
		$.ajax({
			type: 'GET',
			url: "{{url('')}}/" + "cte_contratos/" + id + "/conductores",
			data: {},
		}).done(function(msg) {
			var m = JSON.parse(msg);
			if (m.error == 'NO') {
				$.each(m.data, function(index, item) {
					$("#conductor1").append("<option value='" + index + "'>" + item + "</option>");
					$("#conductor2").append("<option value='" + index + "'>" + item + "</option>");
					$("#conductor3").append("<option value='" + index + "'>" + item + "</option>");
				});
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Alerta!',
					text: m.mensaje
				});
			}
		});
	}

	function limpiarselect() {
		$("#conductor1 option").each(function() {
			$(this).remove();
		});
		$("#conductor2 option").each(function() {
			$(this).remove();
		});
		$("#conductor3 option").each(function() {
			$(this).remove();
		});
		$("#conductor1").append("<option value=''>-- Seleccione opción --</option>");
		$("#conductor2").append("<option value=''>-- Seleccione opción --</option>");
		$("#conductor3").append("<option value=''>-- Seleccione opción --</option>");
	}
</script>
@endsection