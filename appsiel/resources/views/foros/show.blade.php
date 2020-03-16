@extends('layouts.principal')

@section('webstyle')
<style>
	.chat-list {
		padding: 0;
		font-size: .8rem;
	}

	.chat-list li {
		margin-bottom: 10px;
		/*overflow: auto;*/
		color: #ffffff;
		margin-top: 40px !important;
	}

	.chat-list .chat-img {
		float: left;
		width: 48px;
	}

	.chat-list .chat-img img {
		-webkit-border-radius: 50px;
		-moz-border-radius: 50px;
		border-radius: 50px;
		width: 100%;
	}

	.chat-list .chat-message {
		-webkit-border-radius: 50px;
		-moz-border-radius: 50px;
		border-radius: 50px;
		background: #7a32c1;
		display: inline-block;
		padding: 10px 20px;
		position: relative;
	}

	.chat-list .chat-message:before {
		content: "";
		position: absolute;
		top: 15px;
		width: 0;
		height: 0;
	}

	.chat-list .chat-message h5 {
		margin: 0 0 5px 0;
		font-weight: 600;
		line-height: 100%;
		font-size: .9rem;
	}

	.chat-list .chat-message p {
		line-height: 18px;
		margin: 0;
		padding: 0;
	}

	.chat-list .chat-body {
		margin-left: 20px;
		float: left;
		width: 70%;
	}

	.chat-list .in .chat-message:before {
		left: -12px;
		border-bottom: 20px solid transparent;
		border-right: 20px solid #7a32c1;
	}

	.chat-list .out .chat-img {
		float: right;
	}

	.chat-list .out .chat-body {
		float: right;
		margin-right: 20px;
		text-align: right;
	}

	.chat-list .out .chat-message {
		background: #00d79e;
	}

	.chat-list .out .chat-message:before {
		right: -12px;
		border-bottom: 20px solid transparent;
		border-left: 20px solid #00d79e;
	}

	.card .card-header:first-child {
		-webkit-border-radius: 0.3rem 0.3rem 0 0;
		-moz-border-radius: 0.3rem 0.3rem 0 0;
		border-radius: 0.3rem 0.3rem 0 0;
	}

	.card .card-header {
		background: #17202b;
		border: 0;
		font-size: 1rem;
		padding: .65rem 1rem;
		position: relative;
		font-weight: 600;
		color: #ffffff;
	}

	.content {
		margin-top: 40px;
	}
</style>
@endsection

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="row">
	<div class="col-lg-10 col-lg-offset-1">
		<div class="panel panel-primary">
			<div class="panel-heading" style="text-align: center">
				<h3>Foros de Discusión<h3>
						<h4>
							Año lectivo: {{$periodo->descripcion}}</br>
							Curso: {{$curso->descripcion}}</br>
							Asignatura: {{$materia->descripcion}}
						</h4>
			</div>
			<div class="panel-body" style="font-size: 16px !important;">
				<div class="col-md-12">
					<div class="card" style="font-size: 16px !important;">
						<div class="card-header" style="font-size: 16px !important;">{{$foro->titulo." (AUTOR: ".$foro->user->name.")"}}</div>
						<div class="card-body height3" style="padding: 20px;">
							{{$foro->contenido}}
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="card" style="font-size: 16px !important;">
						<div class="card-header" style="font-size: 16px !important;">PARTICIPACIONES EN EL FORO</div>
						<div class="card-body height3" style="padding: 20px;">
							<div class="col-md-12">
								<a style="cursor: pointer; font-size:16px;" data-toggle="modal" data-target="#exampleModal" class="btn btn-sm btn-primary"><i class="fa fa-comment"></i> Responder al Tema </a>
							</div>
							@if(count($respuestas)>0)
							<ul class="chat-list" style="font-size: 16px !important;">
								@foreach($respuestas as $r)
								<div class="col-md-12">
									@if($r->user_id==Auth::user()->id)
									<li class="in">
										<div class="chat-img">
											<img alt="Avtar" src="https://bootdey.com/img/Content/avatar/avatar1.png">
										</div>
										<div class="chat-body">
											<div class="chat-message">
												<h4 style="text-decoration: underline;">{{$r->user->name}}</h4>
												<p>{{$r->contenido}}</p>
											</div>
										</div>
									</li>
									@else
									<li class="out">
										<div class="chat-img">
											<img alt="Avtar" src="https://bootdey.com/img/Content/avatar/avatar6.png">
										</div>
										<div class="chat-body">
											<div class="chat-message">
												<h4 style="text-decoration: underline;">{{$r->user->name}}</h4>
												<p>{{$r->contenido}}</p>
											</div>
										</div>
									</li>
									@endif
								</div>
								@endforeach
							</ul>
							@else
							<div class="col-md-12">
								<h4 class="danger"><i class="fa fa-warning"> </i> Ups! Parece que nadie ha escrito nada, ¿Desea ser el primero? <a style="cursor: pointer;" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-bullhorn"></i> Escriba una respuesta al tema de discusión</a></h4>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Responder al Tema</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="POST" action="{{route('foros.guardarrespuesta')}}" id="nueva">
					<input type="hidden" name="periodo_id" value="{{$periodo->id}}" />
					<input type="hidden" name="curso_id" value="{{$curso->id}}" />
					<input type="hidden" name="asignatura_id" value="{{$materia->id}}" />
					<input type="hidden" name="app" value="{{$app}}" />
					<input type="hidden" name="foro_id" value="{{$foro->id}}" />
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
		var c = $("#contenido").val();
		if (c == "") {
			Swal.fire(
				'Información',
				'Debe indicar contenido',
				'error'
			);
			return;
		}
		$("#nueva").submit();
	}
</script>
@endsection