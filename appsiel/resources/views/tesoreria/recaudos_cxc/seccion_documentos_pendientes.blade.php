<!-- Documentos pendientes de cartera -->
<div class="row">
	<div class="col-md-12">
		<div id="div_aplicacion_cartera" style="display: none;">
        	<div id="div_documentos_pendientes">

        	</div>
        </div>
	</div>
</div>

<!-- Documentos seleccionados -->
<div class="row">
	<div class="col-md-12">
		<div id="div_documentos_a_cancelar" style="display: none;">
			<h3 style="width: 100%; text-align: center;"> Documentos seleccionados </h3>
			<hr>

			<table class="table table-striped" id="tabla_registros_documento">
			    <thead>
			        <tr>
			            <th style="display: none;" data-override="id_doc"> ID Doc. Pendiente </th>
			            <th> Cliente </th>
			            <th> Documento interno </th>
			            <th> Fecha </th>
			            <th> Fecha vencimiento </th>
			            <th> Valor Documento </th>
			            <th> Valor pagado </th>
			            <th> Saldo pendiente </th>
			            <th data-override="abono"> Abono </th>
			        </tr>
			    </thead>
			    <tbody>
			    </tbody>
			    <tfoot>
			        <tr>
			            <td style="display: none;"> &nbsp; </td>
			            <td colspan="7"> &nbsp; </td>
			            <td> <div id="total_valor">$0</div> </td>
			        </tr>						    	
			    </tfoot>
			</table>
        </div>
	</div>
</div>