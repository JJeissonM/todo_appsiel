<table class="table table-bordered">
    <tr>
        <td style="border: solid 1px black;" colspan="3">
            <h3>Datos del estudiante</h3>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Nombre:</b> {{ $estudiante->tercero->apellido1 }} {{ $estudiante->tercero->apellido2 }} {{ $estudiante->tercero->nombre1 }} {{ $estudiante->tercero->otros_nombres }}
        </td>
        <td style="border: solid 1px black;" colspan="2">
            <b>Documento:</b> {{ $estudiante->tercero->numero_identificacion }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Dirección:</b> {{ $estudiante->tercero->direccion1 }}
        </td>
        <td style="border: solid 1px black;">
            <b>Teléfono:</b> {{ $estudiante->tercero->telefono1 }}
        </td>
        <td style="border: solid 1px black;">
            <b>Email:</b> {{ $estudiante->tercero->email }}
        </td>
    </tr>
</table>