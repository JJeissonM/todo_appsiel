@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-6">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Datos del Conductor
					</a>
					@if($v->conductor->estado=='Activo')
					<a href="#" class="list-group-item list-group-item-action list-group-item-success"><b>Estado:</b> {{$v->conductor->estado}}</a>
					@else
					<a href="#" class="list-group-item list-group-item-action list-group-item-danger"><b>Estado:</b> {{$v->conductor->estado}}</a>
					@endif
					<a href="#" class="list-group-item list-group-item-action"><b>Documento:</b> {{$v->conductor->tercero->numero_identificacion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Conductor:</b> {{$v->conductor->tercero->descripcion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Dirección:</b> {{$v->conductor->tercero->direccion1}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Teléfono:</b> {{$v->conductor->tercero->telefono1}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Correo:</b> {{$v->conductor->tercero->email}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>En el Sistema Desde:</b> {{$v->conductor->created_at}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Actualizado:</b> {{$v->conductor->updated_at}}</a>
				</div>
			</div>
			<div class="col-md-6">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Documento del Conductor
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