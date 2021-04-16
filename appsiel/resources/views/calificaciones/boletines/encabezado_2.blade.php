<table class="encabezado">
	<tr>											
		@if($colegio->maneja_puesto=="Si")

			@if( !is_null($registro->observacion) )
				
				<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $registro->estudiante->tercero->descripcion }}</td>
				
				@if( $registro->observacion->puesto == "" )
					<td> <b> ¡¡Puesto No calculado!! </b> </td>
				@else
					<td><span class="etiqueta"> Puesto:  </span> {{ $registro->observacion->puesto }} </td>
				@endif

			@else
				<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $registro->estudiante->tercero->descripcion }}</td>
			@endif

		@else
			<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $registro->estudiante->tercero->descripcion }}</td>
		@endif
		
	</tr>
	<tr>
		<td><span class="etiqueta">Periodo/Año:</span> {{ $periodo->descripcion }} &#47;  {{ explode( "-", $periodo->fecha_desde )[0] }}</td>
		<td><span class="etiqueta">Curso:</span> {{ $curso->descripcion }}</td>
		<td><span class="etiqueta">Ciudad:</span> {{ $colegio->ciudad }}</td>
	</tr>
</table>