<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 40%; text-align: center; font-weight: bold; margin-top: 10px !important; font-size: 12px;">@if($empresa!=null) {{$empresa->direccion1." - "}} @endif {{$v->direccion}}<br> @if($empresa!=null) {{$empresa->telefono1." - "}} @endif {{$v->telefono}}<br><a> @if($empresa!=null) {{$empresa->email." - "}} @endif {{$v->correo}}</a></td>
            <td class="border" style="width: 20%; text-align: center; font-weight: bold; margin-top: 10px !important;">
                <img src="{{config('contratos_transporte.url_imagen_sello_empresa')}}" style="max-width: 120px;">
                <br>Sello
            </td>
            <td class="border" style="width: 40%; text-align: center; font-weight: bold; margin-top: 10px !important; font-size: 14px;">
                <img src="{{config('contratos_transporte.url_imagen_firma_rep_legal')}}"  style="max-height: 70px;">
                <br>FIRMA
                <br>
                <i style="font-size: 9px; text-decoration: none;" valign="bottom">{{$v->firma}}</i>
            </td>
        </tr>
    </tbody>
</table>