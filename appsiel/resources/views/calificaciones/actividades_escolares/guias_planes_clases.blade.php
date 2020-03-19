@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<h3 align="center">Guías de planes de clases "{{ $asignatura->descripcion }}" </h3>

	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable">
			{{ Form::bsTableHeader(['Semana académica', 'Fecha', 'Periodo', 'Curso', 'Asignatura', 'Profesor', 'Imprimir']) }}
			<tbody>
				@foreach ($planes as $fila)
					<tr>
						<td>
							{{ $fila->semana }}
						</td>
						<td>
							{{ $fila->fecha }}
						</td>
						<td>
							{{ $fila->periodo_decripcion }}
						</td>
						<td>
							{{ $fila->curso_decripcion }}
						</td>
						<td>
							{{ $fila->asignatura_decripcion }}
						</td>
						<td>
							{{ $fila->profesor }}
						</td>
						<td>
							{{ Form::bsBtnPrint( 'sga_planes_clases_imprimir/'.$fila->id.'?id='.Input::get('id') ) }}
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endsection