<table style="width: 100%; margin: 0px;">
	<tr>
		@if( !is_null($lbl) )
			<td width="35%" style="border: 0px; !important">
				<strong> {{ $lbl }} </strong>
			</td>
		@endif
		<td width="5px" style="border: 0px !important;">
			$
		</td>
		<td style="text-align: right; border: 0px !important; background-color: transparent !important;">
			{{ number_format($valor, 0, ',', '.') }}
		</td>
	</tr>
</table>