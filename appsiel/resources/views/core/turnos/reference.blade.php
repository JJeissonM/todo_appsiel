<?php
    $turnoReferencia = app(\App\Core\Services\TurnoPresentationService::class)
        ->describe(isset($documento) ? $documento : null);
    $turnoCompacto = isset($compacto) && $compacto;
?>
@if(!is_null($turnoReferencia))
    @if($turnoCompacto)
        <span class="turno-operativo-reference" title="Fecha operativa: {{ $turnoReferencia['fecha_operativa'] }} | {{ $turnoReferencia['contexto'] }} | {{ $turnoReferencia['estado'] }}">
            {{ $turnoReferencia['codigo'] }}
        </span>
    @else
        <div class="turno-operativo-reference" style="margin-top: 4px; line-height: 1.35;">
            <b>Turno operativo:</b> {{ $turnoReferencia['codigo'] }}<br>
            <!-- 
            <span><b>Fecha operativa:</b> {{ $turnoReferencia['fecha_operativa'] }}</span>
            &nbsp;|&nbsp; <span><b>Contexto:</b> {{ $turnoReferencia['contexto'] }}</span>
            &nbsp;|&nbsp; <span><b>Estado:</b> {{ $turnoReferencia['estado'] }}</span>
            -->
        </div>
    @endif
@endif
