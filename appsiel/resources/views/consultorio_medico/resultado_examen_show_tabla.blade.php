<table class="table table-bordered">
	<thead>
		<tr>
			<th>
				&nbsp;
			</th>
			@foreach($variables as $variable)
				<th>
					{{ $variable->descripcion }}
				</th>
			@endforeach								
		</tr>
	</thead>
	<tbody>	
		@foreach($organos as $organo)
			<tr>
				<td>
					{{ $organo->descripcion }}
				</td>
				@foreach($variables as $variable)
					<td class="campo_variable" id="{{ $variable->id }}-{{ $organo->id }}">
						{{ DB::table('salud_resultados_examenes')->where( [ 'paciente_id' => $paciente_id, 'consulta_id' => $consulta_id, 'examen_id' => $examen_id, 'variable_id' => $variable->id, 'organo_del_cuerpo_id' => $organo->id])->value('valor_resultado') }}
					</td>
				@endforeach
			</tr>
		@endforeach
	</tbody>
</table>