@if( isset($formula->id) )
	<h5 class="pull-right" style="display: none;"> Fórmula No. <span class="formula_id">{{ $formula->id }}</span> </h5>
	<table class="table table-bordered">
		<tr>
			<td colspan="4">
				<b>Tipo de Lentes: </b> {{ DB::table('salud_tipo_lentes')->where('id',$formula->tipo_de_lentes)->value('descripcion') }}
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<b>Material: </b> {{ DB::table('salud_material_lentes')->where('id',$formula->material)->value('descripcion') }}
			</td>
			<td>
				<b>Uso: </b> {{ $formula->uso }}
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<b>Diagnóstico: </b> {{ $formula->recomendaciones }}
			</td>
			<td>
				<b>Control: </b> {{ $formula->proximo_control }}
			</td>
		</tr>
	</table>
@endif