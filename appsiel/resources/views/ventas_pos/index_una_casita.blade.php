<?php 
    
    $num_facturas = App\VentasPos\FacturaPos::where('pdv_id', $pdv->id)->where('estado', 'Pendiente')->orderBy('id')->get()->pluck('id')->toArray();
    
    $fecha_primera_factura = date('Y-m-d');
    $primera_factura = App\VentasPos\FacturaPos::where('pdv_id', $pdv->id)->where('estado', 'Pendiente')->first();
    if ( !is_null($primera_factura ) )
    {
        $fecha_primera_factura = $primera_factura->fecha;
    }
    $fecha_hoy = date('Y-m-d');

    $apertura = App\VentasPos\AperturaEncabezado::where('pdv_id', $pdv->id)->get()->last();
    $cierre = App\VentasPos\CierreEncabezado::where('pdv_id', $pdv->id)->get()->last();

    $fecha_desde = '--';

    $btn_abrir = '<a href="' . url('web/create') . '?id=20&id_modelo=228&id_transaccion=45&pdv_id='.$pdv->id.'&cajero_id='.Auth::user()->id.'" class="btn btn-xs btn-success" > Apertura </a>';

    $btn_facturar = '<a href="' . url('pos_factura/create') . '?id=20&id_modelo=230&id_transaccion=47&pdv_id='.$pdv->id . '&action=create" class="btn btn-xs btn-primary" > Facturar </a>';

    $btn_facturar_restaurante = '<a href="' . url('pos_factura_restaurante/create') . '?id=20&id_modelo=230&id_transaccion=47&pdv_id='.$pdv->id . '&action=create" class="btn btn-xs btn-primary" > Facturar </a>';

    $btn_hacer_pedido = '<a href="' . url('pos_pedido/create') . '?id=20&id_modelo=175&id_transaccion=42&pdv_id='.$pdv->id . '&action=create" class="btn btn-xs btn-primary" > Hacer pedido </a>';

    $btn_cerrar = '<a href="' . url('web/create') . '?id=20&id_modelo=229&id_transaccion=46&pdv_id='.$pdv->id.'&cajero_id='.Auth::user()->id.'" class="btn btn-xs btn-danger" > Cierre </a>';

    $btn_acumular = '<button class="btn btn-xs btn-warning btn_acumular" data-pdv_id="'.$pdv->id.'" data-pdv_descripcion="'.$pdv->descripcion.'"  data-ids_facturas="'.json_encode($num_facturas).'" > Acumular </button>';

    $btn_hacer_arqueo = '<a href="'.url( '/web/create' . '?id=20&id_modelo=158&vista=tesoreria.arqueo_caja.create&teso_caja_id='.$pdv->caja_default_id . '&pdv_id='.$pdv->id ) .'" class="btn btn-xs btn-info" id="btn_hacer_arqueo"> Hacer arqueo </a>';

    $btn_consultar_estado = '';

    $color = 'red';

    if ( $pdv->estado == 'Abierto' )
    {
        $color = 'green';

        $btn_abrir = '';
        $btn_acumular = '';
        $btn_hacer_arqueo = '';

        if ( !is_null( $apertura ) )
        {
            $fecha_desde = $apertura->fecha;
        }
    }

    if ( $pdv->estado == 'Cerrado' )
    {
        $btn_cerrar = '';
        $btn_facturar = '';
        $btn_facturar_restaurante = '';

        if (empty($num_facturas))
        {
            $btn_acumular = '';
        }

        if ( !is_null( $cierre ) )
        {
            $fecha_desde = $cierre->created_at;
        }
    }

    if ($btn_acumular != '') {
        $btn_hacer_arqueo = '';
    }
?>

<div class="col-md-{{12/$cant_cols}} col-xs-12 col-sm-12" style="padding: 5px;">
    <div class="tienda">
        <p style="text-align: center; margin: 10px;">
            <img src="{{asset('assets/images/canopy_shop_pos.jpg') }}" style="display: inline; height: 120px; width: 100%;" />
        </p>
        <div class="caja">
            <div class="datos_pdv">

                <br>

                <div class="table-responsive">
                    <div class="table">
                        
                            <div style="text-align: center; font-size: 1.1em; font-weight: bold;" colspan="2">
                                {{ $pdv->descripcion }}
                                <hr>
                            </div>
                        
                        
                            <div>
                                <b> Estado: </b> <i class="fa fa-circle" style="color: {{$color}}"> </i> {{ $pdv->estado }} <small> | desde {{ $fecha_desde }} </small>
                            </div>
                        
                            @can('vtas_pos_ver_valor_documento_en_consultar_facturas_pdv')
                                <div>
                                    <b> # facturas: </b>
                                    <span class="badge">{{ count($num_facturas) }}</span>
                                    @if( !empty($num_facturas) )
                                        <button style="background: transparent; border: 0px; text-decoration: underline; color: #069;" class="btn_consultar_facturas" href="#" data-pdv_id="{{$pdv->id}}" data-lbl_ventana="Facturas de ventas" data-fecha_primera_factura="{{$fecha_primera_factura}}" data-fecha_hoy="{{$fecha_hoy}}" data-view="index"> Consultar </button>
                                    @endif
                                </div>
                            @endcan
                        
                    </div>

                    <div class="btn-group">

                        <!-- Si tiene el permiso, le quita el botón --> 
                        @can('vtas_pos_no_abrir_pdv')
                            &nbsp;
                        @else 
                            {!! $btn_abrir !!}
                        @endcan
                        
                        @can('vtas_pos_no_facturar_pdv')

                            @can('vtas_pos_btn_facturar_restaurante')
                                hi baby
                                {!! $btn_facturar_restaurante !!}
                            @endcan

                        @else 
                            {!! $btn_facturar !!}
                        @endcan
                        
                        @can('vtas_pos_no_cerrar_pdv')
                            &nbsp;
                        @else 
                            {!! $btn_cerrar !!}
                        @endcan

                        <!-- Si tiene el permiso, le agrega el botón --> 
                        @can('vtas_pos_acumular_pdv')
                            {!! $btn_acumular !!}
                        @else 
                            &nbsp;
                        @endcan

                        @can('vtas_pos_hacer_arqueo_pdv')
                            {!! $btn_hacer_arqueo !!}
                        @else 
                            &nbsp;
                        @endcan
                        
                    </div>
                    <br><br>

                    @can('vtas_pos_hacer_pedido')
                        <div class="btn-group">
                            {!! $btn_hacer_pedido !!}
                        </div>
                        <br><br>
                    @endcan
                    
                    {!! $btn_consultar_estado !!}
                </div>
            </div>										
        </div>
    </div>
</div>