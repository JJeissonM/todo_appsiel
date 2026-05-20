<?php 

    $medio_pago = $doc_encabezado->medio_recaudo;
    $caja_banco = '';
    $cuenta_bancaria = null;
    $errores_medios_pago_encabezado = [];

    if ( (int)$doc_encabezado->teso_medio_recaudo_id != 0 && is_null($medio_pago) )
    {
        $errores_medios_pago_encabezado[] = 'El medio de recaudo ID ' . $doc_encabezado->teso_medio_recaudo_id . ' no existe en la base de datos.';
    }

    if ( !is_null( $medio_pago ) )
    {
        switch ( $medio_pago->comportamiento )
        {
            case 'Efectivo':
                if ( !is_null($doc_encabezado->caja) )
                {
                    $caja_banco = $doc_encabezado->caja->descripcion;
                }
                break;

            case 'Tarjeta bancaria':
                if ( !is_null($doc_encabezado->cuenta_bancaria) )
                {
                    $entidad_financiera_descripcion = '';
                    if ( !is_null($doc_encabezado->cuenta_bancaria->entidad_financiera) )
                    {
                        $entidad_financiera_descripcion = $doc_encabezado->cuenta_bancaria->entidad_financiera->descripcion;
                    }

                    $caja_banco = 'Cuenta de ' . $doc_encabezado->cuenta_bancaria->tipo_cuenta . ' ' . $entidad_financiera_descripcion . ' No. ' . $doc_encabezado->cuenta_bancaria->descripcion;
                }
                break;
            
            default:
                break;
        }
    }

?>

@if( !empty($errores_medios_pago_encabezado) )
    <div class="alert alert-danger">
        <ul style="margin-bottom: 0;">
            @foreach( $errores_medios_pago_encabezado as $error_medio_pago_encabezado )
                <li>{{ $error_medio_pago_encabezado }}</li>
            @endforeach
        </ul>
    </div>
@endif

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
