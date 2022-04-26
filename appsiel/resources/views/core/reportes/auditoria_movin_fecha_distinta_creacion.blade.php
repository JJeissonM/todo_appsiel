{{ Form::bsBtnExcel('movimiento_tesoreria') }}
<h3>Movimientos con fecha distinta a su creación</h3>
<h4> {{"Aplicacion: " . $aplicacion->descripcion }} </h4>
<h5> {{"Desde: ".$fecha_desde." - Hasta: ".$fecha_hasta }} </h5>
<div class="table-responsive">
    <table class="table table-striped table-bordered tabla_pdf">
        <thead>
            <tr>
                <th>Documento</th>
                <th>Tercero</th>
                <th>Fecha</th>
                <th>Fecha creación</th>
                <th>Creado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach( $arr_movin as $key => $movimiento )                
                <tr>
                    <td> {{ $movimiento->get_label_documento() }}</td>
                    <td> {{ $movimiento->tercero->descripcion }}</td>
                    <td> {{ $movimiento->fecha }}</td>
                    <td> {{ $movimiento->created_at }}</td>
                    <td> {{ explode("@",$movimiento->creado_por)[0] }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
</div>
    