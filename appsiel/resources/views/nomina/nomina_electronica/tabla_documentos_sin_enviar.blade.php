
<h4>{{ $model->modelo->descripcion }}</h4>
<div id="div_datos_doc_soporte">

    <table class="table table-bordered table-striped table-hover" id="tbDatos">
        {{ Form::bsTableHeader($encabezado_tabla) }}
        <tbody>
            @foreach ($registros as $fila)
                <tr>
                    <td> 
                        <a href="{{ url('/nom_electronica_enviar_documentos') . '/[' . $fila->id . ']' }}" class="btn btn-info btn-sm"> <i class="fa fa-send"></i> Enviar </a>
                    </td>
                    <td> {{ $fila->fecha }} </td>
                    <td> {{ $fila->get_value_to_show() }} </td>
                    <td> {{ $fila->empleado->tercero->descripcion }} </td>
                    <td> {{ $fila->estado }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>