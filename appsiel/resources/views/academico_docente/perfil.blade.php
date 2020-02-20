@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="row">
		<div class="col col-sm-10 col-sm-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading" align="center">
					<h3>Perfil del usuario</h3>
				</div>

				<div class="panel-body">
					<table class="table table-striped">
						{{ Form::bsTableHeader(['Nombre','Correo','Acción']) }}
						<tbody>
								<tr>
									<td class="table-text"><div>{{ $usuario->name }}</div></td>
									<td class="table-text"><div>{{ $usuario->email }}</div></td>
									<td>
										<a id="btn_add" name="btn_add" class="btn btn-warning btn-sm" href="{{ url('/usuario/perfil/cambiarpasswd?ruta=profesor/perfil') }}"><i class="fa fa-btn fa-key"></i>Cambiar contraseña</a>
									</td>
								</tr>
						</tbody>
					</table>
					<a href="{{ url('/dashboard') }}" class="btn btn-danger">Cancelar</a>
				</div>
			</div>
		</div>
	</div>
@endsection