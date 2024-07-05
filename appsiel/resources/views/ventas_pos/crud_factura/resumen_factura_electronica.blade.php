<div id="div_resumen_cliente" style="font-size:13px;">
    
    <hr>    
    <div class="well" style="text-align: center;">
        @if ( (int)config('ventas_pos.modulo_fe_activo'))
            @if ( $resolucion_facturacion_electronica == null )
                <code>No hay una resolución de Fact. Electrónica Asociada.</code>
            @else
                @if ( Input::get('action') != 'edit' )
                    <button class="btn btn-lg btn-primary" id="btn_guardar_factura_electronica" disabled="disabled">
                        <i class="fa fa-save"></i> Guardar como F.E.
                    </button>
                @else
                    <code>No puede guardar como Factura Electrónica un Factura POS. Debe CONVERTIR la Factura POS en Electrónica.</code>
                @endif                
            @endif
        @else
            <code>El Módulo de Fact. Electrónica no está Activo.</code>
        @endif
    </div>
    <br><br>
</div>