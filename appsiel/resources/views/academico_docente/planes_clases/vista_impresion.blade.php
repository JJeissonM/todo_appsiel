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

<table class="table table-bordered" style="font-size: 0.8em;">
    @foreach( $registros as $registro )
        <tr>
            <td>
                <b> {{ $registro->elemento_descripcion }} </b>
            </td>
            <td>
                {!! $registro->contenido !!}
            </td>
        </tr>
    @endforeach
</table>