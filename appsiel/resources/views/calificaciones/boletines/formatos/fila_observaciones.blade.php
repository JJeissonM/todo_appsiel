<tr>
	<td colspan="{{$cant_columnas}}" style="font-size: {{$tam_letra}}mm; padding: 10px;">
		<b> {{ config('calificaciones.etiqueta_observaciones') }}: </b>
		&nbsp;&nbsp;&nbsp;
		@if( !is_null( $registro->observacion ) )
			{{ $registro->observacion->observacion }}
		@endif
	</td>
</tr>