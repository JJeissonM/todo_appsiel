
<table style="border: 1px solid; width: 100%; border-collapse: collapse;">
    <tbody>
        <tr>
            <th style="border: 1px solid; padding-left: 5px; font-weight: bold; text-align:center;" colspan="2">GRUPO DE USUARIOS</th>
        </tr>
        <tr>
            <td style="border: 1px solid; padding-left: 5px; font-weight: bold; text-align:center;">IDENTIFICACIÓN</td>
            <td style="border: 1px solid; padding-left: 5px; font-weight: bold; text-align:center;">NOMBRE COMPLETO</td>
        </tr>
        @foreach($c->contratogrupous as $p)
            <tr>
                <td style="border: 1px solid; padding-left: 5px;">{{$p->identificacion}}</td>
                <td style="border: 1px solid; padding-left: 5px;">{{$p->persona}}</td>
            </tr>
        @endforeach
    </tbody>
</table>