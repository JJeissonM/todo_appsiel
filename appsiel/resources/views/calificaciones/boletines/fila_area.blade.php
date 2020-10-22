@if ( $area_anterior != $asignatura->area AND $mostrar_areas == 'Si')
	<tr style="font-size: {{$tam_letra}}mm; background: #ddd;">
		<td colspan="{{$cant_columnas}}">
			&nbsp;
			@if( $asignatura->asignatura->area->get_valor_eav( 122, $asignatura->area_id, 947) != "No" )
				<b> ÁREA: {{ strtoupper($asignatura->area) }}</b>
			@endif
		</td>
	</tr>
@endif