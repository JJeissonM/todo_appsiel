
<div class="container-fluid">
	<div class="marco_formulario">
		@can('vtas_pedidos_restaurante')
			
			<div class="row">
				<h3>Toma de pedidos</h3>
				<hr>
				<?php 
					$cocinas = config('pedidos_restaurante.cocinas');
				?>

				@foreach($cocinas as $cocina)
					<div class="col-md-3 col-xs-6" style="padding: 10px;">
						<a href="{{url( 'vtas_pedidos_restaurante/create?id=13&id_modelo=320&id_transaccion=60' ) . '&grupo_inventarios_id=' . $cocina['grupo_inventarios_id'] }}" class="btn btn-block btn-default">
							<br>
							<img style="width: 100px; height: 100px; border-radius:4px;" src="{{$cocina['url_imagen']}}">
							<p style="text-align: center; white-space: nowrap; overflow: hidden; white-space: initial;">{{ $cocina['label'] }}</p>
						</a>
					</div>
				@endforeach
			</div>

		@else
			<?php 
				$user = \Auth::user();
				$vendedor = App\Ventas\Vendedor::where('user_id',$user->id)->get()->first();

				if ($vendedor == null) {
					dd('Usuario no está creado como Vendedor. Consute con el administraor del sistema.');
				}
				
				$cliente = $vendedor->cliente;

				if ($cliente == null) {
					dd('El Vendedor no tiene un Cliente relacionado. Consute con el administraor del sistema.');
				}

			?>
			<div class="row">
				
				<div class="col-sm-4">
					<a href="{{url( 'web/create?id=13&id_modelo=216' )}}">
						<div class="boton">
								<h1> <i class="fa fa-smile-o"> </i> </h1>
								Crear cliente
						</div>
					</a>
				</div>

				<div class="col-sm-4">
					<a href="{{url( 'vtas_cotizacion/create?id=13&id_modelo=155&id_transaccion=30' )}}">
						<div class="boton">
								<h1> <i class="fa fa-file"> </i> </h1>
								Crear cotización
						</div>
					</a>
				</div>

				<div class="col-sm-4">
					<a href="{{url( 'vtas_pedidos/create?id=13&id_modelo=175&id_transaccion=42' )}}">
						<div class="boton">
								<h1> <i class="fa fa-file"> </i> </h1>
								Crear pedido
						</div>
					</a>
				</div>

			</div>

			<div class="row">
				<div class="col-md-6">
					<h5>Estado de clientes</h5>
					<hr>
					<div style="text-align:center;width: 100%;">
						<img height="350px" src="{{asset('assets/images/grafica_clientes_crm.png')}}" />
					</div>
				</div>
				<div class="col-md-6">
					&nbsp;
				</div>
			</div>
		@endcan
	</div>
</div>