@if( isset($formula->id) )
	<h5 class="pull-right" style="display: none;"> Fórmula No. <span class="formula_id">{{ $formula->id }}</span> </h5>
	<table class="table table-bordered">
		<!-- <tr>
			<td colspan="4">
				<b>Diagnóstico: </b> { { $formula->diagnostico }}
			</td>
		</tr>
	-->
		<tr>
			<td colspan="3">
				<b>Tipo de Lentes: </b> {{ DB::table('salud_tipo_lentes')->where('id',$formula->tipo_de_lentes)->value('descripcion') }}, 
				<br>
				<b>Material: </b> {{ DB::table('salud_material_lentes')->where('id',$formula->material)->value('descripcion') }}, <b>Filtro: </b> {{ $formula->filtro }}
			</td>
			<td>
				<b>Diagnostico: </b> {{ $formula->diagnostico }}
				<br>
				<b>Recomendaciones: </b> {{ $formula->recomendaciones }}
			</td>
		</tr>
	</table>
@endif