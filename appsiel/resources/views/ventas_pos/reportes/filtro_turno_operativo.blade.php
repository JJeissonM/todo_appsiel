<?php
    $turnos_resumen = (new \App\VentasPos\Services\SalesReportShiftService())
        ->available(Auth::user()->empresa_id)->with('pdv')->get();
?>
<div class="form-group">
    <label for="turno_operativo_id">Turno operativo cerrado/auditado</label>
    <select name="turno_operativo_id" id="turno_operativo_id" class="form-control combobox">
        <option value="">Consultar por fechas</option>
        @foreach($turnos_resumen as $turno_resumen)
            <option value="{{ $turno_resumen->id }}"
                    data-desde="{{ $turno_resumen->abierto_en->format('Y-m-d') }}"
                    data-hasta="{{ $turno_resumen->cerrado_en->format('Y-m-d') }}"
                    data-intervalo="{{ $turno_resumen->abierto_en->format('Y-m-d H:i:s') }} — {{ $turno_resumen->cerrado_en->format('Y-m-d H:i:s') }}">
                {{ $turno_resumen->codigo }} — {{ $turno_resumen->pdv ? $turno_resumen->pdv->descripcion : 'PDV ' . $turno_resumen->contexto_id }} — {{ $turno_resumen->estado }} — {{ $turno_resumen->abierto_en->format('Y-m-d H:i') }} / {{ $turno_resumen->cerrado_en->format('Y-m-d H:i') }}
            </option>
        @endforeach
    </select>
    <p class="help-block" id="periodo_turno_resumen">Al seleccionar un turno se utiliza su periodo de apertura y cierre.</p>
</div>
