<div class="table-responsive">
    <table class="table table-striped table-responsive">
        <thead>
            <tr style=" vertical-align: middle !important;">
                <th>Nro. Documento</th>
                <th>Documento - Categoría</th>
                <th>Vigencia</th>
                <th>Conductor</th>
            </tr>
        </thead>
        <tbody>
            @if($documentos_vencidos_conductores!=null)
            @foreach($documentos_vencidos_conductores as $d)
            <tr>
                <td>{{$d->nro_documento}}</td>
                <td>{{$d->documento}} @if($d->categoria!=null) {{" - CATEGORIA: ".$d->categoria}} @endif</td>
                <td>{{"DESDE: ".$d->vigencia_inicio." - HASTA: ".$d->vigencia_fin}}</td>
                <td>{{$d->conductor->tercero->descripcion}}</td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
</div>