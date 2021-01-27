<h3 class="my-3" align="center">Guías Académicas para la asignatura "{{ $asignatura->descripcion }}" </h3>

<div class="table-responsive gua">
	<table class="table table-bordered table-striped" id="myTable">
		{{ Form::bsTableHeader([ 'Fecha', 'Descripción', 'Semana académica', 'Periodo', 'Curso', 'Asignatura', 'Profesor', 'Descargar']) }}
		<tbody>
			@foreach ($planes as $fila)
				<tr>
					<td>
						{{ $fila->fecha }}
					</td>
					<td>
						{{ $fila->descripcion }}
					</td>
					<td>
						{{ $fila->semana }}
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

						<a href="{{ config('configuracion.url_instancia_cliente').'/storage/app/planes_clases/'.$fila->archivo_adjunto }}" class="btn btn-success btn-sm" target="_blank"> <i class="fa fa-file"></i>  {{ $fila->archivo_adjunto }} </a>

						
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>