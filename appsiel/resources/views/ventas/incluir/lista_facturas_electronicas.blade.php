<h4 class="card-header" style="text-align: center; width: 100%; background-color: #ddd; color: #636363;">{{$titulo}}</h4>
<table class="table table-bordered table-responsive">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th>Fecha</th>
            <th>Doc.</th>
            <th>Cliente</th>
            <th style="display: none;">ID</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $i = 1;
        ?>
        @foreach($facturas as $factura )
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $factura->fecha }}</td>
                <td>{!! $factura->enlace_show_documento() !!}</td>
                <td>{{ number_format( $factura->tercero->numero_identificacion, 0, ',', '.' ) }} / {{ $factura->tercero->descripcion }}</td>
                <td style="display: none;">{{$factura->id}}</td>
            </tr>
            <?php 
                $i++;
            ?>
        @endforeach
    </tbody>
</table>