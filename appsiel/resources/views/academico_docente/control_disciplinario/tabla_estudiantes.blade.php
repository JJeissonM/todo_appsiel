<table class="table table-responsive" id="tabla" style=" border: 1px solid; border-collapse: collapse;">
	<thead>
		<tr>
			<th style="font-size:12px;border: 1px solid; border-collapse: collapse;">No.</th>
			<th style="font-size:12px;border: 1px solid; border-collapse: collapse;">Estudiante</th>
			@foreach($asignaturas as $asignatura)
				<th style="font-size:12px;border: 1px solid; border-collapse: collapse;"> {{ $asignatura->abreviatura }} </th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php 
			$linea=1;
		?>
		@foreach($estudiantes as $estudiante)
			<tr> 
				<td style="font-size:12px;border: 1px solid; border-collapse: collapse;">
					<b>{{ $linea }}</b>
				</td>
				<td width="250px" style="font-size:12px;border: 1px solid; border-collapse: collapse;">
					<b>{{ $estudiante->nombre_completo }}</b>
				</td>
				
				
				@foreach($asignaturas as $asignatura)
				
				@php
					$codigo = App\Matriculas\ControlDisciplinario::where('estudiante_id',$estudiante->id_estudiante)->where('semana_id',$semana_actual->id)->where('curso_id',$curso->id)->where('asignatura_id',$asignatura->id)->get()->first();
				@endphp

				<td style="border: 1px solid; border-collapse: collapse;">
					{!! imprimir_codigos($codigo) !!}
				</td>
					
				@endforeach
			
				@php $linea++ @endphp
				
			</tr>
		@endforeach
	</tbody>
</table>

<?php  
	function imprimir_codigos($codigo)
	{
		if ( is_null($codigo) ) 
		{
			$codigo = (object)['codigo_1_id' => 0, 'codigo_2_id' => 0, 'codigo_3_id' => 0, 'observacion_adicional' => ''];
		}

		/* ASVERTENCIA: tipo_codigo no es enviado en el objeto $codigo
		if ( $codigo->tipo_codigo == 'positivo') {
			$clase = 'success';
		}else{
			$clase = 'success';
		}
		*/

		$mostrar = '';
		if ( $codigo->codigo_1_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($codigo->codigo_1_id);
			$mostrar .= '<a href="#" data-toggle="tooltip" data-placement="right" title="'.$el_codigo->descripcion.'"> <span class="badge">'.$el_codigo->id.'</span></a>';

		}

		if ( $codigo->codigo_2_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($codigo->codigo_2_id);
			$mostrar .= '<a href="#" data-toggle="tooltip" data-placement="right" title="'.$el_codigo->descripcion.'"> <span class="badge">'.$el_codigo->id.'</span></a>';

		}

		if ( $codigo->codigo_3_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($codigo->codigo_3_id);
			$mostrar .= '<a href="#" data-toggle="tooltip" data-placement="right" title="'.$el_codigo->descripcion.'"> <span class="badge">'.$el_codigo->id.'</span></a>';

		}

		if ( $codigo->observacion_adicional != '') {

			$mostrar .= '<code>'.$codigo->observacion_adicional.'</code>';

		}

		return $mostrar;
	}
?>