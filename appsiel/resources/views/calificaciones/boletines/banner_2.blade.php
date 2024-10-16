<table class="banner">
    <tr>
        <td width="25%">
            <div class="imagen">
                <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen }}" style="max-width: 190px; max-height: 70px; padding: 2px;" />
            </div>
        </td>

        <td align="center">
            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->descripcion }}</b>
            <br/>
            <b style="font-size: {{$tam_letra - 0.7}}mm;">{!! $colegio->slogan !!}</b>
            <br/>
            <span style="font-size: {{$tam_letra - 0.7}}mm;">
                Resolución No. {{ $colegio->resolucion }}<br/>
                {{ $colegio->direccion }}, {{ $colegio->ciudad }}, Teléfono: {{ $colegio->telefonos }}
            </span>
        </td>
    </tr>
</table>