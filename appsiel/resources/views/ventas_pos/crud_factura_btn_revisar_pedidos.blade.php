
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
            <i class="fa fa-money"></i> Revisar anticipos/Saldos a favor
        </button>
        
        &nbsp; &nbsp; &nbsp;

        <button class="btn btn-default btn-xs" style="background-color: black !important; color: white !important;" id="btn_update_uniqid">
            <i class="fa fa-circle"></i> REFRESH
        </button>
        
        <!-- -->
        &nbsp; &nbsp; &nbsp;

        <button class="btn btn-default btn-xs" style="background-color: rgb(64, 216, 236) !important; color: rgb(20, 4, 4) !important;" id="btn_testing">
            <i class="fa fa-cog"></i> Testing
        </button>

	</div>
</div>