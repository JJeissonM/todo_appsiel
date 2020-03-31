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
						Datos del Artículo
					</a>
					<a href="#" class="list-group-item list-group-item-action"><b>{{$v->plantilla->titulo}}</b></a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-info"><b>Título: </b> {{$v->titulo}}</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-info"><b>Texto: </b> {{$v->texto}}</a>
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