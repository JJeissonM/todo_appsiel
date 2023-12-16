<div style="text-align: justify;">
	Observaciones:

	@if( $resultado_academico == 'APROBÓ' )
		<br>
		<b>El estudiante es promovido al grado siguiente.</b>
	@endif

	<br>
	{{ $observacion_adicional }}
</div>

<br>

<div style="text-align: justify; font-size: 1em;">
	Para mayor constancia, se firma la presente en la ciudad de {{ $colegio->ciudad }} a los {{ $array_fecha[0] }} días del mes de {{ $array_fecha[1] }} de {{ $array_fecha[2] }}.
</div>