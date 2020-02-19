<?php
    $colegio = App\Core\Colegio::where('empresa_id','=', Auth::user()->empresa_id )
                    ->get()->first();

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;
?>
<table class="table banner" >
    <tr>
        <td width="250px">
            <img src="{{ $url }}" width="{{ config('configuracion.ancho_logo_formatos') }}" height="{{ config('configuracion.alto_logo_formatos') }}" />
        </td>

        <td align="center">
            <br/>
            <b style="font-size: 1.5em;">{{ $colegio->descripcion }}</b>
            <br/>
            <b style="font-size: 1.1em;">{{ $colegio->slogan }}</b>
            <br/>
            Resolución No. {{ $colegio->resolucion }}<br/>
            {{ $colegio->direccion }}<br/>
            Teléfonos: {{ $colegio->telefonos }}<br/><br/>
        </td>
    </tr>
</table>