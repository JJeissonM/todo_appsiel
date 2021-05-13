<h5>
	Operaciones de pagos 
	<small>
		<button style="border: 0; background: transparent; display: none;" title="Mostrar" id="btn_mostrar_operaciones">
			<i class="fa fa-eye"></i>
		</button>
		<button style="border: 0; background: transparent;" title="Ocultar" id="btn_ocultar_operaciones">
			<i class="fa fa-eye-slash"></i>
		</button>
	</small>
</h5>
<div id="div_operaciones">
	<hr>
	<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="tab" href="#tab1"> Documentos pendientes </a></li>
		<li><a data-toggle="tab" href="#tab2"> Retenciones </a></li>
		<li><a data-toggle="tab" href="#tab3"> Dctos pronto pago </a></li>
		<!-- <li><a data-toggle="tab" href="#tab4"> Asientos contables </a></li> -->
    </ul>

    <div class="tab-content">
    	<div id="tab1" class="tab-pane fade in active">
	        @include('tesoreria.pagos_cxp.seccion_documentos_pendientes')
	    </div>
	    <div id="tab2" class="tab-pane fade">
	        @include('tesoreria.incluir.seccion_retenciones')
    	</div>
	    <div id="tab3" class="tab-pane fade">
	        -- EN DESARROLLO -- Para acceder por favor consultar al mail paula@appsiel.com.co
    	</div>
	    <!-- <div id="tab4" class="tab-pane fade">
	        @ include('tesoreria.recaudos_cxc.seccion_asientos_contables')
    	</div> -->
    </div>
</div>