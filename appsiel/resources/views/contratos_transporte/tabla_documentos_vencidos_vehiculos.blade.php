<div class="table-responsive">
    <table class="table table-striped table-responsive">
        <thead>
            <tr style=" vertical-align: middle !important;">
                <th>Cod. Interno</th>
                <th>Placa</th>
                <th>Documento</th>
                <th>Vigencia</th>
                <th>Propietario</th>
            </tr>
        </thead>
        <tbody>
            @if($documentos_vencidos_vehiculos!=null)
                @foreach($documentos_vencidos_vehiculos as $d)
                    <tr>
                        <td>{{$d->vehiculo->int}}</td>
                        <td>{{$d->vehiculo->placa}}</td>
                        <td>{{$d->documento}} No. {{$d->nro_documento}}</td>
                        <td>{{"DESDE: ".$d->vigencia_inicio." - HASTA: ".$d->vigencia_fin}}</td>
                        <td>{{$d->vehiculo->propietario->tercero->descripcion}}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>