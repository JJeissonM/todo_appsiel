<div class="modal fade" id="modal_retiro_personalizado" tabindex="-1" role="dialog" aria-labelledby="modal_retiro_personalizado_label" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal_retiro_personalizado_label">
                    <i class="fa fa-filter"></i> Retiro personalizado
                </h4>
            </div>
            <div class="modal-body">
                <div id="retiro-personalizado-alerta" class="alert alert-danger" style="display:none;"></div>

                <div class="form-group">
                    <label for="retiro_grupo_empleado_id">Grupo de empleados</label>
                    <select id="retiro_grupo_empleado_id" class="form-control retiro-personalizado-select">
                        <option value="">Todos los grupos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="retiro_nom_contrato_id">Empleado</label>
                    <select id="retiro_nom_contrato_id" class="form-control retiro-personalizado-select">
                        <option value="">Todos los empleados</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="retiro_nom_concepto_id">Concepto</label>
                    <select id="retiro_nom_concepto_id" class="form-control retiro-personalizado-select">
                        <option value="">Todos los conceptos</option>
                    </select>
                </div>

                <div class="alert alert-warning" style="margin-bottom:0;">
                    <i class="fa fa-warning"></i>
                    Se revertirán también los valores asociados en cuotas, préstamos, novedades TNL, turnos o prestaciones, según corresponda.
                    <div style="margin-top:6px;">Seleccione al menos uno de los tres filtros.</div>
                    <div style="margin-top:6px;"><strong>Registros encontrados:</strong> <span id="retiro_personalizado_cantidad">0</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn_ejecutar_retiro_personalizado" disabled>
                    <span class="btn-text">Retirar</span>
                    <i class="fa fa-spinner fa-spin btn-spinner" style="display:none; margin-left:6px;"></i>
                </button>
            </div>
        </div>
    </div>
</div>
