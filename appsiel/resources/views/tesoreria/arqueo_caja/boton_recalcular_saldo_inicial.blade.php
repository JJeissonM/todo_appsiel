@if(\App\Tesoreria\ArqueoCaja::usuario_puede_recalcular_saldo_inicial($usuarioArqueo))
    <button type="button" id="btn_recalcular_saldo_inicial" class="btn btn-primary"
            title="Recalcular desde los movimientos de Tesorería">
        <i class="fa fa-refresh"></i> Recalcular
    </button>
    <small id="mensaje_recalcular_saldo_inicial" class="text-muted"></small>
@endif
