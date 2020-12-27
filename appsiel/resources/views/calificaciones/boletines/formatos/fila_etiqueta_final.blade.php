@if( $mostrar_etiqueta_final != 'No' )
	<tr style="font-size: {{$tam_letra}}mm;">
		<td colspan="{{$cant_columnas}}">
			@include('calificaciones.boletines.mostrar_etiqueta_final')
		</td>
	</tr>
@endif