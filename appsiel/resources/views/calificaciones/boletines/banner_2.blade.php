<table class="banner">
    <tr>
        <td width="25%" align="center" style="text-align: center; vertical-align: middle;">
            <div style="width: 100%; text-align: center;">
                <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen }}" style="max-width: 190px; max-height: 70px; padding: 2px;" />
            </div>
        </td>

        <td align="center" @if( isset($centrar_banner_en_hoja) && $centrar_banner_en_hoja ) width="50%" @endif>
            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->descripcion }}</b>
            <br/>
            <b style="font-size: {{$tam_letra - 0.5}}mm;">{!! $colegio->slogan !!}</b>
            <br/>
            <span style="font-size: {{$tam_letra - 0.5}}mm;">
                Resolución No. {{ $colegio->resolucion }}<br/>
                {{ $colegio->direccion }}, {{ $colegio->ciudad }}, Teléfono: {{ $colegio->telefonos }}
            </span>
        </td>

        @if( isset($centrar_banner_en_hoja) && $centrar_banner_en_hoja )
            <td width="25%">&nbsp;</td>
        @endif
    </tr>
</table>
