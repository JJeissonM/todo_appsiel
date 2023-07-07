<div>
    <table>
        <tr>
            <td style="width:120px;"><b>{{ config('calificaciones.etiqueta_estudiante') }}:</b></td>
            <td>&nbsp;&nbsp;{{ $registro->estudiante->tercero->descripcion }}.</td>
            @if($colegio->maneja_puesto=="Si")
                @if( !is_null($registro->observacion) )
                    @if( $registro->observacion->puesto == "" )
                        <td> <b> ¡¡Puesto No calculado!! </b> </td>
                    @else
                        <td> <b>Puesto:</b> {{ $registro->observacion->puesto }} </td>
                    @endif
                @endif
            @endif
        </tr>
        <tr>
            <td style="width:120px;"><b>{{ config('calificaciones.etiqueta_curso') }}: </b></td>
            <td>&nbsp;&nbsp;{{ $curso->descripcion }}.</td>
            @if($colegio->maneja_puesto=="Si")
                @if( !is_null($registro->observacion) )
                    <td> &nbsp; </td>
                @endif
            @endif
        </tr>
    </table>        	
</div>