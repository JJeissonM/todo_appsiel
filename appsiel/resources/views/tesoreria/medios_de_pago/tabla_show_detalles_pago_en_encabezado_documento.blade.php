<?php 

    $medio_pago = $doc_encabezado->medio_recaudo;
    $caja_banco = '';
    $cuenta_bancaria = null;
    if ( !is_null( $medio_pago ) )
    {
        switch ( $medio_pago->comportamiento )
        {
            case 'Efectivo':
                $caja_banco = $doc_encabezado->caja->descripcion;
                break;

            case 'Tarjeta bancaria':
                $caja_banco = 'Cuenta de ' . $doc_encabezado->cuenta_bancaria->tipo_cuenta . ' ' . $doc_encabezado->cuenta_bancaria->entidad_financiera->descripcion . ' No. ' . $doc_encabezado->cuenta_bancaria->descripcion;
                break;
            
            default:
                break;
        }
    }

?>

@if( !is_null( $medio_pago ) )
    <div class="table-responsive contenido">
        <table class="table table-bordered">
            {{ Form::bsTableHeader(['Medio de pago','Caja/Cta. Bancaria','Valor']) }}
            <tbody>
                <tr>
                    <td> {{ $medio_pago->descripcion }} </td>
                    <td> {{ $caja_banco }} </td>
                    <td align="right"> ${{ number_format($doc_encabezado->valor_total, 0, ',', '.') }} </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"> &nbsp; </td>
                    <td align="right">
                       $ {{ number_format($doc_encabezado->valor_total, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif