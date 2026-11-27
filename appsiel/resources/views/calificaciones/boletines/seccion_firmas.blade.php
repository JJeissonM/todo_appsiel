@if( $mostrar_escala_valoracion == 'Si')
	@include('calificaciones.boletines.escala_valoracion')
@else
	@include('calificaciones.boletines.tabla_firmas')
@endif
