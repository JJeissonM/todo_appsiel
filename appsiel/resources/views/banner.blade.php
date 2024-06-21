<div class="banner">
	@if($logo == 'Sin Logo')
		<h3>{{ $logo }} </h3>
	@else
		<img src="{{ $logo }}" height="120px"/> 
	@endif
</div>