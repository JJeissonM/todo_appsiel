<table>
    <tr>
        <td rowspan="2" width="40%" style="text-align: right;">
            <img src="{{ $url.'?'.rand(1,1000) }}" style="max-width: 190px; max-height: 80px; display: inline;" />
        </td>

        <td align="center">
            <br/>
            <b style="font-size: 1.1em;">{{ $colegio->descripcion }}</b>
            <br/>
            <b style="font-size: 0.9em;">{{ $colegio->slogan }}</b>
            <br/>
            Resolución No. {{ $colegio->resolucion }}
        </td>
    </tr>
</table>