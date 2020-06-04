<tr>
    <td> <div class="elemento_modificar" title="Doble click para modificar." data-asignatura_id="{{ $asignatura_id }}"> {{ $orden_boletin }} </div>  </td>
    <td> {{ $area_descripcion }} </td>
    <td> {{ $asignatura_descripcion }} </td>
    <td> {{ $intensidad_horaria }} </td>
    <td> {{ $maneja_calificacion }} </td>
    <td> 
    	@if( $profesor == 'No' )
    		<button class="btn btn-danger btn-sm eliminar" data-periodo_lectivo_id="{{ $periodo_lectivo_id }}" data-curso_id="{{ $curso_id }}" data-asignatura_id="{{ $asignatura_id }}"><i class="fa fa-btn fa-trash"></i> </button>
    	@else
    		Profesor asignado: {{ $profesor }}
    	@endif
    </td>
</tr>