<div class="container-fluid">
    <br/><br/>

    @if($encabezado_doc->estado == 'Activo')
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_agregar_empleados_documento">
                    <i class="fa fa-user-plus"></i> Agregar empleados
                </button>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal_retirar_empleados_documento">
                    <i class="fa fa-users"></i> Retirar todos los empleados
                </button>
            </div>
        </div>
    @endif

        <div class="row">
            <div class="col-md-12">
                <div id="tabla-empleados-documento">
                    {!! $tabla !!}
                </div>
            </div>
        </div>

    <div id="empleados-alerta"></div>

    {{ Form::open(array('url'=>'nom_guardar_asignacion','id'=>'form-asignar-empleado')) }}
        <div class="row">
            <div class="col-md-8 col-md-offset-2" style="vertical-align: center; border: 1px solid gray;">
                <h3>Asignar nuevo</h3>
                <div class="row">
                    <div class="col-md-6">
                        {{ Form::bsSelect('registro_modelo_hijo_id',null,$titulo_tab,$opciones,['class'=>'combobox']) }}
                    </div>
                    <div class="col-md-6">
                        {{ Form::bsText('nombre_columna1',null,'Orden',[]) }}
                    </div>
                    {{ Form::hidden('registro_modelo_padre_id',$registro_modelo_padre_id) }}

                    {{ Form::hidden('url_id',Input::get('id'))}}
                    {{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
                    {{ Form::hidden('url_id_transaccion',Input::get('id_transaccion'))}}
                </div>
                <div align="center">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn_guardar_empleado">
                        <span class="btn-text">Guardar</span>
                        <i class="fa fa-spinner fa-spin btn-spinner" style="display: none; margin-left: 6px;"></i>
                    </button>
                </div>
                <br/><br/>
            </div>
        </div>
    {{ Form::close() }}

    <div class="modal fade" id="modal_agregar_empleados_documento" tabindex="-1" role="dialog" aria-labelledby="modal_agregar_empleados_documento_label" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal_agregar_empleados_documento_label">Confirmar adición de empleados</h4>
                </div>
                <div class="modal-body">
                    <p>Se agregarán los contratos activos cuya vigencia coincida con el lapso del documento:</p>
                    <p><strong>{{ $lapso_documento->fecha_inicial }}</strong> a <strong>{{ $lapso_documento->fecha_final }}</strong>.</p>
                    <p>Los contratos ya asignados no se duplicarán.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn_confirmar_agregar_empleados"
                        data-url="{{ url('nomina/documentos/' . $encabezado_doc_id . '/empleados/agregar') }}">
                        <span class="btn-text">Agregar empleados</span>
                        <i class="fa fa-spinner fa-spin btn-spinner" style="display: none; margin-left: 6px;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_retirar_empleados_documento" tabindex="-1" role="dialog" aria-labelledby="modal_retirar_empleados_documento_label" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal_retirar_empleados_documento_label">Confirmar retiro de empleados</h4>
                </div>
                <div class="modal-body">
                    <p>¿Realmente quiere retirar todos los empleados que sea posible?</p>
                    <div class="alert alert-warning" style="margin-bottom: 0;">
                        Los empleados que tengan conceptos liquidados permanecerán asignados al documento.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn_confirmar_retirar_empleados"
                        data-url="{{ url('nomina/documentos/' . $encabezado_doc_id . '/empleados/retirar') }}">
                        <span class="btn-text">Retirar empleados</span>
                        <i class="fa fa-spinner fa-spin btn-spinner" style="display: none; margin-left: 6px;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
