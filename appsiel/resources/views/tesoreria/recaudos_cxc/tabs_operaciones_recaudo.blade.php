<h5>
	Operaciones de recaudo 
	<small>
		<button style="border: 0; background: transparent;" title="Mostrar" id="btn_mostrar_operaciones">
			<i class="fa fa-eye"></i>
		</button>
		<button style="border: 0; background: transparent; display: none;" title="Ocultar" id="btn_ocultar_operaciones">
			<i class="fa fa-eye-slash"></i>
		</button>
	</small>
</h5>
<div id="div_operaciones" style="display: none;">
	<hr>
	<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="tab" href="#tab1"> Recaudo cartera (CxC) </a></li>
		<li><a data-toggle="tab" href="#tab2"> Retenciones </a></li>
		<!-- <li><a data-toggle="tab" href="#tab3"> Asientos contables </a></li> -->
    </ul>

    <div class="tab-content">
    	<div id="tab1" class="tab-pane fade in active">
	        @include('tesoreria.recaudos_cxc.seccion_documentos_pendientes')
	    </div>
	    <div id="tab2" class="tab-pane fade">
	        @include('tesoreria.recaudos_cxc.seccion_retenciones')
    	</div>
	    <!-- <div id="tab3" class="tab-pane fade">
	        @ include('tesoreria.recaudos_cxc.seccion_asientos_contables')
    	</div> -->
    </div>
</div>