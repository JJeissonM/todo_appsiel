@if ( $area_anterior != $linea->asignacion_asignatura->asignatura->area->descripcion AND $mostrar_areas == 'Si')
	<tr style="background: #ddd;">
		<td colspan="{{$cant_columnas}}">
			&nbsp;
			@if( $linea->asignacion_asignatura->asignatura->area->get_valor_eav( 122, $linea->asignacion_asignatura->asignatura->area_id, 947) != "No" )
				<b> ÁREA: {{ strtoupper( $linea->asignacion_asignatura->asignatura->area->descripcion ) }}</b>
			@endif
		</td>
	</tr>
@endif