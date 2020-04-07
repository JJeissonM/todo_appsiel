@extends('layouts.principal')

@section('webstyle')
<style>
	.item-foreach {
		list-style: none;
		padding: 10px;
		margin-bottom: 5px;
		-webkit-box-shadow: 0px 0px 5px 0px rgba(0, 0, 0, 0.3);
		-moz-box-shadow: 0px 0px 5px 0px rgba(0, 0, 0, 0.3);
		box-shadow: 0px 0px 5px 0px rgba(0, 0, 0, 0.3);
	}

	.warning {
		color: #a94442;
		background-color: #f2dede;
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
				<div class="list-group">
					<a style="font-size: 18px;" href="#" class="list-group-item list-group-item-action active">
						Mantenimientos del Vehículo
					</a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{"PLACA: ".$v->placa." - MARCA: ".$v->marca." - CLASE: ".$v->clase." - MODELO: ".$v->modelo." - INTERNO: ".$v->int." - NÚMERO VINCULACIÓN: ".$v->numero_vin}}</b></a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{"PROPIETARIO: ".$v->propietario->tercero->numero_identificacion." - ".$v->propietario->tercero->descripcion." ".$v->propietario->tercero->razon_social}}</b></a>
				</div>
			</div>
			<div class="col-md-12">
				@foreach($periodos as $key=> $value)
				<div class="panel panel-primary">
					<div class="panel-heading">{{"PERÍODO: ".$key}}</div>
					<div class="panel-body">
						<a href="{{route('mantenimiento.create',[$v->id,$value['anioperiodo_id']]).$variables_url}}" class="btn btn-success"><b>Registrar Mantenimiento</b></a>
						@if(count($value['mantenimientos'])==0)
						<div class="list-group" style="margin-top: 20px;">
							<a href="#" class="list-group-item list-group-item-action list-group-item-danger"><b>No se ha registrado mantenimientos en el período</b></a>
						</div>
						@endif
						@if(count($value['mantenimientos'])>0)
						<div class="table-responsive" style="margin-top: 20px;">
							<table class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>Mantenimientos</th>
										<th>Reportes</th>
										<th>Observaciones</th>
									</tr>
								</thead>
								<tbody>
									@foreach($value['mantenimientos'] as $m)
									<tr>
										<td>
											<div class="item-foreach">
												<b>FECHA: </b>{{$m->fecha}}<br>
												<b>SEDE: </b>{{$m->sede}}<br>
												<b>REVISADO: </b>{{$m->revisado}}<br>
												<a onclick="mantenimiento(this.id)" id="{{$m->id}}" data-toggle="modal" data-target="#myModal1" style="margin-bottom: 5px;" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i> Agregar Reporte</a><br>
												<a onclick="observacion(this.id)" id="{{$m->id}}" data-toggle="modal" data-target="#myModal2" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i> Agregar Observación</a>
											</div>
										</td>
										<td>
											@if(count($m->mantreportes)>0)
											<ol>
												@foreach($m->mantreportes as $r)
												<li class="item-foreach">
													<b>FECHA SUCESO: </b>{{$r->fecha_suceso}}<br>
													<b>REPORTE: </b>{{$r->reporte}}
													<a href="{{route('mantenimiento.deletereporte',$r->id).$variables_url}}" class="btn btn-danger btn-xs" style="float: right; text-align: center;"><i class="fa fa-trash-o"></i></a>
												</li>
												@endforeach
											</ol>
											@else
											<ol>
												<li class="item-foreach warning">
													<b>No hay reportes en el mantenimiento</b>
												</li>
											</ol>
											@endif
										</td>
										<td>
											@if(count($m->mantobs)>0)
											<ol>
												@foreach($m->mantobs as $o)
												<li class="item-foreach">
													<b>FECHA SUCESO: </b>{{$o->fecha_suceso}}<br>
													<b>OBSERVACIÓN: </b>{{$o->observacion}}
													<a href="{{route('mantenimiento.deleteobs',$o->id).$variables_url}}" class="btn btn-danger btn-xs" style="float: right; text-align: center;"><i class="fa fa-trash-o"></i></a>
												</li>
												@endforeach
											</ol>
											@else
											<ol>
												<li class="item-foreach warning">
													<b>No hay observaciones en el mantenimiento</b>
												</li>
											</ol>
											@endif
										</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
						@endif
					</div>
				</div>
				@endforeach
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="myModal1" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Crear Reporte</h4>
			</div>
			<div class="modal-body">
				{{ Form::open(['route'=>'mantenimiento.storemant', 'id'=>'form-mant','method'=>'post','class'=>'form-horizontal']) }}
				<input type="hidden" name="vehiculo_id" value="{{$v->id}}" />
				<input type="hidden" name="variables_url" value="{{$variables_url}}" />
				<input type="hidden" name="mantenimiento_id" id="txtmant" />
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Fecha Suceso</label>
						<input type="date" class="form-control" name="fecha_suceso" required />
					</div>
					<div class="col-md-12">
						<label class="control-label">Reporte</label>
						<input type="text" class="form-control" name="reporte" required />
					</div>
				</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary" onclick="mant()">Guardar</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="modal fade" id="myModal2" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Crear Observación</h4>
			</div>
			<div class="modal-body">
				{{ Form::open(['route'=>'mantenimiento.storeobs', 'id'=>'form-obs','method'=>'post','class'=>'form-horizontal']) }}
				<input type="hidden" name="vehiculo_id" value="{{$v->id}}" />
				<input type="hidden" name="variables_url" value="{{$variables_url}}" />
				<input type="hidden" name="mantenimiento_id" id="txtobs" />
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Fecha Suceso</label>
						<input type="date" class="form-control" name="fecha_suceso" required />
					</div>
					<div class="col-md-12">
						<label class="control-label">Observación</label>
						<input type="text" class="form-control" name="observacion" required />
					</div>
				</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary" onclick="obs()">Guardar</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection

@section('scripts')
<script>
	function mantenimiento(id) {
		$("#txtmant").val(id);
	}

	function observacion(id) {
		$("#txtobs").val(id);
	}

	function mant() {
		$("#form-mant").submit();
	}

	function obs() {
		$("#form-obs").submit();
	}
</script>
@endsection