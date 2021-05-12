<h5>
	Medios de pago
	<small>
		<button style="border: 0; background: transparent; display: none;" title="Mostrar" id="btn_mostrar_medios_pago">
			<i class="fa fa-eye"></i>
		</button>
		<button style="border: 0; background: transparent;" title="Ocultar" id="btn_ocultar_medios_pago">
			<i class="fa fa-eye-slash"></i>
		</button>
	</small>
</h5>
<div id="div_medios_pago">
	<hr>
	<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="tab" href="#tab_mp_1"> Efectivo </a></li>
		<li><a data-toggle="tab" href="#tab_mp_2"> Transferencia/Consignación </a></li>
		<li><a data-toggle="tab" href="#tab_mp_3"> Tarj. Débito </a></li>
		<li><a data-toggle="tab" href="#tab_mp_4"> Tarj. Crédito </a></li>
		<li><a data-toggle="tab" href="#tab_mp_5"> Cheque </a></li>
		<!-- <li><a data-toggle="tab" href="#tab_mp_6"> PSE </a></li> -->
    </ul>

    <div class="tab-content">
    	<div id="tab_mp_1" class="tab-pane fade in active">
	        @include('tesoreria.medios_de_pago.seccion_efectivo')
	    </div>
	    <div id="tab_mp_2" class="tab-pane fade">
	        @include('tesoreria.medios_de_pago.seccion_transferencia_consignacion')
    	</div>
	    <div id="tab_mp_3" class="tab-pane fade">
	        @include('tesoreria.medios_de_pago.seccion_tarjeta_debito')
    	</div>
	    <div id="tab_mp_4" class="tab-pane fade">
	        @include('tesoreria.medios_de_pago.seccion_tarjeta_credito')
    	</div>
	    <div id="tab_mp_5" class="tab-pane fade">
	        @include('tesoreria.medios_de_pago.seccion_cheque')
    	</div>
	    <!-- <div id="tab_mp_6" class="tab-pane fade">
	        @ include('tesoreria.medios_de_pago.seccion_pse')
    	</div> -->
    </div>
</div>