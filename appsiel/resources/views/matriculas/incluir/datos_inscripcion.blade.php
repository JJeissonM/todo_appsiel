<table class="table table-bordered">
    <tr>
        <td style="border: solid 1px black;" colspan="3">
            <h3>Datos del estudiante</h3>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Nombre:</b> {{ $inscripcion->tercero->apellido1 }} {{ $inscripcion->tercero->apellido2 }} {{ $inscripcion->tercero->nombre1 }} {{ $inscripcion->tercero->otros_nombres }}
        </td>
        <td style="border: solid 1px black;" colspan="2">
            <b>Documento:</b> {{ $inscripcion->tercero->numero_identificacion }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Dirección:</b> {{ $inscripcion->tercero->direccion1 }}
        </td>
        <td style="border: solid 1px black;">
            <b>Teléfono:</b> {{ $inscripcion->tercero->telefono1 }}
        </td>
        <td style="border: solid 1px black;">
            <b>Email:</b> {{ $inscripcion->tercero->email }}
        </td>
    </tr>
</table>