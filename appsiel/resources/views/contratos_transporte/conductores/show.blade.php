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
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-action active">
						Vehículos asociados al conductor y vehículos que ha usado en las rutas asignadas
					</a>
					@if($vehiculos!=null)
					@foreach($vehiculos as $v)
					@if($v['id']==0)
					<a class="list-group-item list-group-item-action list-group-item-success">
						@else
						<a class="list-group-item list-group-item-action list-group-item-info">
							@endif
							<p>
								<b>Placa:</b> {{$v['placa']}},<b> Interno:</b> {{$v['interno']}}, <b>Modelo:</b> {{$v['modelo']}}, <b>Marca:</b> {{$v['marca']}}, <b>Clase:</b> {{$v['clase']}}<br>
								<b>Tipo:</b> {{$v['tipo']}}
							</p>
						</a>
						@endforeach
						@else
						<a href="#" class="list-group-item list-group-item-action list-group-item-danger">El conductor no tiene vehículos asociados ni ha realizado rutas</a>
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
		var url = "{{url('')}}/appsiel/storage/app/" + id;
		window.open(url, '', 'width=800,height=500,left=50,top=50,toolbar=yes');
	}
</script>
@endsection