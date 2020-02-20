<div class="table-responsive">
    <table class="table table-bordered table-striped" id="myTable">
        <thead>
            @for($i=0;$i<count($encabezado_tabla);$i++)
                <th> {{ $encabezado_tabla[$i] }} </th>
            @endfor
        </thead>
        <tbody>
            @foreach($registros as $fila)
                @php
                    $cant = count($fila);
                @endphp

                <tr>
                    <td> 
                        <div data-modelo_id="{{ Input::get('id_modelo') }}" data-registro_modelo_padre_id="{{ $registro_modelo_padre->id }}" data-registro_modelo_hijo_id="{{ $fila[1] }}" class="elemento_modificar" title="Doble click para modificar."> {{ $fila[0] }} </div>
                    </td>
                    
                    @for( $i=1; $i < $cant; $i++)
                        <td> {{ $fila[$i] }} </td>
                    @endfor
                    
                    <td>
                        <a class="btn btn-danger btn-sm" href="{{ url( 'web/eliminar_asignacion/registro_modelo_hijo_id/'.$fila[1].'/registro_modelo_padre_id/'.$registro_modelo_padre->id.'/id_app/'.Input::get('id').'/id_modelo_padre/'.Input::get('id_modelo') ) }}"><i class="fa fa-btn fa-trash"></i> </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>