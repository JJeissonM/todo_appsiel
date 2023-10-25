
<div class="col-md-12 well">
    <div class="container-fluid">
        @if( Input::get('action') == 'create' )
            <button class="btn btn-info btn-xs btn_revisar_pedidos_ventas" data-id_modelo="54"
                data-id_transaccion="17" data-lbl_ventana="PEDIDOS DE VENTAS"><i class="fa fa-eye"></i> Revisar pedidos </button>
        @else
            &nbsp;
        @endif
	</div>
</div>