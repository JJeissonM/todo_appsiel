<style type="text/css">
    
    table.banner{
        font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
        font-style: italic;
        font-size: 16px;
        border: 1px solid;
        padding-top: -20px;
    }

</style>

<table class="banner">
    <tr>
        <td rowspan="2" width="40%" style="text-align: center;">
            <img src="{{ $url.'?'.rand(1,1000) }}" style="max-width: 190px; max-height: 80px; display: inline; padding-top: -25px;" />
        </td>

        <td align="center">
            <br/>
            <b style="font-size: 1.1em;">{{ $colegio->descripcion }}</b>
            <br/>
            <b style="font-size: 0.9em;">{{ $colegio->ciudad }}</b>
            <br/>
            Resolución No. {{ $colegio->resolucion }}<br/>
            {{ $colegio->direccion }},Teléfono: {{ $colegio->telefonos }}
        </td>
    </tr>
</table>