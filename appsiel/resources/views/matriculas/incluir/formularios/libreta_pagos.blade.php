<div class="panel panel-default">
    <div class="panel-heading">Datos para Crear Libreta De Pagos</div>
    <div class="panel-body">

        <div class="row">
            <div class="col-md-6">
                <div class="row" style="padding:5px;">
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="fecha_inicio"><i class="fa fa-asterisk"></i>Fecha
                            incio:</label>
                        <div class="col-sm-9">
                            <input class="form-control" id="fecha_inicio" required="required" name="fecha_inicio"
                                type="date">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row" style="padding:5px;">
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="numero_periodos"><i class="fa fa-asterisk"></i>Núm.
                            periodos:</label>
                        <div class="col-sm-9">
                            <input class="form-control" id="numero_periodos" max="36" required="required"
                                name="numero_periodos" type="number">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="row" style="padding:5px;">
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-md-3" for="valor_matricula"><i
                                class="fa fa-asterisk"></i>Valor matrícula:</label>
                        <div class="col-sm-9 col-md-9">
                            <input class="form-control" id="valor_matricula" placeholder="Valor matrícula"
                                autocomplete="off" required="required" name="valor_matricula" type="text">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row" style="padding:5px;">
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-md-3" for="valor_pension_mensual"><i
                                class="fa fa-asterisk"></i>Valor pensión mensual:</label>
                        <div class="col-sm-9 col-md-9">
                            <input class="form-control" id="valor_pension_mensual" placeholder="Valor pensión mensual"
                                autocomplete="off" required="required" name="valor_pension_mensual" type="text">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>