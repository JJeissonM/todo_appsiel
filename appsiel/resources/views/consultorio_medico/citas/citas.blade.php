@extends('layouts.principal')
@section('estilos_1')
<style type="text/css">
	.consultorio {
		font-size: 1.2rem;
		background: #eee;
		border: 0 solid #3d7e9a;
		color: #333;
		margin-top: 0;
		margin-bottom: 5px;
		padding: 15px;
		position: relative;
		font-style: normal;
		font-weight: 400;
		line-height: 1.5;
		overflow: auto;
		direction: ltr !important;
		text-align: left !important;
		border-left-width: 5px !important;
		border-right-width: 0 !important;
		-moz-tab-size: 4;
		tab-size: 4;
		-moz-hyphens: none;
		-webkit-hyphens: none;
		-ms-hyphens: none;
		hyphens: none;
	}
</style>
@endsection

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<div class="row">
			<div class="col-md-12" style="padding-top: 30px;">
				<div class="col-md-12">
					<div class="panel panel-primary">
						<div class="panel-heading">Citas Programadas Para la Fecha [[ {{$fecha}} ]]</div>
						<div class="panel-body">
							<div class="col-md-12">
								<div class="col-md-12">
									<label>Seleccionar Otra Fecha</label>
								</div>
								<div class="col-md-6">
									<input type="date" id="fecha" name="fecha" class="form-control">
								</div>
								<div class="col-md-3">
									<button class="btn btn-primary btn-block" onclick="cambiar()"><i class="fa fa-search"></i> Consultar Esta Fecha</button>
								</div>
								<div class="col-md-3">
									<button data-toggle="modal" data-target="#modalNuevo" onclick="cita();" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Crear Nueva Cita</button>
								</div>
							</div>
							<div class="col-md-12">
								<div class="col-md-12">
									<h3>Listado de Consultorios (Citas por consultorio para la fecha << {{$fecha}}>> )</h3>
								</div>
							</div>
							<div class="col-md-12" style="padding-top: 30px;">
								@if($citas!=null)
								<?php
								$i = 0;
								?>
								@foreach($citas as $key=>$value)
								<div class="col-md-3">
									<div class="consultorio">
										<ul>
											<li><b>{{$key}}</b></li>
											<li>Total Citas en la Fecha: <b>{{count($value)}}</b></li>
											<li><a data-toggle="modal" data-target="#modal_{{$i}}" class="btn btn-default btn-xs btn-block">Ver Citas</a></li>
										</ul>
									</div>
								</div>
								<?php
								$i = $i + 1;
								?>
								@endforeach
								@else
								<h4 style="color: red;">No hay consultorios disponibles</h4>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<br />

@if($citas!=null)
<?php
$j = 0;
?>
@foreach($citas as $key=>$value)

<!-- Modal -->
<div class="modal fade bs-example-modal-lg" id="modal_{{$j}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<h4>Citas en el Consultorio: {{$key}}</h4>
				<div class="row">
					<div class="col-md-12" style="padding-top: 30px;">
						<h5 style="border-left: 6px solid; border-color: #f7a30b; background-color: #f3f595; padding: 10px;"><b>Nota:</b> usted puede cambiar el estado de la cita</h5>
						<div class="table-responsive">
							<table class="table table-striped table-responsive">
								<thead>
									<tr>
										<th>Horario</th>
										<th>Profesional</th>
										<th>Paciente</th>
										<th>Estado</th>
										<th><i style="color: red; font-size: 16px;" class="fa fa-trash-o"></i></th>
									</tr>
								</thead>
								<tbody>
									@if(count($value)>0)
									@foreach($value as $v)
									<tr>
										<td>{{$v['hora_inicio']." - ".$v['hora_fin']}}</td>
										<td>{{$v['profesional']}}</td>
										<td>{{$v['paciente']}}</td>
										<td>
											<select class="form-control" id="estado_{{$v['cita_id']}}" onchange="cambiarEstado(this.id)">
												@foreach($estados as $e)
												@if($v['estado']==$e)
												<option selected value="{{$e}}">{{$e}}</option>
												@else
												<option value="{{$e}}">{{$e}}</option>
												@endif
												@endforeach
											</select>
										</td>
										<td>
											<a href="{{route('citas_medicas.citas_delete',$v['cita_id']).$variables_url.'&fecha='.$fecha}}" title="Eliminar Cita" class="btn btn-danger btn-xs"><i class="fa fa-trash-o"></i></a>
										</td>
									</tr>
									@endforeach
									@else
									<tr class="danger">
										<td colspan="5">No hay citas programadas para la fecha y el consultorio indicados</td>
									</tr>
									@endif
								</tbody>
							</table>
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
<?php
$j = $j + 1;
?>
@endforeach
@endif


