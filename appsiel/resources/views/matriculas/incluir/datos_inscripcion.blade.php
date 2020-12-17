<table class="table table-bordered">
    <tr>
        <td style="border: solid 1px black;" colspan="3">
            <h3>Datos del estudiante</h3>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Nombre:</b> {{ $tercero->apellido1 }} {{ $tercero->apellido2 }} {{ $tercero->nombre1 }} {{ $tercero->otros_nombres }}
        </td>
        <td style="border: solid 1px black;" colspan="2">
            <b>Documento:</b> {{ $tercero->numero_identificacion }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Dirección:</b> {{ $tercero->direccion1 }}
        </td>
        <td style="border: solid 1px black;">
            <b>Teléfono:</b> {{ $tercero->telefono1 }}
        </td>
        <td style="border: solid 1px black;">
            <b>Email:</b> {{ $tercero->email }}
        </td>
    </tr>
</table>