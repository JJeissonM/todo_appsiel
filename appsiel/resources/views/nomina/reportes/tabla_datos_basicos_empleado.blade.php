<table style="border: 1px solid; border-collapse: collapse; width:100%; font-size: 12px;">
    <tr>
        <td style="border: 1px solid;"> <b> Empleado: </b> {{ $empleado->tercero->descripcion }} &nbsp;&nbsp; | &nbsp;&nbsp;  <b> C.C.: </b> {{ number_format($empleado->tercero->numero_identificacion,'0',',','.') }}  </td>
        <td style="border: 1px solid;"> <b> Cargo: </b> {{ $empleado->cargo->descripcion }}</td>
        <td style="border: 1px solid;"> {{ Form::TextoMoneda( $empleado->sueldo, 'Sueldo: ') }} </td>
    </tr>
    <tr>
        <td style="border: 1px solid;"><b> Fecha ingreso: </b> {{ $empleado->fecha_ingreso }}</td>
        <td style="border: 1px solid;" colspan="2">

            @if( $empleado->entidad_salud != null)
                <b> E.P.S.: </b> {{ $empleado->entidad_salud->descripcion }}                
            @endif
            
            @if( $empleado->entidad_pension != null)
                &nbsp;&nbsp; | &nbsp;&nbsp;
                <b> A.F.P.: </b> {{ $empleado->entidad_pension->descripcion }}                
            @endif
            
            @if( $empleado->entidad_arl != null)
                &nbsp;&nbsp; | &nbsp;&nbsp;
                <b> A.R.L.: </b> {{ $empleado->entidad_arl->descripcion }}                
            @endif
        </td>
    </tr>
</table>