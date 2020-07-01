<table style="width: 100%; margin: 0px;">
	<tr>
		@if( !is_null($lbl) )
			<td width="35%" style="border: 0px;">
				<strong> {{ $lbl }} </strong>
			</td>
		@endif
		<td width="5px" style="border: 0px;">
			$
		</td>
		<td style="text-align: right; border: 0px; background-color: transparent !important;">
			{{ number_format($valor, 0, ',', '.') }}
		</td>
	</tr>
</table>