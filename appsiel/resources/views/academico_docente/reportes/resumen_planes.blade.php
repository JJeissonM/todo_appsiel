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

<table class="table table-bordered table-striped" style="font-size: 0.9em;">
    <thead>
        <tr>
            <th>Curso</th>
            <th>Asignatura</th>
            <th>Lista de planes de clases</th>
        </tr>
    </thead>
    <tbody>
        @foreach( $lineas_planes_clases as $linea )
            <tr>
                <td>
                    {{ $linea->curso }}
                </td>
                <td>
                    {{ $linea->asignatura }}
                </td>
                <td>
                    <ol>
                        @foreach( $linea->lista_planes_clases as $pla_clases )
                            <li> {{ $etiqueta_primer_elemento_plantilla }}: {!! $pla_clases['contenido_primer_elemento_plantilla'] !!} > Fecha: {{ $pla_clases['fecha_plan_clases'] }} > {!! $pla_clases['enlace_plan_clases'] !!}</li>
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