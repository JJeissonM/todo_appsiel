@if((new \App\Compras\Services\ReteicaService())->habilitado())
<?php
    $retenciones_aplicadas = (new \App\Compras\Services\ContabilidadService())->get_retenciones($doc_encabezado);
    $retenciones_aplicadas->load('retencion');
?>
<div class="compras-retenciones-aplicadas" style="margin-top:15px;">
    <h4 style="font-size:13px;">Retenciones aplicadas</h4>
    <table class="table table-bordered" style="width:100%; border-collapse:collapse; font-size:11px;">
        <thead style="display:table-header-group;">
            <tr>
                <th style="border:1px solid #ddd; padding:4px;">Retención</th>
                <th style="border:1px solid #ddd; padding:4px; text-align:right;">Base</th>
                <th style="border:1px solid #ddd; padding:4px; text-align:right;">Tasa (%)</th>
                <th style="border:1px solid #ddd; padding:4px; text-align:right;">Valor retenido</th>
            </tr>
        </thead>
        <tbody>
            @forelse($retenciones_aplicadas as $registro_retencion)
                <tr style="page-break-inside:avoid;">
                    <td style="border:1px solid #ddd; padding:4px;">
                        {{ $registro_retencion->retencion ? $registro_retencion->retencion->descripcion : 'Retención #' . $registro_retencion->contab_retencion_id }}
                    </td>
                    <td style="border:1px solid #ddd; padding:4px; text-align:right;">$ {{ number_format($registro_retencion->valor_base_retencion, 2, ',', '.') }}</td>
                    <td style="border:1px solid #ddd; padding:4px; text-align:right;">{{ rtrim(rtrim(number_format($registro_retencion->tasa_retencion, 4, ',', '.'), '0'), ',') }}</td>
                    <td style="border:1px solid #ddd; padding:4px; text-align:right;">$ {{ number_format($registro_retencion->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="border:1px solid #ddd; padding:4px;">Este documento no tiene retenciones activas.</td></tr>
            @endforelse
        </tbody>
        @if(count($retenciones_aplicadas))
            <tfoot>
                <tr style="page-break-inside:avoid;">
                    <th colspan="3" style="border:1px solid #ddd; padding:4px; text-align:right;">Total retenciones</th>
                    <th style="border:1px solid #ddd; padding:4px; text-align:right;">$ {{ number_format($retenciones_aplicadas->sum('valor'), 2, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
@endif
