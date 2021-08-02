<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
	  <li class="breadcrumb-item">
		<a class="ml-2 btn-gmail" href="{{ URL::previous() }}" title="Regresar"><i class="fa fa-arrow-left"></i></a>
	  </li>
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