<!-- Modal -->
<div class="modal fade bs-example-modal-lg" id="modalNuevo" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-primary">
							<div class="panel-heading">Crear Nueva Cita Médica</div>
							<div class="panel-body">
								<h5 style="border-left: 6px solid; border-color: #f7a30b; background-color: #f3f595; padding: 10px;"><b>Nota:</b> Si crea una cita con una fecha diferente a <b>{{$fecha}}</b> será redirigido a la gestión de citas con la nueva fecha para que pueda visualizar la cita que acaba de crear</h5>
								{{ Form::open(['route'=>'citas_medicas.store_cita','method'=>'post','class'=>'form-horizontal']) }}
								<input type="hidden" name="variables_url" value="{{$variables_url}}" />
								<input type="hidden" name="estado" value="PENDIENTE" />
								<div class="form-group">
									<div class="col-md-4">
										<label>Fecha Cita</label>
										<input type="date" class="form-control" id="f" name="fecha" required>
									</div>
									<div class="col-md-4">
										<label>Hora Inicio Cita</label>
										<input type="time" class="form-control" id="hi" name="hora_inicio" required>
									</div>
									<div class="col-md-4">
										<label>Hora Fin Cita</label>
										<input type="time" class="form-control" id="hf" name="hora_fin" required>
									</div>
								</div>
								<div class="form-group">
									<div class="col-md-12">
										<label>Consultorio</label>
										<select class="select2" style="width: 100%;" id="s1" name="consultorio_id" required>
											@if($consultorios!=null)
											@foreach($consultorios as $key=>$value)
											<option value="{{$key}}">{!!$value!!}</option>
											@endforeach
											@endif
										</select>
									</div>
									<div class="col-md-12">
										<label>Profesional de la Salud</label>
										<select class="select2" style="width: 100%;" id="s2" name="profesional_id" required>
											@if($profesionales!=null)
											@foreach($profesionales as $key=>$value)
											<option value="{{$key}}">{!!$value!!}</option>
											@endforeach
											@endif
										</select>
									</div>
									<div class="col-md-12">
										<label>Paciente</label>
										<select class="select2" style="width: 100%;" id="s3" name="paciente_id" required>
											@if($pacientes!=null)
											@foreach($pacientes as $key=>$value)
											<option value="{{$key}}">{!!$value!!}</option>
											@endforeach
											@endif
										</select>
									</div>
								</div>
								<div class="form-group">
									<div class="col-md-12">
										<p style="color: #f54805; font-weight: bold;">Verifique la disponibilidad del horario y el profesional de la salud escogido <a style="cursor: pointer;" onclick="validar()">haciendo clic aquí</a>, sino verifica y dicho horario ya está ocupado o dicho profesional ya tiene cita asignada en ese horario; perderá la información que acaba de indicar en la cita</p>
									</div>
								</div>
								<div class="form-group">
									<div class="col-md-12" id="html">
									</div>
								</div>
								<div class="form-group">
									<div class="col-md-12" style="margin-top: 20px;">
										<button id="btn" style="display: none;" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Cita</button>
										<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
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
</div>


@endsection
@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		//
	});

	function cita() {
		$('.select2').select2();
		$("#btn").attr('style', 'display: none;');
	}

	function cambiar() {
		var f = $("#fecha").val();
		if (f != '') {
			location.href = "{{url('')}}/citas_medicas/agenda/citas?id={{$app}}&id_modelo={{$modelo}}&fecha=" + f;
		} else {
			Swal.fire({
				icon: 'error',
				title: 'Atención!',
				text: 'Debe indicar la fecha para proceder'
			});
		}
	}

	function cambiarEstado(id) {
		var estado = $("#" + id).val();
		$.ajax({
			type: 'GET',
			url: "{{url('')}}/" + "citas_medicas/agenda/citas/" + id.split('_')[1] + "/estado/" + estado + "/cambiar",
			data: {},
		}).done(function(msg) {
			var m = JSON.parse(msg);
			Swal.fire({
				icon: m.icon,
				title: m.title,
				text: m.text
			});
		});
	}

	function validar() {
		$("#html").html("");
		var fecha = $("#f").val();
		var hi = $("#hi").val();
		var hf = $("#hf").val();
		var con = $("#s1").val();
		var pro = $("#s2").val();
		if (fecha == '' || hi == '' || hf == '') {
			Swal.fire({
				icon: 'error',
				title: 'Atención',
				text: 'Debe indicar Fecha, Hora Inicio, Hora Fin, Consultorio y Profesional de la Salud para verificar'
			});
		} else {
			$.ajax({
				type: 'GET',
				url: "{{url('')}}/" + "citas_medicas/agenda/citas/" + fecha + "/" + hi + "/" + hf + "/" + con + "/" + pro + "/verificar",
				data: {},
			}).done(function(msg) {
				var m = JSON.parse(msg);
				if (m.disponibilidad == 'SI') {
					$("#btn").attr('style', 'display: initial;');
				}
				if (m.html != 'NO') {
					$("#html").html(m.html);
				}
				Swal.fire({
					icon: m.icon,
					title: m.title,
					text: m.text
				});
			});
		}
	}
</script>
@endsection