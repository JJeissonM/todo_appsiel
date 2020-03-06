<div style="font-size: 10px;">
    @include('banner_colegio')
</div>


<table class="table table-fluid">
    <tr>
        <td colspan="2" align="center"> <b style="font-size: 1.2em;">{{ $encabezado->plantilla_decripcion }}</b></td>
    </tr>
    <tr>
        <td>
            <b> Fecha: </b> {{ $encabezado->fecha }}
        </td>
        <td>
            <b> Semana: </b> {{ $encabezado->semana_decripcion }}
        </td>
    </tr>
    <tr>
        <td>
            <b> Periodo: </b> {{ $encabezado->periodo_decripcion }}
        </td>
        <td>
            <b> Curso: </b> {{ $encabezado->curso_decripcion }}
        </td>
    </tr>
    <tr>
        <td>
            <b> Asignatura: </b> {{ $encabezado->asignatura_decripcion }}
        </td>
        <td>
            <b> Profesor: </b> {{ $encabezado->usuario_decripcion }}
        </td>
    </tr>
</table>


@foreach( $registros as $registro )
    
    <h4> <b> {{ $registro->elemento_descripcion }} </b> </h4>
    
    <hr>

    <div style="padding: 15px;">
        {!! $registro->contenido !!}
    </div>
@endforeach