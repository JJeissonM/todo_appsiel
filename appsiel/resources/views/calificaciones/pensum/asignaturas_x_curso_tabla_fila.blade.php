<tr>
    <td>
        <div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('calificaciones_cambiar_orden_asignatura') . "/" . $periodo_lectivo_id . "/" . $curso_id . "/" . $asignatura_id }}"> {{ $orden_boletin }} 
        </div>
    </td>
    <td> {{ $area_descripcion }} </td>
    <td> {{ $asignatura_descripcion }} </td>
    <td>
        <div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('calificaciones_cambiar_intensidad_horaria_asignatura') . "/" . $periodo_lectivo_id . "/" . $curso_id . "/" . $asignatura_id }}"> {{ $intensidad_horaria }} 
        </div>
    </td>
    <td> {{ $maneja_calificacion }} </td>
    <td> 
    	@if( $profesor == 'No' )
    		<button class="btn btn-danger btn-sm eliminar" data-periodo_lectivo_id="{{ $periodo_lectivo_id }}" data-curso_id="{{ $curso_id }}" data-asignatura_id="{{ $asignatura_id }}"><i class="fa fa-btn fa-trash"></i> </button>
    	@else
    		Profesor asignado: {{ $profesor }}
    	@endif
    </td>
</tr>