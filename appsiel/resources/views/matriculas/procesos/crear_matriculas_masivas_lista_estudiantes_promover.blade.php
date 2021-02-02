<div class="table-responsive">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th><input type="checkbox" class="btn-gmail-check" checked="checked"></th>
				<td>Estudiante</td>
				<td>Grado / Curso Actual</td>
				<td>Perido final</td>
				<td>Nota final</td>
				<td>Nota Nivelación</td>
				<td style="background: #ddd;">&nbsp;</td> <!-- columna en negro para separar -->
				<td>Grado/Curso siguiente</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
			@foreach( $matriculas As $matricula )
				<tr>
					<td><input type="checkbox" value="{{ $matricula->id }}" class="btn-gmail-check" checked="checked"></td>
					<td>{{ $matricula->estudiante->tercero->descripcion }}</td>
					<td>{{ $matricula->curso->grado->descripcion }} / {{ $matricula->curso->descripcion }}</td>
					<td> </td>
					<td> </td>
					<td> </td>
					<td style="background: #ddd;"> &nbsp; </td>
					<td> </td>
					<td> </td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>