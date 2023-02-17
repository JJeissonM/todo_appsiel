<?php
    function formato_numero($numero,$tipo){
        if ($numero=='') {
            $numero_formateado='';
        }else{
            switch ($tipo) {
                case 'cantidad':
                    $numero_formateado = number_format($numero, 2, ',','.');
                    break;
                case 'valor':
                    $numero_formateado = '$'.number_format($numero, 2, ',','.');
                    break;
                
                default:
                    # code...
                    break;
            }
        }
        return $numero_formateado;
    }
?>
{{ Form::bsBtnExcel('movimiento_inventarios') }}
<h3>Movimiento de inventarios <small>{{ $bodega->descripcion }}</small></h3>
@if($mensaje_advertencia != '')
    <div class="alert alert-warning">
        <strong>Advertencia!</strong> {{$mensaje_advertencia}}
    </div>
@endif
<div class="table-responsive">
    <table class="table table-striped table-bordered tabla_pdf">
        <thead>
            <tr>
                <th rowspan="2">Fecha</th>
                <th rowspan="2">Documento</th>
                <th rowspan="2">Tercero</th>
                <th colspan="3">Entradas</th>
                <th colspan="3">Salidas</th>
                <th colspan="3">Saldo</th>
            </tr>
            <tr>
                <th>Cant.</th>
                <th>Costo Unit.</th>
                <th>Costo Tot.</th>
                <th>Cant.</th>
                <th>Costo Unit.</th>
                <th>Costo Tot.</th>
                <th>Cant.</th>
                <th>Costo Unit.</th>
                <th>Costo Tot.</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_cant_in = 0;
            $total_cant_out = 0;
                for($i=0;$i<count($productos);$i++){

                    $modelo_crud_id = 0;
                    $url = '/';
                    if( $productos[$i]['core_tipo_transaccion_id'] != '' )
                    {
                        $modelo_crud_id = App\Sistema\TipoTransaccion::find( $productos[$i]['core_tipo_transaccion_id'] )->core_modelo_id;
                        $url = 'inventarios/'.$productos[$i]['documento_id'].'?id=8&id_modelo='.$modelo_crud_id.'&id_transaccion='.$productos[$i]['core_tipo_transaccion_id'];
                    }
                        
            ?>
                <tr>
                    <td>{{ $productos[$i]['fecha'] }}</td>
                    <td><a href="{{ url( $url ) }}" target="_blank">{{ $productos[$i]['documento'] }}</a></td>
                    <td>{{ $productos[$i]['tercero'] }}</td>
                    <td>{{ formato_numero($productos[$i]['cantidad_in'],'cantidad') }}</td>
                    <td>{{ formato_numero($productos[$i]['costo_unit_in'],'valor') }}</td>
                    <td>{{ formato_numero($productos[$i]['costo_total_in'],'valor') }}</td>
                    <td>{{ formato_numero($productos[$i]['cantidad_out'],'cantidad') }}</td>
                    <td>{{ formato_numero($productos[$i]['costo_unit_out'],'valor') }}</td>
                    <td>{{ formato_numero($productos[$i]['costo_total_out'],'valor') }}</td>
                    <td>{{ formato_numero($productos[$i]['cantidad_saldo'],'cantidad') }}</td>
                    <td>{{ formato_numero($productos[$i]['costo_unit_saldo'],'valor') }}</td>
                    <td>{{ formato_numero($productos[$i]['costo_total_saldo'],'valor') }}</td>
                </tr>
            <?php 
            $total_cant_in += (float)$productos[$i]['cantidad_in'];
            $total_cant_out += (float)$productos[$i]['cantidad_out'];
        } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"></td>
                <td>
                    {{ formato_numero($total_cant_in,'cantidad') }}
                </td>
                <td colspan="2"></td>
                <td>
                    {{ formato_numero($total_cant_out,'cantidad') }}
                </td>
                <td colspan="5"></td>
            </tr>
        </tfoot>
    </table>
</div>
    