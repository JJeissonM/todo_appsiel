<style>
    .table2 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        border-collapse: collapse;
        width: 100%;
    }

    .table2 td, .table2 th {
        border: 1px solid #ddd;
        padding: 2px;
    }

    .table2 tr:nth-child(even){background-color: #f2f2f2;}

    .table2 tr:hover {background-color: #ddd;}

    .table2 th {
        text-align: center;
        background-color: #ffffff;
    }
</style>
<div class="table-responsive">
    <table id="myTable" class="table2">
        <thead>
            <tr>
                <th colspan="5" style="text-align: center;">
                    <div style="width: 20%; display: inline-block; vertical-align: middle;">
                        @include('core.dis_formatos.plantillas.render_logo_empresa', ['url' => $url])
                    </div>
                    <div style="width: 70%; display: inline-block; vertical-align: middle;">
                        <h3 style="width: 100%;"> LISTADO DE PLANILLAS DE TRANSPORTE - FUEC </h3>
                        <b>Meses:</b> {{  \App\Http\Controllers\Core\ConfiguracionController::nombre_mes(\Carbon\Carbon::parse($fecha_desde)->format('m')) }} a {{ \App\Http\Controllers\Core\ConfiguracionController::nombre_mes(\Carbon\Carbon::parse($fecha_hasta)->format('m')) }} <br>
                        <b>Cantidad planillas generadas:</b> {{ count($contracts) }} 
                    </div>
                </th>
            </tr>
            <tr>
                <th style="width: 10%;"> Fuec No. </th>
                <th style="width: 10%;"> Placa Vehículo </th>
                <th> ORIGEN - DESTINO </th>
                <th> Vigencia FUEC </th>
                <th> Nombre Contratante </th>
            </tr>
        </thead>
        <tbody>
            @foreach( $contracts as $contract )
                <tr>
                    <td align="center"> {{ $contract->numero_fuec }} </td>
                    <td align="center"> {{ $contract->vehiculo->placa }} </td>
                    <td> {{ $contract->origen }} - {{ $contract->destino }} </td>
                    <td> 
                        Desde: {{ $contract->fecha_inicio }} <br>
                        Hasta: {{ $contract->fecha_fin }}
                    </td>
                    <td>
                        <?php 
                            $contrato = $contract;
                            if($contract->tipo_registro == 'fuec_adicional') {
                                $contrato = $contract->contrato;
                            }

                            $lbl_contratante = $contrato->contratanteText;
                            if ( $contrato->contratante != null ) {
                                $lbl_contratante = $contrato->contratante->tercero->descripcion;
                            }
                        ?>
                        {{ $lbl_contratante }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>