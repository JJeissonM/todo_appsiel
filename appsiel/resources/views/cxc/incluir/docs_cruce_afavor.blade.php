<?php 
    //use App\CxC\CxcEstadoCartera;
?>
<table class="table table-bordered" id="documentos_afavor" width="100%">
    @if ($vista == 'formulario')
        {{ Form::bsTableHeader(['Documento','Fecha','Saldo a favor','Vlr. aplicar']) }}
    @else
        {{ Form::bsTableHeader(['Documento','Fecha','Saldo a favor']) }}
    @endif
    
    <tbody>
        <?php
        $total_saldo = 0;
        for($i=0;$i<count($movimiento_cxc);$i++)
        { 
            $id = $movimiento_cxc[$i]['id'];

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
        @if( $movimiento_cxc[$i]['saldo_pendiente'] < 0 )
            <tr id="{{ $id }}" class="{{$class_advertencia}}">
                <td class="text-center"> {{ $movimiento_cxc[$i]['documento'] }} </td>
                <td> {{ $movimiento_cxc[$i]['fecha'] }} </td>
                <td class="col_saldo_pendiente text-right" >
                    <?php
                        $saldo_pendiente = $movimiento_cxc[$i]['saldo_pendiente'] * -1;

                        $total_saldo += $saldo_pendiente;
                    ?> 
                    $ {{ number_format($saldo_pendiente, 0, ',', '.') }} 
                </td>
                @if ($vista == 'formulario')
                    <td>
                        {{ Form::text('text_aplicar_'.$id, $saldo_pendiente, [ 'id' => 'text_aplicar_'.$id, 'class' => 'text_aplicar' ] ) }} 
                        <button class="btn btn-success btn-xs btn_agregar_documento_afavor" style="display: {{$ocultar}};" ><i class="fa fa-check"></i></button>
                        {!! $advertencia !!}
                    </td>
                @endif                
            </tr>
        @endif
        <?php 
        } ?>

        @if ($vista == 'show')
            <tr>
                <td colspan="2"></td>
                <td class="col_saldo_pendiente text-right" >
                    $ {{ number_format($total_saldo, 0, ',', '.') }} 
                </td>
            </tr>
        @endif

    </tbody>
</table>