<!--<table class="banner" >
	<tr>
		<td width="150px">
			@if($logo == 'Sin Logo')
				<h3>{{ $logo }} </h3>
			@else
				<img src="{{ $logo }}" height="120px"/> 
			@endif
		</td>
		<td align="center">
			<b>{{ $empresa->descripcion }}</b><br/>
		</td>
	</tr>
</table>-->

<div class="banner">
	@if($logo == 'Sin Logo')
				<h3>{{ $logo }} </h3>
			@else
				<img src="{{ $logo }}" height="120px"/> 
			@endif
			<!--<div class="nombre-empresa">
				<b style="color: #50B794;">{{ $empresa->descripcion }}</b>
			</div>-->
</div>