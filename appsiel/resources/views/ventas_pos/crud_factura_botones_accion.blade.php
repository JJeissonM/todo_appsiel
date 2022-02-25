<div class="row">
    <div class="col-md-4 col-xs-12">
        <div class="btn-group">
            @can('vtas_pos_consultar_estado_pdv')
                <button class="btn btn-primary btn-xs btn_consultar_estado_pdv" data-pdv_id="{{Input::get('pdv_id')}}" data-lbl_ventana="Estado de PDV">
                    <i class="fa fa-btn fa-search"></i> Estado PDV </button>
            @endcan

            @can('vtas_pos_consultar_facturas_pdv')
                <button class="btn btn-default btn-xs btn_consultar_documentos" data-pdv_id="{{Input::get('pdv_id')}}" data-lbl_ventana="Facturas de ventas">
                    <i class="fa fa-btn fa-search"></i> Consultar facturas
                </button>
            @endcan
        </div>
    </div>

    <div class="col-md-4 col-xs-12 text-center">
        <div class="btn-group">
            <button class="btn btn-success btn-xs btn_registrar_ingresos_gastos" data-id_modelo="46"
                    data-id_transaccion="8" data-lbl_ventana="Ingresos"><i class="fa fa-btn fa-arrow-up"></i> <i class="fa fa-btn fa-money"></i> Registrar Ingresos </button>
            <button class="btn btn-danger btn-xs btn_registrar_ingresos_gastos" data-id_modelo="54"
                    data-id_transaccion="17" data-lbl_ventana="Gastos"> <i class="fa fa-btn fa-arrow-down"></i> <i class="fa fa-btn fa-money"></i> Registrar Salidas
            </button>
        </div>
    </div>
    
    <div class="col-md-4 col-xs-12">
        <div class="btn-group pull-right">
            @if( Input::get('action') == 'create' )
                <button class="btn btn-info btn-xs btn_revisar_pedidos_ventas" data-id_modelo="54"
                    data-id_transaccion="17" data-lbl_ventana="PEDIDOS DE VENTAS"><i class="fa fa-eye"></i> Revisar pedidos </button>
            @else
                &nbsp;
            @endif
        </div>
    </div>
</div>