@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-4">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Datos del Vehículo
					</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Número Interno:</b> {{$v->vehiculo->int}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Placa:</b> {{$v->vehiculo->placa}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Número Vinculación:</b> {{$v->vehiculo->numero_vin}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Número Motor:</b> {{$v->vehiculo->numero_motor}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Modelo:</b> {{$v->vehiculo->modelo}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Marca:</b> {{$v->vehiculo->marca}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Clase:</b> {{$v->vehiculo->clase}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Color:</b> {{$v->vehiculo->color}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Cilindraje:</b> {{$v->vehiculo->cilindraje}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Capacidad:</b> {{$v->vehiculo->capacidad}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Fecha Control Kilometraje:</b> {{$v->vehiculo->fecha_control_kilometraje}}</a>
				</div>
			</div>
			<div class="col-md-4">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Propietario del Vehículo
					</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Documento:</b> {{$v->vehiculo->propietario->tercero->numero_identificacion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Propietario:</b> {{$v->vehiculo->propietario->tercero->descripcion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>¿Puede Generar Planilla?</b> {{$v->vehiculo->propietario->genera_planilla}}</a>
				</div>
			</div>
			<div class="col-md-4">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Documento del Vehículo
					</a>
					<a class="list-group-item list-group-item-action">
						<p>
							<b>Nro. Documento:</b> {{$v->nro_documento}}<br>
							<b>Documento:</b> {{$v->documento}}<br>
							<b>Inicio Vigencia:</b> {{$v->vigencia_inicio}}<br>
							<b>Vence:</b> {{$v->vigencia_fin}}<br>
							<b onclick="ir(this.id)" id="{{$v->recurso}}" style="cursor: pointer; color: blue;">Ver Documento</b>
						</p>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script>
	function ir(id) {
		var url = "{{url('')}}/appsiel/storage/app/" + id;
		window.open(url, '', 'width=800,height=500,left=50,top=50,toolbar=yes');
	}
</script>
@endsection