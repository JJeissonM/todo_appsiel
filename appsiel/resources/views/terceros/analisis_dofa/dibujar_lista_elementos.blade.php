<div class="lista">
	<ul>
		@foreach ($lista_items as $item)
				@if( $item->tipo_caracteristica == $tipo_caracteristica )
					<li>{{ $item->descripcion }}</li>
				@endif
		@endforeach
	</ul>
</div>
