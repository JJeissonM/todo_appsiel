<?php 
    //use App\CxC\CxcEstadoCartera;
?>
<table class="table table-bordered" id="documentos_afavor" width="100%">
    {{ Form::bsTableHeader(['Documento','Fecha','Saldo a favor','Vlr. aplicar']) }}
    <tbody>
        <?php
        for($i=0;$i<count($movimiento);$i++)
        { 
            $id = $movimiento[$i]['id'];

            /*$ultimo_estado = CxcEstadoCartera::ultimo_estado($id);

            if ( $ultimo_estado->fecha_registro > $fecha_doc) 
            {
                $class_advertencia = "danger";
                $advertencia = '<a href="#" data-toggle="tooltip" title="¡Advertencia! Existen movimientos posteriores para este documento. Si aplica algún pago en la fecha que ingresó arriba, puede causar descuadres de cartera."><i class="fa fa-exclamation-triangle"></i> </a>';
                $ocultar = 'none';
            }else{
                $class_advertencia = "";
                $advertencia = '';
                $ocultar = '';
            }*/

            $class_advertencia = '';
            $advertencia = '';
            $ocultar = '';

        ?>
        @if( $movimiento[$i]['saldo_pendiente'] <= -0.1 )
            <tr id="{{ $id }}" class="{{$class_advertencia}}">
                <td class="text-center"> {{ $movimiento[$i]['documento'] }} </td>
                <td> {{ $movimiento[$i]['fecha'] }} </td>
                <td class="col_saldo_pendiente text-right" >
                    <?php
                        $saldo_pendiente = $movimiento[$i]['saldo_pendiente'] * -1;
                    ?> 
                    {{ number_format($saldo_pendiente, 2, ',', '.') }} 
                </td>
                <td>
                    {{ Form::text('text_aplicar_'.$id, $saldo_pendiente, [ 'id' => 'text_aplicar_'.$id, 'class' => 'text_aplicar' ] ) }} 
                    <button class="btn btn-success btn-xs btn_agregar_documento_afavor" style="display: {{$ocultar}};" ><i class="fa fa-check"></i></button>
                    {!! $advertencia !!}
                </td>
            </tr>
        @endif
        <?php 
        } ?>
    </tbody>
</table>