<style type="text/css">

	table.encabezado{
		border: 1px solid;
		padding-top: -20px;
	}
	
	span.etiqueta{
		font-weight: bold;
		display: inline-block;
		width: 100px;
		text-align:right;
	}

</style>

<table class="encabezado">
	<tr>											
		@if($colegio->maneja_puesto=="Si")

			@if( !is_null($observacion) )
				
				<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombre_completo }}</td>
				
				@if($observacion->puesto=="")
					<td> <b> ¡¡Puesto No calculado!! </b> </td>
				@else
					<td><span class="etiqueta"> Puesto:  </span> {{ $observacion->puesto }} </td>
				@endif

			@else
				<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombre_completo }}</td>
			@endif

		@else
			<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombre_completo }}</td>
		@endif
		
	</tr>
	<tr>
		<td><span class="etiqueta">Periodo/Año:</span> {{ $periodo->descripcion }} &#47;  {{ $anio }}</td>
		<td><span class="etiqueta">Curso:</span> {{ $curso->descripcion }}</td>
		<td><span class="etiqueta">Ciudad:</span> {{ $colegio->ciudad }}</td>
	</tr>
</table>