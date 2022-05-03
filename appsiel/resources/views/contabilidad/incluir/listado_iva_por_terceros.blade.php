<?php
	$total_base = 0;
    $total_valor_impuesto = 0;
    $j=0;
?>

<h3 style="width: 100%; text-align: center;"> {{$title}} </h3>
<hr>
<div class="table-responsive">
    <table id="myTable" class="table table-striped" style="margin-top: -4px;">
        {{ Form::bsTableHeader(['No. identificación','Tercero','Impuesto','Tot. Base', 'Tot. impuesto']) }}
        <tbody>
            @foreach($group_taxes_third_parties as $tax)
                <tr class="tax-{{$j}}">
                    <td> {{ $tax->tercero_numero_identificacion }} </td>
                    <td> {{ $tax->tercero_descripcion }} </td>
                    <td> {{ $tax->group }} </td>
                    <td class="text-center"> ${{ number_format( $tax->base_impuesto, 0, ',', '.') }} </td>
                    <td class="text-center"> ${{ number_format( $tax->valor_impuesto, 0, ',', '.') }} </td>
                </tr>
                <?php
                    $j++;
                    if ($j == 3)
                    {
                        $j=1;
                    }
                    $total_base += $tax->base_impuesto;
                    $total_valor_impuesto += $tax->valor_impuesto;
                ?>
            @endforeach

            <tr class="tax-{{$j}}" >
                <td colspan="3"></td>
                <td class="text-center">
                   ${{ number_format($total_base, 0, ',', '.')}}
                </td>
                <td class="text-center">
                   ${{ number_format($total_valor_impuesto, 0, ',', '.')}}
                </td>
            </tr>
        </tbody>
    </table>
</div>