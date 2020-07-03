<div style="font-size: 10px;">
    @include('banner_colegio')
</div>

<?php $cant_elementos = 0; ?>

<table class="table table-fluid" style="font-size: 11px;">
    <tr>
        <td colspan="2" align="center"> <b>{{ $plantilla->decripcion }}</b></td>
    </tr>
    <tr>
        <td>
            <b> Fecha: </b> desde {{ $fecha_desde }} hasta {{ $fecha_hasta }}
        </td>
        <td>
            <b> Profesor: </b> {{ $profesor->name }}
        </td>
    </tr>
</table>

<br>

<table class="table table-bordered table-striped" style="font-size: 0.9em;">
    <thead>
        <tr>
            <th>Curso</th>
            <th>Asignatura</th>
            <th>Fecha</th>
            @foreach( $elementos_plantilla as $elemento )
                <th>{{ $elemento->descripcion }}</th>
                <?php $cant_elementos++; ?>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach( $lineas_asignaturas as $linea )
            <tr>
                <td>
                    {{ $linea->curso }}
                </td>
                <td>
                    {{ $linea->asignatura }}
                </td>
                <td>
                    {{ $linea->fecha }}
                </td>

                @if( is_null($linea->contenido_elementos) )
                    @for($i = 0; $i < $cant_elementos; $i++)
                        <td> &nbsp; </td>
                    @endfor
                @else
                    @foreach( $linea->contenido_elementos as $key => $value )
                        <td>
                            {!! $value !!}
                        </td>
                    @endforeach
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

<table width="100%" style="text-align: center;">
    <tr>
        <td width="15%"> </td>
        <td width="30%"> _______________________ </td>
        <td width="10%"> </td>
        <td width="30%"> _______________________ </td>
        <td width="15%"> </td>
    </tr>
    <tr>
        <td width="15%"> </td>
        <td width="30%"> DOCENTE </td>
        <td width="10%"> </td>
        <td width="30%"> Vo. Bo. COORDINADOR </td>
        <td width="15%"> </td>
    </tr>
</table>