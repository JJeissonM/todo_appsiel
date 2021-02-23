<div class="row">
	<h4 style="text-align: center;">Selecciona la categoría luego indica el producto dando clic en COMPRAR</h4>
	<div class="col-md-12">
		<div class="accordion" id="accordionExample">
			<?php $i = 0; ?>
			@foreach( $productosTemp as $key=>$value)
			<div class="card">
				<div class="card-header" id="heading{{$i}}">
					<h2 class="mb-0">
						<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}">
							{{$key}}
						</button>
					</h2>
				</div>
				<div id="collapse{{$i}}" class="collapse" aria-labelledby="heading{{$i}}" data-parent="#accordionExample">
					<div class="card-body">
						@if(count($value)>0)
						@foreach($value as $item)
						<div class="col-md-3" style="padding: 10px;">
							<div class="col-md-12" style="border: 1px solid;">
								@if($item->imagen!='')
								<img style="width: 100%;" src="{{url('')}}/appsiel/storage/inventarios/{{$item->imagen}}">
								@else
								<img style="width: 100%;" src="{{url('')}}/assets/img/box.png">
								@endif
								<p style="text-align: center;">{{ $item->descripcion }}</p>
								<button onclick="mandar_codigo2({{ $item->id }})" class="btn btn-block btn-primary btn-xs"><i class="fa fa-check"></i> COMPRAR</button>
								<br>
							</div>
						</div>
						@endforeach
						@else
						<h5>No hay productos en esta categoría</h5>
						@endif
					</div>
				</div>
			</div>
			<?php $i = $i + 1; ?>
			@endforeach
		</div>
	</div>
</div>