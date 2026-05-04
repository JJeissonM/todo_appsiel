{{--
    Sección: Entradas de Almacén vinculadas a esta factura de compra.
    Variables:
      $doc_encabezado        → ComprasDocEncabezado
      $ea_asignadas          → Collection de InvDocEncabezado (las ya asignadas)
--}}
<div class="marco_formulario" style="margin-top:20px; border-top:3px solid #8e44ad;">
    <h4>
        <i class="fa fa-truck" style="color:#8e44ad;"></i>
        &nbsp; Entradas de Almacén Relacionadas
        <small class="text-muted" style="font-size:13px;">
            — Vincula las EA que llegaron antes de recibir esta factura del proveedor
        </small>
    </h4>

    {{-- Tabla de EAs ya asignadas --}}
    <div id="tabla_ea_asignadas">
        @if($ea_asignadas->isEmpty())
            <p class="text-muted" id="msg_sin_ea">
                <i class="fa fa-info-circle"></i> Aún no hay Entradas de Almacén vinculadas a esta factura.
            </p>
        @else
            <table class="table table-bordered table-condensed table-striped" style="font-size:13px;">
                <thead style="background:#8e44ad; color:#fff;">
                    <tr>
                        <th>Documento EA</th>
                        <th>Fecha</th>
                        <th>Detalle</th>
                        <th style="text-align:right;">Valor sin IVA</th>
                        <th style="text-align:right;">Valor con IVA</th>
                        @if($doc_encabezado->estado != 'Anulado')
                        <th style="width:60px;"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($ea_asignadas as $ea)
                    <tr id="ea_row_{{ $ea->id }}">
                        <td>
                            <strong>{{ $ea->documento_transaccion_prefijo_consecutivo }}</strong>
                        </td>
                        <td>{{ $ea->fecha }}</td>
                        <td>{{ str_limit($ea->descripcion, 60) }}</td>
                        <td style="text-align:right;">${{ number_format($ea->total_documento, 2, ',', '.') }}</td>
                        <td style="text-align:right;">${{ number_format($ea->total_documento_mas_iva, 2, ',', '.') }}</td>
                        @if($doc_encabezado->estado != 'Anulado')
                        <td style="text-align:center;">
                            <button type="button"
                                    class="btn btn-danger btn-xs btn_desasignar_ea"
                                    data-ea_id="{{ $ea->id }}"
                                    data-factura="{{ $doc_encabezado->id }}"
                                    title="Desvincular esta EA">
                                <i class="fa fa-unlink"></i>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($doc_encabezado->estado != 'Anulado')
    {{-- Panel para buscar y asignar EAs pendientes --}}
    <div style="margin-top:12px;">
        <button type="button" class="btn btn-default btn-sm" id="btn_buscar_ea_pendientes"
                data-proveedor="{{ $doc_encabezado->proveedor_id }}"
                data-factura="{{ $doc_encabezado->id }}">
            <i class="fa fa-search"></i> Buscar EA pendientes de este proveedor
        </button>
        <span id="ea_spinner" style="display:none; margin-left:8px;">
            <i class="fa fa-spinner fa-spin"></i>
        </span>
    </div>

    <div id="contenedor_ea_pendientes" style="margin-top:12px; display:none;">
        {{-- Se carga dinámicamente via AJAX --}}
    </div>
    @endif
</div>

