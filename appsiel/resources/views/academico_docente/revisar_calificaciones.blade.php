@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="row">
		<div class="col-lg-10 col-lg-offset-1">
			<div class="table-responsive">
				<table class="table table-bordered table-striped" id="myTable">
					<thead>
						<tr>
							<th>Año</th>
							<th>Periodo</th>
							<th>Curso</th>
							<th>Estudiante</th>
							<th>Asignatura</th>
							<th>Calificación</th>
							<th>Logros</th>
						</tr>
					</thead>

					<tbody>
						<?php $n=count($calificaciones);?>
						@for ($i=0;$i<$n;$i++)
							<tr>
								<td class="table-text"><div>{{ $calificaciones[$i]['anio'] }}</div></td>
								<td class="table-text"><div>{{ $calificaciones[$i]['periodo']->descripcion }}</div></td>
								<td class="table-text"><div>{{ $calificaciones[$i]['curso']->descripcion }}</div></td>
								<td class="table-text"><div>{{ $calificaciones[$i]['estudiante'] }}</div></td>
								<td class="table-text"><div>{{ $calificaciones[$i]['asignatura']->descripcion }}</div></td>
								<td class="table-text"><div>{{ $calificaciones[$i]['calificacion'] }}</div></td>
								<td class="table-text"><div>{!! $calificaciones[$i]['logros'] !!}</div></td>
							</tr>
						@endfor
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection

@section('scripts')

<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script>
	$(document).ready( function () {
		$('#myTable').DataTable();
		@yield('j_query')
	} );
</script>
@endsection