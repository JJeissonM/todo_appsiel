
<div class="col-md-12 well">
    <div class="container-fluid">
        @if( Input::get('action') == 'create' )
            <button class="btn btn-info btn-xs btn_revisar_pedidos_ventas" data-id_modelo="54"
                data-id_transaccion="17" data-lbl_ventana="PEDIDOS DE VENTAS"><i class="fa fa-eye"></i> Revisar pedidos
            </button>
        @endif
        
        &nbsp; &nbsp; &nbsp; 

        @include('ventas_pos.componentes.boton_prefactura')
        
        &nbsp; &nbsp; &nbsp;

        <button class="btn btn-success btn-xs" id="btn_revisar_anticipos">
            <i class="fa fa-money"></i> Revisar anticipos
        </button>
        
        &nbsp; &nbsp; &nbsp;

        <button class="btn btn-default btn-xs" id="btn_update_uniqid">
            <i class="fa fa-circle"></i> REFRESH
        </button>

	</div>
</div>