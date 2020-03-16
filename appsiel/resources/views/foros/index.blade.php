@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="row">
	<div class="col-lg-10 col-lg-offset-1">
		<div class="panel panel-primary">
			<div class="panel-heading" align="center">
				<h3>Foros de Discusión<h3>
						<h4>
							Año lectivo: {{$periodo->descripcion}}</br>
							Curso: {{$curso->descripcion}}</br>
							Asignatura: {{$materia->descripcion}}
						</h4>
			</div>
			<div class="panel-body">
				<div class="col-md-12">
					<a style="cursor: pointer; font-size:16px;" data-toggle="modal" data-target="#exampleModal" class="btn btn-sm btn-primary"><i class="fa fa-bullhorn"></i> Crear Nuevo Foro </a>
				</div>
				@if(count($foros)>0)
				<table class="table table-responsive">
					<thead>
						<tr>
							<th></th>
							<th>Título</th>
							<th>Autor</th>
							<th>Participar</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$contador = 1;
						?>
						@foreach ($foros as $f)
						<tr>
							<td>{{ $contador }}</td>
							<td>{{ $f->titulo }}</td>
							<td>{{ $f->user->name }}</td>
							<td>
								<a href="{{$f->url}}" style="cursor: pointer;" class="btn btn-sm btn-primary" title="Participar en el foro"><i class="fa fa-comment"></i></a>
							</td>
						</tr>
						<?php
						$contador++;
						?>
						@endforeach
					</tbody>
				</table>
				@else
				<div class="col-md-12">
					<h3><i class="fa fa-warning"> </i> Ups! Parece que nadie ha escrito nada, ¿Desea ser el primero? <a style="cursor: pointer;" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-bullhorn"></i> Escriba un tema de discusión</a></h3>
				</div>
				@endif
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Agregar Nuevo Tema</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="POST" action="{{route('foros.store')}}" id="nueva">
					<input type="hidden" name="periodo_id" value="{{$periodo->id}}" />
					<input type="hidden" name="curso_id" value="{{$curso->id}}" />
					<input type="hidden" name="asignatura_id" value="{{$materia->id}}" />
					<input type="hidden" name="app" value="{{$id}}" />
					<div class="form-group">
						<label class="control-label">Título</label>
						<input type="text" id="titulo" name="titulo" class="form-control" required>
					</div>
					<div class="form-group">
						<label class="control-label">Contenido</label>
						<input type="text" id="contenido" name="contenido" class="form-control" required>
					</div>
					{{ csrf_field() }}
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" onclick="ir()" class="btn btn-primary">Guardar</button>
			</div>
		</div>
	</div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
	function ir() {
		var t = $("#titulo").val();
		var c = $("#contenido").val();
		if (t == "" || c == "") {
			Swal.fire(
				'Información',
				'Debe indicar título y contenido',
				'error'
			);
			return;
		}
		$("#nueva").submit();
	}
</script>
@endsection