<div class="form-group">
    <label for="turno_operativo_busqueda">Turno operativo cerrado/auditado</label>
    <div style="position:relative;">
    <input type="text" id="turno_operativo_busqueda" autocomplete="off"
           class="form-control text_input_sugerencias turno-operativo-ajax"
           placeholder="Busque por código, fecha o punto de venta"
           data-url_busqueda="{{ url('turnos/operativos/sugerencias') }}?modulo=tesoreria&amp;reporte=pos_movimientos_ventas"
           data-busqueda-reporte="1" style="padding-right:35px;" data-preservar-turno-al-cambiar-pdv="1" data-ajax-fields="pdv_id" data-selected-label="">
    <input type="hidden" name="turno_operativo_id" id="turno_operativo_id" value="">
    <i id="turno_busqueda_spinner" class="fa fa-spinner fa-spin" aria-hidden="true" style="display:none;position:absolute;right:12px;top:12px;pointer-events:none;"></i>
    </div>
    <button type="button" class="btn btn-default btn-xs" id="limpiar_turno_resumen" style="margin-top:5px;">Consultar por fechas</button>
    <p class="help-block" id="periodo_turno_resumen">Busque y seleccione un turno para utilizar su periodo de apertura y cierre.</p>
</div>
