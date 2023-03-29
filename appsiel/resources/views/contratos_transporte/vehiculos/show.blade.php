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
					<a href="#" class="list-group-item list-group-item-action"><b>Número Interno:</b> {{$v->int}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Placa:</b> {{$v->placa}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Número Vinculación:</b> {{$v->numero_vin}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Número Motor:</b> {{$v->numero_motor}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Modelo:</b> {{$v->modelo}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Marca:</b> {{$v->marca}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Clase:</b> {{$v->clase}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Color:</b> {{$v->color}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Cilindraje:</b> {{$v->cilindraje}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Capacidad:</b> {{$v->capacidad}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Fecha Control Kilometraje:</b> {{$v->fecha_control_kilometraje}}</a>
				</div>
			</div>
			<div class="col-md-4">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Propietario del Vehículo
					</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Documento:</b> {{$v->propietario->tercero->numero_identificacion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Propietario:</b> {{$v->propietario->tercero->descripcion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>¿Puede Generar Planilla?</b> {{$v->propietario->genera_planilla}}</a>
				</div>
			</div>
			<div class="col-md-4">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Documentos del Vehículo
					</a>
					@if(count($v->documentosvehiculos)>0)
					@foreach($v->documentosvehiculos as $d)
					<a class="list-group-item list-group-item-action">
						<p>
							<b>Nro. Documento:</b> {{$d->nro_documento}}<br>
							<b>Documento:</b> {{$d->documento}}<br>
							<b>Inicio Vigencia:</b> {{$d->vigencia_inicio}}<br>
							<b>Vence:</b> {{$d->vigencia_fin}}<br>
							<b onclick="ir(this.id)" id="{{$d->recurso}}" style="cursor: pointer; color: blue;">Ver Documento</b>
						</p>
					</a>
					@endforeach
					@else
					<a href="#" class="list-group-item list-group-item-action list-group-item-danger">El vehículo no tiene documentos asociados</a>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script>
	function ir(id) {
		var url = "{{config('configuracion.url_instancia_cliente')}}/storage/app/" + id;
		window.open(url, '', 'width=800,height=500,left=50,top=50,toolbar=yes');
	}
</script>
@endsection