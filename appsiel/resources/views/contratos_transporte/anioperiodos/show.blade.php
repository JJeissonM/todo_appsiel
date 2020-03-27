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
						Datos del Período
					</a>
					<a href="#" class="list-group-item list-group-item-action"><b>ID:</b> {{$v->id}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Desde:</b> {{$v->inicio}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Hasta:</b> {{$v->fin}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Año:</b> {{$v->anio->anio}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Creado:</b> {{$v->created_at}}</a>
					<a href="#" class="list-group-item list-group-item-action"><b>Modificado:</b> {{$v->updated_at}}</a>
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