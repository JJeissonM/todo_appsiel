<?php

$fila_foot = '<tr>
					                <td style="display: none;"> <div id="total_valor_total">0</div> </td>
					                <td colspan="5">&nbsp;</td>
					                <td> <div id="total_valor_aux">$0</div> </td>
					                <td> &nbsp;</td>
					            </tr>';

$datos = [
    'titulo' => '',
    'columnas' => [
        [ 'name' => 'teso_medio_recaudo_id', 'display' => '', 'etiqueta' => 'Medio de recaudo', 'width' => ''],
        [ 'name' => 'teso_motivo_id', 'display' => '', 'etiqueta' => 'Motivo', 'width' => ''],
        [ 'name' => 'teso_caja_id', 'display' => '', 'etiqueta' => 'Caja', 'width' => ''],
        [ 'name' => 'teso_cuenta_bancaria_id', 'display' => '', 'etiqueta' => 'Cta. Bancaria', 'width' => ''],
        [ 'name' => 'valor', 'display' => '', 'etiqueta' => 'Valor', 'width' => ''],
        [ 'name' => '', 'display' => '', 'etiqueta' => ' ', 'width' => '10px']
    ],
    'fila_body' => '',
    'fila_foot' => '<tr>
								                <td colspan="4">&nbsp;</td>
								                <td> <div id="total_valor_total">$0.00</div> </td>
								                <td> &nbsp; </td>
								            </tr>'
];
?>

<ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#home">Medios de Pago/Recaudo</a></li>
</ul>

<div class="tab-content">

    <div id="home" class="tab-pane fade in active">
        <div id="div_ingreso_registros_medios_recaudo">
            <br>
            <div class="table-responsive" id="table_contentenido">
                <table class="table table-striped" id="ingreso_registros_medios_recaudo">
                    <thead>
                    <tr>
                        <th data-override="teso_medio_recaudo_id">Medio de Pago/Recaudo</th>
                        <th data-override="teso_motivo_id">Motivo</th>
                        <th data-override="teso_caja_id">Caja</th>
                        <th data-override="teso_cuenta_bancaria_id">Cta. Bancaria</th>
                        <th data-override="valor">Valor</th>
                        <th width="10px"> </th>
                    </tr>
                    </thead>
                    <tbody>
                        
                        @if( isset( $cuerpo_tabla_medios_recaudos ) )
                            {!! $cuerpo_tabla_medios_recaudos !!}
                        @endif

                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">&nbsp;</td>
                        <td> 
                            <div id="total_valor_total">$0.00</div>
                        </td>
                        <td> &nbsp; </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <a id="btn_nuevo" style="background-color: transparent; color: #3394FF; border: none; cursor: pointer;"><i class="fa fa-btn fa-plus"></i> Agregar registro</a>
    </div>

</div>

<!-- Modal -->
@include('tesoreria.incluir.ingreso_valores_recaudos')
@section('scritps')

@endsection