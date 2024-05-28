<div class="row">
	<!-- <h4 style="text-align: center;">Selecciona la categoría luego indica el producto dando clic en COMPRAR</h4> -->
	<div class="col-md-12">
		<div class="accordion" id="accordionExample">
			<?php $i = 0; ?>
			@foreach( $productosTemp as $key=>$value)
			<div class="accordion-item">
				<div class="accordion-header" id="heading{{$i}}">
					<h2 class="mb-0">
						<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}">
							{{$key}}
						</button>
					</h2>
				</div>
				<div id="collapse{{$i}}" class="accordion-collapse collapse" aria-labelledby="heading{{$i}}" data-parent="#accordionExample">
					<div class="accordion-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit,minmax(auto,150px)); gap:10px;">
							@if(count($value)>0)
								@foreach($value as $item)
									<?php 
										if (!(int)$item->mostrar_en_pagina_web) {
											continue;
										}
									?>
									<div id="btn_{{ $item->id }}">
										@include('ventas_pos.tags_lista_items_dibujar_item')
										<br>
									</div>
								@endforeach
							@else
								<h5>No hay productos en esta categoría</h5>
							@endif
						</div>
					</div>
				</div>
			</div>
			<?php $i = $i + 1; ?>
			@endforeach
		</div>
	</div>
</div>