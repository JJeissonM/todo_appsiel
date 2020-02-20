<table class="table">
	<tr>
		<td>
			@if($formula->tipo_de_lentes != '' ) <b>Tipo de Lentes: </b> {{ $formula->tipo_de_lentes }} @endif
		</td>
		<td>
			@if($formula->proximo_control != '' ) <b>Próximo control: </b> {{ $formula->proximo_control }} @endif
		</td>
	</tr>
	<tr>
		<td>
			@if($formula->material != '' ) <b>Material: </b> {{ $formula->material }} @endif
		</td>
		<td>
			@if($formula->recomendaciones != '' ) <b>Recomendaciones: </b> {{ $formula->recomendaciones }} @endif
		</td>
	</tr>
</table>