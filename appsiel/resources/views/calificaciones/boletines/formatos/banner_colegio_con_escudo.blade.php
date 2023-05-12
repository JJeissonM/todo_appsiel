<?php 
    if ( !isset($colegio) )
    {
        if (Auth::user() != null) {
            $empresa_id = Auth::user()->empresa_id;
        }else{
            $empresa_id = \App\Core\Empresa::get()->first()->id;
        }
        
        $colegio = \App\Core\Colegio::where('empresa_id', $empresa_id)->get()[0];
    }

    if ( !isset($tam_letra) )
    {
        $tam_letra = 3;
    }

    $opacity_default = 0.1;
    if (isset($opacity)) {
        $opacity_default = $opacity;
    }
?>
<table style="width: 100%; color: {{ config('calificaciones.color_fuente_boletin') }};font-size: {{$tam_letra-1}}mm; opacity: {{$opacity_default}};">
    <tr>
        <td width="20%">
            <div class="imagen" style="text-align: center;">
                <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen }}" style="max-width: 190px; max-height: 80px;" />
            </div>
        </td>

        <td align="center">
            <br/>
            <b style="font-size: {{$tam_letra+1}}mm;">{{ $colegio->descripcion }}</b>
            <br/>
            Resoluciones: {{ $colegio->resolucion }}
            @if(config('matriculas.codigo_dane') != '')
                <br/>
                Código DANE: {{ config('matriculas.codigo_dane') }}
            @endif
            <br/>
            NIT: {{ $colegio->empresa->numero_identificacion . '-' .$colegio->empresa->digito_verificacion }}
        </td>

        <td width="20%">
            <div class="imagen" style="text-align: center;">
                <img src="{{ config('matriculas.url_escudo_colegio') }}" style="max-height: 80px;" />
            </div>
        </td>
    </tr>
</table>