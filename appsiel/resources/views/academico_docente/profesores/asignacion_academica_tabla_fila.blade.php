<tr>
	<td>{{ $curso_descripcion }}</td>
	<td>{{ $asignatura_descripcion }}</td>
	<td>{{ $intensidad_horaria }}</td>
	<td>
		<button data-asignacion_id="{{ $asignacion_id }}" class="btn btn-sm btn-danger btn-xs eliminar_asignacion">
		<i class="fa fa-trash"></i> Eliminar </button>
	</td>
</tr>