@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading" align="center">
				<h4>LISTADO DE RESPONSABLES DEL ESTUDIANTE</h4>
			</div>
			<div class="panel-body">
				<div class="col-md-12" style="border: 1px solid; margin-bottom: 50px; padding: 10px;">
					<div class="col-md-6">
						<p><b>Documento:</b> {{$tercero->numero_identificacion}}</p>
						<p><b>Estudiante:</b> {{$tercero->descripcion}}</p>
						<p><b>Teléfono:</b> {{$tercero->telefono1}}</p>
						<p><b>Correo:</b> {{$tercero->email}}</p>
					</div>
					<div class="col-md-6">
						<p><b>Estado:</b> {{$tercero->estado}}</p>
						<p><b>Género:</b> {{$estudiante->genero}}</p>
						<p><b>Fecha Nacimiento:</b> {{$estudiante->fecha_nacimiento}}</p>
						<p><b>Eps:</b> {{$estudiante->eps}}</p>
					</div>
				</div>
				<a onclick="addPersona()" class="btn btn-danger btn-xs"><i class="fa fa-plus"></i> Agregar Persona Responsable</a>
				<table id="tbPersonas" class="table table-hover table-dark table-responsive" style="margin-top: 20px;">
					<thead>
						<tr>
							<th scope="col">Número Doc.</th>
							<th scope="col">Responsable</th>
							<th scope="col">Ocupación</th>
							<th scope="col">Teléfono</th>
							<th scope="col">Correo</th>
							<th scope="col">Tipo</th>
							<th scope="col">Acciones</th>
						</tr>
					</thead>
					<tbody>
						@foreach($lista as $l)
						<tr>
							<td>{{$l->tercero->numero_identificacion}}</td>
							<td>{{$l->tercero->descripcion}}</td>
							<td>{{$l->ocupacion}}</td>
							<td>{{$l->tercero->telefono1}}</td>
							<td>{{$l->tercero->email}}</td>
							<td>{{$l->tiporesponsable->descripcion}}</td>
							<td>
								<a class="btn btn-xs btn-primary" id="edit_{{$l->id}}" onclick="edit(this.id)" data-toggle="modal" data-target="#editModal" title="Editar Información"><i class="fa fa-edit"></i></a>
								<a class="btn btn-xs btn-success" id="{{$l->id}}" onclick="show(this.id)" data-toggle="modal" data-target="#showModal" title="Ver Información del Responsable"><i class="fa fa-eye"></i></a>
								<a href="{{route('gestionresponsables_delete',$l->id).$variables_url}}" class="btn btn-xs btn-danger" title="Eliminar Responsable"><i class="fa fa-remove"></i></a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">DATOS RESPONSABLE</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="alert alert-success" role="alert">
					Al escribir la identificación del responsable el sistema buscará automáticamente y rrellenará los campos en caso de que ya exista.
				</div>
				<form action="{{ url('matriculas/estudiantes/gestionresponsables/store') }}" method="POST">
					{{ csrf_field() }}

					<input type="hidden" name="core_empresa_id" value="{{ $tercero->core_empresa_id }}">
					<input type="hidden" name="estudiante_id" value="{{ $estudiante->id }}">
					<input type="hidden" name="variables_url" value="{{ $variables_url }}">
					<div class="row">
						<h4 style="text-align: center;">DATOS BÁSICOS</h4>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">Tipo Documento*</label>
								<select class="form-control" name="id_tipo_documento_id" id="txt1" required>
									@foreach($tiposdoc as $td)
									<option value="{{$td->id}}">{{$td->descripcion}}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Número de Identificación*</label>
								<input type="text" onkeyup="buscar()" class="form-control" id="txtID" name="numero_identificacion" required>
							</div>
							<div class="form-group">
								<label class="control-label">Primer Nombre*</label>
								<input type="text" class="form-control" id="txt2" name="nombre1" required>
							</div>
							<div class="form-group">
								<label class="control-label">Segundo Nombre*</label>
								<input type="text" class="form-control" id="txt3" name="otros_nombres" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">Primer Apellido*</label>
								<input type="text" class="form-control" id="txt4" name="apellido1" required>
							</div>
							<div class="form-group">
								<label class="control-label">Segundo Apellido*</label>
								<input type="text" class="form-control" id="txt5" name="apellido2" required>
							</div>
							<div class="form-group">
								<label class="control-label">Tipo Responsable*</label>
								<select class="form-control" name="tiporesponsable_id" id="tr" onchange="mostrar()" required>
									@foreach($tipos as $t)
									<option value="{{$t->id}}">{{$t->descripcion}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">Teléfono*</label>
								<input type="text" class="form-control" id="txt6" name="telefono1" required>
							</div>
							<div class="form-group">
								<label class="control-label">Correo*</label>
								<input type="text" class="form-control" id="txt7" name="email" required>
							</div>
							<div class="form-group">
								<label class="control-label">Ocupación*</label>
								<input type="text" class="form-control" name="ocupacion" required>
							</div>
						</div>
					</div>
					<div class="row" style="display: none;" id="mostrar">
						<h4 style="text-align: center;">DATOS RESPONSABLE FINANCIERO</h4>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Dirección Trabajo*</label>
								<input type="text" class="form-control" name="direccion_trabajo" value=" " required>
							</div>
							<div class="form-group">
								<label class="control-label">Teléfono Trabajo*</label>
								<input type="text" class="form-control" name="telefono_trabajo" value=" " required>
							</div>
							<div class="form-group">
								<label class="control-label">Puesto Trabajo</label>
								<input type="text" class="form-control" name="puesto_trabajo">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Empresa Dónde Labora</label>
								<input type="text" class="form-control" name="empresa_labora">
							</div>
							<div class="form-group">
								<label class="control-label">Jefe Inmediato</label>
								<input type="text" class="form-control" name="jefe_inmediato">
							</div>
							<div class="form-group">
								<label class="control-label">Teléfono Jefe</label>
								<input type="text" class="form-control" name="telefono_jefe">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">Si Es Trabajador Independiente Escriba su Actividad</label>
								<input type="text" class="form-control" name="descripcion_trabajador_independiente">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12" style="text-align: right;">
							<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
							<button type="submit" class="btn btn-primary">Guardar Datos</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">DATOS DEL RESPONSABLE</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<h4>DATOS BÁSICOS</h4>
						<div class="col-md-6">
							<p id="txtTd"><b>Tipo Documento:</b></p>
							<p id="txtDoc"><b>Documento:</b></p>
							<p id="txtRes"><b>Responsable:</b></p>
							<p id="txtTr"><b>Tipo Responsable:</b></p>
						</div>
						<div class="col-md-6">
							<p id="txtTel"><b>Teléfono:</b></p>
							<p id="txtEm"><b>Correo:</b></p>
							<p id="txtEs"><b>Estado:</b></p>
							<p id="txtOcu"><b>Ocupación:</b></p>
						</div>
					</div>
					<div class="col-md-12" id="mostrar2" style="display: none;">
						<h4>DATOS RESPONSABLE FINANCIERO</h4>
						<div class="col-md-6">
							<p id="txtDirT"><b>Dirección Trabajo:</b></p>
							<p id="txtTelT"><b>Teléfono Trabajo:</b></p>
							<p id="txtPuestoT"><b>Puesto:</b></p>
							<p id="txtIndT"><b>Independiente:</b></p>
						</div>
						<div class="col-md-6">
							<p id="txtEmpT"><b>Empresa:</b></p>
							<p id="txtJIT"><b>Jefe Inmediato:</b></p>
							<p id="txtTelJT"><b>Teléfono Jefe:</b></p>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>


<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">EDITAR DATOS RESPONSABLE</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="{{ url('matriculas/estudiantes/gestionresponsables/update') }}" method="POST">
					{{ csrf_field() }}

					<input type="hidden" name="core_empresa_id" value="{{ $tercero->core_empresa_id }}">
					<input type="hidden" name="estudiante_id" value="{{ $estudiante->id }}">
					<input type="hidden" name="variables_url" value="{{ $variables_url }}">
					<input type="hidden" name="responsable_id" id="responsable_id">
					<div class="row">
						<h4 style="text-align: center;">DATOS BÁSICOS</h4>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">Tipo Documento*</label>
								<select class="form-control" name="id_tipo_documento_id" id="txtTde" required>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Número de Identificación*</label>
								<input type="text" class="form-control" id="txtDoce" name="numero_identificacion" required>
							</div>
							<div class="form-group">
								<label class="control-label">Primer Nombre*</label>
								<input type="text" class="form-control" id="txtpne" name="nombre1" required>
							</div>
							<div class="form-group">
								<label class="control-label">Segundo Nombre*</label>
								<input type="text" class="form-control" id="txtsne" name="otros_nombres" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">Primer Apellido*</label>
								<input type="text" class="form-control" id="txtpae" name="apellido1" required>
							</div>
							<div class="form-group">
								<label class="control-label">Segundo Apellido*</label>
								<input type="text" class="form-control" id="txtsae" name="apellido2" required>
							</div>
							<div class="form-group">
								<label class="control-label">Tipo Responsable*</label>
								<select class="form-control" name="tiporesponsable_id" id="txtTre" onchange="mostrare()" required>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">Teléfono*</label>
								<input type="text" class="form-control" id="txtTele" name="telefono1" required>
							</div>
							<div class="form-group">
								<label class="control-label">Correo*</label>
								<input type="text" class="form-control" id="txtEme" name="email" required>
							</div>
							<div class="form-group">
								<label class="control-label">Ocupación*</label>
								<input type="text" class="form-control" id="txtOcue" name="ocupacion" required>
							</div>
						</div>
					</div>
					<div class="row" style="display: none;" id="mostrare">
						<h4 style="text-align: center;">DATOS RESPONSABLE FINANCIERO</h4>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Dirección Trabajo*</label>
								<input type="text" class="form-control" id="txtDirTe" name="direccion_trabajo" value=" ">
							</div>
							<div class="form-group">
								<label class="control-label">Teléfono Trabajo*</label>
								<input type="text" class="form-control" id="txtTelTe" name="telefono_trabajo" value=" ">
							</div>
							<div class="form-group">
								<label class="control-label">Puesto Trabajo</label>
								<input type="text" class="form-control" id="txtPuestoTe" name="puesto_trabajo">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Empresa Dónde Labora</label>
								<input type="text" class="form-control" id="txtEmpTe" name="empresa_labora">
							</div>
							<div class="form-group">
								<label class="control-label">Jefe Inmediato</label>
								<input type="text" class="form-control" id="txtJITe" name="jefe_inmediato">
							</div>
							<div class="form-group">
								<label class="control-label">Teléfono Jefe</label>
								<input type="text" class="form-control" id="txtTelJTe" name="telefono_jefe">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">Si Es Trabajador Independiente Escriba su Actividad</label>
								<input type="text" class="form-control" id="txtIndTe" name="descripcion_trabajador_independiente">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12" style="text-align: right;">
							<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
							<button type="submit" class="btn btn-primary">Guardar Datos</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {

	});


	var responsablesList = <?php echo json_encode($lista2); ?>;
	var tipos = <?php echo json_encode($tipos); ?>;
	var tiposdoc = <?php echo json_encode($tiposdoc); ?>;

	function addPersona() {
		$("#exampleModal").modal('show');
	}

	function mostrar() {
		var tr = $("#tr option:selected").text();
		if (tr == 'RESPONSABLE-FINANCIERO') {
			$("#mostrar").removeAttr('style');
		} else {
			$("#mostrar").attr('style', 'display: none;');
		}
	}

	function mostrare() {
		var tr = $("#txtTre option:selected").text();
		if (tr == 'RESPONSABLE-FINANCIERO') {
			$("#mostrare").removeAttr('style');
		} else {
			$("#mostrare").attr('style', 'display: none;');
		}
	}


	function show(id) {
		limpiar();
		$("#mostrar2").attr('style', 'display: none;');
		if (responsablesList.length > 0) {
			responsablesList.forEach(element => {
				if (element.id == id) {
					$("#txtTd").html("<b>Tipo Documento: </b>" + element.td);
					$("#txtDoc").html("<b>Documento: </b>" + element.doc);
					$("#txtRes").html("<b>Responsable: </b>" + element.nom);
					$("#txtTr").html("<b>Tipo Responsable: </b>" + element.tr);
					$("#txtTel").html("<b>Teléfono: </b>" + element.tel);
					$("#txtEm").html("<b>Correo: </b>" + element.email);
					$("#txtEs").html("<b>Estado: </b>" + element.estado);
					$("#txtOcu").html("<b>Ocupación: </b>" + element.ocu);
					if (element.tr == 'RESPONSABLE-FINANCIERO') {
						$("#txtDirT").html("<b>Dirección Trabajo: </b>" + element.dt);
						$("#txtTelT").html("<b>Teléfono Trabajo: </b>" + element.tt);
						$("#txtPuestoT").html("<b>Puesto: </b>" + element.pt);
						$("#txtIndT").html("<b>Independiente: </b>" + element.indt);
						$("#txtEmpT").html("<b>Empresa: </b>" + element.et);
						$("#txtJIT").html("<b>Jefe Inmediato: </b>" + element.jt);
						$("#txtTelJT").html("<b>Teléfono Jefe: </b>" + element.tjt);
						$("#mostrar2").removeAttr('style');
					}
				}
			});
		}
	}

	function limpiar() {
		$("#txtTd").html("");
		$("#txtDoc").html("");
		$("#txtRes").html("");
		$("#txtTr").html("");
		$("#txtTel").html("");
		$("#txtEm").html("");
		$("#txtEs").html("");
		$("#txtOcu").html("");
		$("#txtDirT").html("");
		$("#txtTelT").html("");
		$("#txtPuestoT").html("");
		$("#txtIndT").html("");
		$("#txtEmpT").html("");
		$("#txtJIT").html("");
		$("#txtTelJT").html("");
	}

	function limpiar2() {
		$("#responsable_id").val("");
		$("#txtpne").val("");
		$("#txtsne").val("");
		$("#txtpae").val("");
		$("#txtsae").val("");
		$("#txtDoce").val("");
		$("#txtRese").val("");
		$("#txtTre").val("");
		$("#txtTele").val("");
		$("#txtEme").val("");
		$("#txtEse").val("");
		$("#txtOcue").val("");
		$("#txtDirTe").val("");
		$("#txtTelTe").val("");
		$("#txtPuestoTe").val("");
		$("#txtIndTe").val("");
		$("#txtEmpTe").val("");
		$("#txtJITe").val("");
		$("#txtTelJTe").val("");
		$("#txtTde option").each(function() {
			$(this).remove();
		});
		$("#txtTre option").each(function() {
			$(this).remove();
		});
	}

	function edit(id) {
		var id = id.split("_");
		limpiar2();
		$("#mostrare").attr('style', 'display: none;');
		if (responsablesList.length > 0) {
			responsablesList.forEach(element => {
				if (element.id == id[1]) {
					tiposdoc.forEach(e => {
						if (element.tdid == e.id) {
							$("#txtTde").append("<option selected='selected' value='" + e.id + "'>" + e.descripcion + "</option>");
						} else {
							$("#txtTde").append("<option value='" + e.id + "'>" + e.descripcion + "</option>");
						}
					});
					tipos.forEach(m => {
						if (element.trid == m.id) {
							$("#txtTre").append("<option selected='selected' value='" + m.id + "'>" + m.descripcion + "</option>");
						} else {
							$("#txtTre").append("<option value='" + m.id + "'>" + m.descripcion + "</option>");
						}
					});
					$("#responsable_id").val(element.id);
					$("#txtDoce").val(element.doc);
					$("#txtRese").val(element.nom);
					$("#txtTele").val(element.tel);
					$("#txtEme").val(element.email);
					$("#txtEse").val(element.estado);
					$("#txtOcue").val(element.ocu);
					$("#txtpne").val(element.pne);
					$("#txtsne").val(element.sne);
					$("#txtpae").val(element.pae);
					$("#txtsae").val(element.sae);
					if (element.tr == 'RESPONSABLE-FINANCIERO') {
						$("#txtDirTe").val(element.dt);
						$("#txtTelTe").val(element.tt);
						$("#txtPuestoTe").val(element.pt);
						$("#txtIndTe").val(element.indt);
						$("#txtEmpTe").val(element.et);
						$("#txtJITe").val(element.jt);
						$("#txtTelJTe").val(element.tjt);
						$("#mostrare").removeAttr('style');
					}
				}
			});
		}
	}

	function buscar() {
		var id = $("#txtID").val();
		$.ajax({
			type: 'GET',
			url: "{{url('')}}/" + "matriculas/estudiantes/gestionresponsables/consultar/" + id + "/tercero",
			data: {},
		}).done(function(msg) {
			if (msg !== 'null') {
				var m = JSON.parse(msg);
				$("#txt1").val(m.id_tipo_documento_id);
				$("#txtID").val(m.numero_identificacion);
				$("#txt2").val(m.nombre1);
				$("#txt3").val(m.otros_nombres);
				$("#txt4").val(m.apellido1);
				$("#txt5").val(m.apellido2);
				$("#txt6").val(m.telefono1);
				$("#txt7").val(m.email);
			}
		});
	}
</script>
@endsection