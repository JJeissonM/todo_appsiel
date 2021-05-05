@if( !is_null($doc_encabezado->datos_auxiliares_estudiante()) )
	<br>
	<div style="background: #c9efe2; display: inline;">
		<b>Estudiante:</b> {{ $doc_encabezado->datos_auxiliares_estudiante()->matricula->estudiante->tercero->descripcion }} &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; <b>Curso: </b> {{ $doc_encabezado->datos_auxiliares_estudiante()->matricula->curso->descripcion }}
	</div>
@endif