<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  	@foreach($vec as $fila)
  		@if($fila['url']!='NO')
  			<li class="breadcrumb-item">
  				<a href="{{url($fila['url'])}}">{{$fila['etiqueta']}}</a>
  			</li>
  		@else
  			<li class="breadcrumb-item active" aria-current="page">{{$fila['etiqueta']}}</li>
  		@endif
  	@endforeach
  </ol>
</nav>