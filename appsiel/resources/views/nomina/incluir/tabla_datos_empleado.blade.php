
<table class="table" style="border: 1px solid; border-collapse: collapse; width:100%; font-size: 12px;">
    <tr>
        <td style="border: 1px solid;"> <b> Empleado: </b> {{ optional($empleado->tercero)->descripcion ?? 'Sin asignar' }}</td>
        <td style="border: 1px solid;"> <b> Cargo: </b> {{ optional($empleado->cargo)->descripcion ?? 'Sin asignar' }}</td>
        <td style="border: 1px solid;"> {{ Form::TextoMoneda( $empleado->sueldo, 'Sueldo: ') }} </td>
    </tr>
    <tr>
        <td style="border: 1px solid;"><b> Fecha ingreso: </b> {{ $empleado->fecha_ingreso }}</td>
        <td style="border: 1px solid;" colspan="2">
            <b> E.P.S.: </b> {{ optional($empleado->entidad_salud)->descripcion ?? 'Sin asignar' }}
            &nbsp;&nbsp; | &nbsp;&nbsp;
            <b> A.F.P.: </b> {{ optional($empleado->entidad_pension)->descripcion ?? 'Sin asignar' }}
            &nbsp;&nbsp; | &nbsp;&nbsp;
            <b> A.R.L.: </b> {{ optional($empleado->entidad_arl)->descripcion ?? 'Sin asignar' }}
        </td>
    </tr>
</table>
