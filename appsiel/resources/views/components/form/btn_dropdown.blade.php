<div class="dropdown" style="display:inline-block;">
    <button class="btn btn-{{$clase}} btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
    	<i class="fa fa-{{$icono}}"></i> 
    	{{$etiqueta}}
    	<span class="caret"></span>
	</button>
    <ul class="dropdown-menu">
	    <?php $cant = count( $urls ); ?>

	    @for($i=0; $i < $cant; $i++)
			<li>
				<a href="{{ url( $urls[$i]['link'] ) }}"> 
					{{ $urls[$i]['etiqueta'] }} 
				</a>
			</li>
		@endfor
	</ul>
 </div>