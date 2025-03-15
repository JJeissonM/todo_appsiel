@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-12" style="margin-bottom: 40px;">
				<a onclick="abrir()" class="btn btn-primary"><i class="fa fa-car"></i> ASOCIAR VEHÍCULO</a>
			</div>
			<div class="col-md-12" style="display: none;" id="asociar">
				<div class="modal-body">
					{{ Form::open(['route'=>'cte_conductor.vehiculoStore', 'id'=>'form-mant','method'=>'post','class'=>'form-horizontal']) }}
					<input type="hidden" name="variables_url" value="{{$variables_url}}" />
					<input type="hidden" name="conductor_id" value="{{$c->id}}" />
					<div class="form-group">
						<label class="control-label">Vehículo</label>
						<select class="form-control select2" style="width: 100% !important;" name="vehiculo_id" required>
							@if($todosVehiculos!=null)
								@foreach($todosVehiculos as $key=>$value)
									<option value="{{$key}}">{!!$value!!}</option>
								@endforeach
							@else
								<option value="0">No hay vehículos habilitados, si continua no se asociará el vehículo.</option>
							@endif
						</select>
					</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" onclick="cerrar()">Cancelar</button>
					<button type="button" class="btn btn-primary" onclick="enviar()">Guardar</button>
				</div>
			</div>
			<div class="col-md-4">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Datos del Conductor
					</a>
					@if($c->estado=='Activo')
					<a href="#" class="list-group-item list-group-item-action list-group-item-success"><b>Estado:</b> {{$c->estado}}</a>
					@else
					<a href="#" class="list-group-item list-group-item-action list-group-item-danger"><b>Estado:</b> {{$c->estado}}</a>
					@endif
					<a href="#" class="list-group-item list-group-item-action"><b>Documento:</b> {{$c->tercero->numero_identificacion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Conductor:</b> {{$c->tercero->descripcion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Dirección:</b> {{$c->tercero->direccion1}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Teléfono:</b> {{$c->tercero->telefono1}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Correo:</b> {{$c->tercero->email}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>En el Sistema Desde:</b> {{$c->created_at}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Actualizado:</b> {{$c->updated_at}}</a>
				</div>
			</div>
			<div class="col-md-8">
				<h3 style="padding-left: 15px;">Vehículos del Conductor</h3>
				@if(count($vehiculos)>0)
				@foreach($vehiculos as $v)
				<div class="col-md-4" style="background: url({{asset('img/vehiculo.png')}}); background-position: center; background-attachment: contain; background-size: cover;">
					<div class="list-group">
						<a href="#" class="list-group-item list-group-item-action active">
							Interno: {{$v->vehiculo->int}}
						</a>
						<a class="list-group-item list-group-item-action" style="opacity: 0.9;">
							<p>
								<b>Placa:</b> {{$v->vehiculo->placa}}<br>
								<b>VIN:</b> {{$v->vehiculo->numero_vin}}<br>
								<b>Modelo:</b> {{$v->vehiculo->modelo}}<br>
								<b>Marca:</b> {{$v->vehiculo->marca}}<br>
								<b class="btn btn-xs btn-danger" onclick="eliminar({{$v->id}},this.id)" id="{{$variables_url}}">Retirar Vehículo</b>
							</p>
						</a>
					</div>
				</div>
				@endforeach
				@else
				<a href="#" class="list-group-item list-group-item-action list-group-item-danger">El conductor no tiene vehículos asociados</a>
				@endif
			</div>
		</div>
	</div>
</div>

@endsection

@section('scripts')
<script>
	$(document).ready(function() {
		$('.select2').select2();
	});

	function ir(id) {
		var url = "{{url('')}}/appsiel/storage/app/" + id;
		window.open(url, '', 'width=800,height=500,left=50,top=50,toolbar=yes');
	}

	function enviar() {
		$("#form-mant").submit();
	}

	function abrir() {
		$("#asociar").fadeIn(1000);
	}

	function cerrar() {
		$("#asociar").fadeOut(1000);
	}

	function eliminar(id, variables) {
		location.href = "{{url('')}}/cte_conductores/vehiculos/" + id + "/delete" + variables;
	}
</script>
@endsection