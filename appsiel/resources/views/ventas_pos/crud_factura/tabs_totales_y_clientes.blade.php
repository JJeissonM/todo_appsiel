
<ul class="nav nav-tabs">
    <li class="active" id="header_tab1"><a data-toggle="tab" href="#tab1"> Totales </a></li>
    <li id="header_tab2"><a data-toggle="tab" href="#tab2"> Cliente </a></li>
    <li id="header_tab3"><a data-toggle="tab" href="#tab3"> Fact. Elect. </a></li>
</ul>

<div class="tab-content">
    <div id="tab1" class="tab-pane fade in active">
        @include('ventas_pos.crud_factura.resumen_totales')
    </div>
    <div id="tab2" class="tab-pane fade">
        @include('ventas_pos.crud_factura.resumen_cliente')
    </div>
    <div id="tab3" class="tab-pane fade">
        @include('ventas_pos.crud_factura.resumen_factura_electronica')
    </div>
</div>