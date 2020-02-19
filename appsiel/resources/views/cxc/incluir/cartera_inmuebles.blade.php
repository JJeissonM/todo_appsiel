<?php 
    use App\CxC\CxcMovImiento;

    //print_r($movimiento_cxc);
?>
<table class="table table-bordered" id="documentos_pendientes">
    {{ Form::bsTableHeader(['Inmueble','Documento','Detalle','Fecha','Vlr. cartera','Saldo pend.','Vlr. aplicar']) }}
    <tbody>
        <?php
        for($i=0;$i<count($movimiento_cxc);$i++)
        { 
            $id = $movimiento_cxc[$i]['id'];
            $class_advertencia = "";
            $advertencia = '';
            $ocultar = '';

            if ( isset($movimiento_cxc[$i]['fecha_registro']) )
            {
                $ultimo_estado = DB::table('cxc_documento_tiene_recaudos')->where('cxc_documento_id', $id)->where('fecha_registro', '>', $movimiento_cxc[$i]['fecha_registro']);

                if ( count($ultimo_estado) > 0) 
                {
                    $class_advertencia = "danger";
                    $advertencia = '<a href="#" data-toggle="tooltip" title="¡Advertencia! Existen movimientos posteriores para este documento. Si aplica algún pago en la fecha que ingresó arriba, puede causar descuadres de cartera."><i class="fa fa-exclamation-triangle"></i> </a>';
                    $ocultar = 'none';
                }
            }            



        ?>
            <tr id="{{ $movimiento_cxc[$i]['core_tipo_transaccion_id'].'a3p0'.$id }}" class="{{$class_advertencia}}">
                <td> {{ $movimiento_cxc[$i]['codigo_referencia_tercero'] }} </td>
                <td> {{ $movimiento_cxc[$i]['documento'] }} </td>
                <td> {{ $movimiento_cxc[$i]['detalle_operacion'] }} </td>
                <td> {{ $movimiento_cxc[$i]['fecha'] }} </td>
                <td class="col_valor_cartera"> {{ number_format($movimiento_cxc[$i]['valor_cartera'], 0, ',', '.') }} </td>
                <td class="col_saldo_pendiente" > {{ number_format($movimiento_cxc[$i]['saldo_pendiente'], 0, ',', '.') }} </td>
                <td> 
                    {{ Form::text('text_aplicar_'.$id, $movimiento_cxc[$i]['saldo_pendiente'], [ 'id' => 'text_aplicar_'.$id, 'class' => 'text_aplicar' ] ) }} 
                    <button class="btn btn-success btn-xs btn_agregar_documento" style="display: {{$ocultar}};"><i class="fa fa-check"></i></button>
                    {!! $advertencia !!}
                </td>
            </tr>
        <?php 
        } ?>
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="3">Pendiente x aplicar (Anticipo)</td>
            <td>{{ Form::text( 'pendiente_aplicar', null, [ 'id' => 'pendiente_aplicar', 'disabled' => 'disabled' ] ) }} <button class="btn btn-success btn-xs btn_agregar_documento"><i class="fa fa-check"></i></button></td>
        </tr>                       
    </tfoot>
</table>