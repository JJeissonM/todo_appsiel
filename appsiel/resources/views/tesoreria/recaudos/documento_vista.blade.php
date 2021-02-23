<?php 
    $total_recaudo=0;
    $i=0;
    $vec_motivos = [];
?>
@if(isset($nombre))
    <div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Detalles del traslado
    </div>
@else
<div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Detalles del recaudo </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered">
        {{ Form::bsTableHeader(['Medio de pago','Caja/Cta. Bancaria','Valor']) }}
        <tbody>
            @foreach ($doc_registros as $registro)
                <tr>
                    <td> {{ $registro->medio_recaudo }} </td>
                    <td> {{ $registro->caja }} {{ $registro->cuenta_bancaria }} </td>
                    <td> ${{ number_format($registro->valor, 0, ',', '.') }} </td>
                </tr>
                <?php
                    $total_recaudo += $registro->valor;

                    // Si el motivo no está en el array, se agregan sus valores por primera vez
                    if ( !isset( $vec_motivos[$registro->motivo_id] ) )
                    {
                        $vec_motivos[$registro->motivo_id]['descripcion'] = $registro->motivo;
                        $vec_motivos[$registro->motivo_id]['total_motivo'] = $registro->valor;
                    }else{
                        // si ya está el motivo en el array, se acumula su valor
                        $vec_motivos[$registro->motivo_id]['total_motivo'] += $registro->valor;
                    }                
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td style="border-top: solid 1px black;">
                   ${{ number_format($total_recaudo, 0, ',', '.') }} ({{ NumerosEnLetras::convertir($total_recaudo,'pesos',false) }})
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<?php 
    $total_motivo=0;
?>
<div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Detalle de los movimientos </div>
<div class="table-responsive">
    <table class="table table-bordered">
        {{ Form::bsTableHeader(['Motivo','Valor']) }}
        <tbody>
            @foreach ( $vec_motivos as $registro )
                <tr>
                    <td> {{ $registro['descripcion'] }} </td>
                    <td> ${{ number_format( $registro['total_motivo'], 0, ',', '.') }} </td>
                </tr>
                <?php
                    $total_motivo += $registro['total_motivo'];               
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td> &nbsp; </td>
                <td style="border-top: solid 1px black;">
                   ${{ number_format($total_motivo, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>