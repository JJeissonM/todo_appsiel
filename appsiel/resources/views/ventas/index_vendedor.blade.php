
<div class="container-fluid">
	<div class="marco_formulario">
		@can('vtas_pedidos_restaurante')
			<div class="row">
				<div class="col-sm-4">
					<a href="{{url( 'vtas_pedidos_restaurante/create?id=13&id_modelo=320&id_transaccion=60' )}}" class="btn btn-success">
						<h1> <i class="fa fa-file"> </i> </h1>
						Crear pedido
					</a>
				</div>
			</div>
		@else
			<div class="row">
				
				<div class="col-sm-4">
					<div class="boton">
						<a href="{{url( 'web/create?id=13&id_modelo=216' )}}">
							<h1> <i class="fa fa-smile-o"> </i> </h1>
							Crear cliente
						</a>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="boton">
						<a href="{{url( 'vtas_cotizacion/create?id=13&id_modelo=155&id_transaccion=30' )}}">
							<h1> <i class="fa fa-file"> </i> </h1>
							Crear cotización
						</a>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="boton">
						<a href="{{url( 'vtas_pedidos/create?id=13&id_modelo=175&id_transaccion=42' )}}">
							<h1> <i class="fa fa-file"> </i> </h1>
							Crear pedido
						</a>
					</div>
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