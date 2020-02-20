@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable">

			<thead>
				<tr>
					<th>Año Lectivo</th>
					<th>Curso</th>
					<th>Asignatura</th>
					<th>IH</th>
					<th>Profesor</th>
				</tr>
			</thead>
			<tbody>
				@for ($i=0; $i < count($listado); $i++)
				<?php
					if( $listado[$i]['profesor'] == 'No'){
						$color = 'bgcolor="#FF8080"';
						$profesor = "No asignado";
					}else{
						$color = "";
						$profesor = $listado[$i]['profesor'];
					}/*
					$color = "";*/
				?>
				<tr>
					<td> {{ $listado[$i]['periodo_lectivo_descripcion'] }} </td>
					<td> {{ $listado[$i]['curso_descripcion'] }} </td>
					<td> {{ $listado[$i]['asignatura_descripcion'] }} </td>
					<td> {{ $listado[$i]['asignatura_intensidad_horaria'] }} </td>
					<td {!! $color !!}> {{ $profesor }} </td>
				</tr>
				@endfor
			</tbody>
		</table>
	</div>
@endsection
