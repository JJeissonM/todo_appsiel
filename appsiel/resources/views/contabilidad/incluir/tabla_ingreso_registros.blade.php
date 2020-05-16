<h4>Ingreso de registros</h4>
<button class="btn btn-success btn-xs" id="btn_crear_cxc" href="#"> Crear registro CxC </button>
<button class="btn btn-warning btn-xs" id="btn_crear_cxp" href="#"> Crear registro CxP </button>		    
<!--<a class="btn btn-default btn-xs" href="#"> Crear CxP </a>
<a class="btn btn-default btn-xs" href="#"> Aplicar CxP </a>
-->
<code>Nota: Los documentos que usen transacciones de CxP o CxC no podrán ser modificados.</code>
<table class="table table-striped" id="ingreso_registros">
    <thead>
        <tr>
            <th style="display: none;">fecha_vencimiento</th>
            <th style="display: none;">documento_soporte_tercero</th>
            <th data-override="tipo_transaccion" width="50px">Tipo transacción</th>
            <th width="250px">Cuenta</th>
            <th width="250px">Tercero</th>
            <th>Detalle</th>
            <th data-override="debito">Débito</th>
            <th data-override="credito">Crédito</th>
            <th width="10px">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        {!! $lineas_documento !!}
    </tbody>
    <tfoot>
        <tr>
            <td style="display: none;"></td>
            <td style="display: none;"></td>
            <td colspan="7">
            	<button id="btn_nuevo" style="background-color: transparent; color: #3394FF; border: none;"><i class="fa fa-btn fa-plus"></i> Agregar registro</button>
            </td>
        </tr>
        <tr>
            <td style="display: none;"></td>
            <td style="display: none;"></td>
            <td colspan="4">&nbsp;</td>
            <td> <div id="total_debito" > $0 </div> </td>
            <td> <div id="total_credito"> $0 </div> </td>
            <td> <div id="sumas_iguales"> - </div> </td>
        </tr>
    </tfoot>
</table>