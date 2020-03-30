@extends('layouts.principal')

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
					<a href="#" class="list-group-item list-group-item-action active">
						Datos del Numeral de Tabla
					</a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{$v->plantillaarticulonumeral->plantillaarticulo->plantilla->titulo}}</b></a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{$v->plantillaarticulonumeral->plantillaarticulo->titulo}} </b> {{$v->plantillaarticulonumeral->plantillaarticulo->texto}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{$v->plantillaarticulonumeral->numeracion}}</b> {{$v->plantillaarticulonumeral->texto}}</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-info"><b>Campo:</b> {{$v->campo}}</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-info"><b>Valor:</b> {{$v->valor}}</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-info"><b>Creado:</b> {{$v->created_at}}</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-info"><b>Modificado:</b> {{$v->updated_at}}</a>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script>

</script>
@endsection