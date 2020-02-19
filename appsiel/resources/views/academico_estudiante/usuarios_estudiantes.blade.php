@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	<?php 
		$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
	?>

	@include('layouts.mensajes')

	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable">
			{{ Form::bsTableHeader(['Perfil','Nombre','Email','Acción']) }}
			<tbody>
				@foreach ($registros as $estudiante)
					<tr>
						<td> {{ $estudiante->role }} </td>
						<td> {{ $estudiante->name }} </td>
						<td> {{ $estudiante->email }} </td>
						<td> 
							{{ Form::bsBtnEdit( 'academico_estudiante/modificar_usuario_estudiante/'.$estudiante->id.$variables_url ) }}
							
							<a href="{{ url('/core/usuario/cambiarpasswd'.$variables_url.'&id_user='.$estudiante->id.'&ruta=academico_estudiante/usuarios_estudiantes') }}" class="btn btn-info btn-sm"><i class="fa fa-btn fa-key"></i> Cambiar contraseña</a>

						</td>
					</tr>
				@endforeach
			</tbody>

		</table>
	</div>
@endsection