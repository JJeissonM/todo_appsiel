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
    <tr>
        <?php  
            $user = \Auth::user();
        ?>

        <td colspan="2">
            @if( $encabezado->archivo_adjunto != '')
                <div class="row">
                    <div class="col-md-8">
                        <b>Archivo adjunto: </b>
                        &nbsp;&nbsp;
                        <a href="{{ config('configuracion.url_instancia_cliente').'/storage/app/planes_clases/'.$encabezado->archivo_adjunto }}" class="btn btn-success btn-sm" target="_blank"> <i class="fa fa-file"></i> {{ $encabezado->archivo_adjunto }} </a>

                    </div>
                    <div class="col-md-4">
                        @if( !$user->hasRole('Estudiante') )
                            <a href="{{ url( 'sga_planes_clases_remover_archivo_adjunto/'.$encabezado->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}" class="btn btn-danger btn-xs"> <i class="fa fa-trash"></i>&nbsp;Remover adjunto</a>
                        @else
                            &nbsp;
                        @endif
                    </div>
                </div>
            @else
                <b>Archivo adjunto: </b>
            @endif
        </td>
    </tr>
</table>


@foreach( $registros as $registro )
    
    @if( $registro->elemento_descripcion != '' )
        <h4> <b> {{ $registro->elemento_descripcion }} </b> </h4>
    @else
        <h4> <b> CONTENIDO </b> </h4>
    @endif
    
    <hr>

    <div style="padding: 15px;">
        {!! $registro->contenido !!}
    </div>
@endforeach