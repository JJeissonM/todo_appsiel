@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<h3 align="center">Mis Actividades asignadas</h3>

	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable">
			{{ Form::bsTableHeader(['Periodo','Asignatura','Descripción actividad','Temática','Fecha de entrega','Acción']) }}
			<tbody>
				@foreach ($actividades as $fila)
					<tr>
						<td>
							{{ $fila->periodo_descripcion }}
						</td>
						<td>
							{{ $fila->asignatura_descripcion }}
						</td>
						<td>
							{{ $fila->descripcion }}
						</td>
						<td>
							{{ $fila->tematica }}
						</td>
						</td>
						<td>
							{{ $fila->fecha_entrega }}
						</td>
						<td>
							{{ Form::bsBtnVer('actividades_escolares/hacer_actividad/'.$fila->id) }}
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endsection