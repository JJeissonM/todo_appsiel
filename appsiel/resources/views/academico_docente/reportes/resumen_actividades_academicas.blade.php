<div style="font-size: 10px;">
    @include('banner_colegio')
</div>

<?php $cant_elementos = 0; ?>

<table class="table table-fluid" style="font-size: 11px;">
    <tr>
        <td>
            <b> Periodo: </b> {{ $periodo->descripcion }}
        </td>
        <td>
            <b> Profesor: </b> {{ $profesor->name }}
        </td>
    </tr>
</table>

<table class="table table-bordered table-striped" style="font-size: 0.9em;">
    {{ Form::bsTableHeader(['Curso','Asignatura','Lista actividades']) }}
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
                    <ol>
                        @foreach( $linea->lista_actividades as $actividad )
                            <li> {{ $actividad['descripcion'] }} {!! $actividad['enlace_actividad'] !!}</li>
                        @endforeach
                    <ol>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>

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