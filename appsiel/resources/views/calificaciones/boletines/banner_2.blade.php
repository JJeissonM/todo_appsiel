<table class="banner">
    <tr>
        <td rowspan="2" width="40%" style="text-align: center;">
            <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen }}" style="max-width: 190px; max-height: 80px; display: inline; padding-top: -25px;" />
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