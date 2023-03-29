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
					@if($v->estado=='Activo')
					<a href="#" class="list-group-item list-group-item-action list-group-item-success"><b>Estado:</b> {{$v->estado}}</a>
					@else
					<a href="#" class="list-group-item list-group-item-action list-group-item-danger"><b>Estado:</b> {{$v->estado}}</a>
					@endif
					<a href="#" class="list-group-item list-group-item-action"><b>Documento:</b> {{$v->tercero->numero_identificacion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Conductor:</b> {{$v->tercero->descripcion}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Dirección:</b> {{$v->tercero->direccion1}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Teléfono:</b> {{$v->tercero->telefono1}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Correo:</b> {{$v->tercero->email}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>En el Sistema Desde:</b> {{$v->created_at}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Actualizado:</b> {{$v->updated_at}}</a>
				</div>
			</div>
			<div class="col-md-6">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Documentos del Conductor
					</a>
					@if(count($v->documentosconductors)>0)
					@foreach($v->documentosconductors as $d)
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
					<a href="#" class="list-group-item list-group-item-action list-group-item-danger">El conductor no tiene documentos asociados</a>
					@endif
				</div>
			</div>
			<div class="col-md-12">
				<h3 style="padding-left: 15px;">Vehículos del Conductor</h3>
				@if($vehiculos!=null)
				@foreach($vehiculos as $v)
				<div class="col-md-3" style="background: url({{asset('img/vehiculo.png')}}); background-position: center; background-attachment: contain; background-size: cover;">
					<div class="list-group">
						<a href="#" class="list-group-item list-group-item-action active">
							Interno: {{$v['interno']}}
						</a>
						@if($v['id']==0)
						<a class="list-group-item list-group-item-action list-group-item-warning" style="opacity: 0.9;">
							@else
							<a class="list-group-item list-group-item-action list-group-item-success" style="opacity: 0.9;">
								@endif
								<p>
									<b>Placa:</b> {{$v['placa']}}<br>
									<b>Modelo:</b> {{$v['modelo']}}<br>
									<b>Marca:</b> {{$v['marca']}}<br>
									<b>Clase:</b> {{$v['clase']}}<br>
									<b>Tipo:</b> {{$v['tipo']}}<br>
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
	function ir(id) {
		var url = "{{config('configuracion.url_instancia_cliente')}}/storage/app/" + id;
		window.open(url, '', 'width=800,height=500,left=50,top=50,toolbar=yes');
	}
</script>
@endsection