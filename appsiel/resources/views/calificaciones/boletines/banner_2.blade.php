<table>
    <tr>
        <td rowspan="2" width="200px">
            <img src="{{ $url.'?'.rand(1,1000) }}" width="60px" height="60px" />
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