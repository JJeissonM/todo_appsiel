<?php 
    if ( !isset($colegio) )
    {
        $colegio = \App\Core\Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];
    }

    if ( !isset($tam_letra) )
    {
        $tam_letra = 3;
    }
?>
<table class="banner">
    <tr>
        <td width="25%">
            <div class="imagen">
                <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen }}" style="max-width: 190px; max-height: 80px;" />
            </div>
        </td>

        <td align="center">
            <br/>
            <b style="font-size: {{$tam_letra+1}}mm;">{{ $colegio->descripcion }}</b>
            <br/>
            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->ciudad }}</b>
            <br/>
            Resolución No. {{ $colegio->resolucion }}<br/>
            {{ $colegio->direccion }},Teléfono: {{ $colegio->telefonos }}
        </td>
    </tr>
</table>