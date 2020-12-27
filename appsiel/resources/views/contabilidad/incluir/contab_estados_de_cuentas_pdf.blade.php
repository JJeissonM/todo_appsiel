<?php
    $propiedad = App\PropiedadHorizontal\Propiedad::where('id',$movimiento_cuenta[0]['codigo_referencia_tercero'])->where('core_empresa_id',Auth::user()->empresa_id)->get()[0];

    $elaboro = Auth::user()->email;
?>
<div class="table-responsive">
    <table style="font-size: 15px; border: 1px solid; border-collapse: collapse;" width="100%">
        <tr>
            <td width="50%" style="border: solid 1px black; padding-top: -20px;">
                <div>
                    @include('core.dis_formatos.plantillas.banner_logo_datos_empresa')
                </div>
            </td>
            <td style="border: solid 1px black; padding-top: -20px;">
                <div style="vertical-align: center;">
                    <b style="font-size: 1.4em; text-align: center; display: block;">
                        Estado de cuentas
                    </b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border: solid 1px black;">
                @php $fecha = explode("-", date('Y-m-d') ) @endphp
                <b>Fecha generación: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border: solid 1px black;">
                <b style="width: 100%;text-align: center;">Residente </b>
                <br/>
                <b>Nombre: </b>{{ $propiedad->nombre_arrendatario }}
                <br/>
                <b>Teléfono: </b>{{ $propiedad->telefono_arrendatario }}
                <br/>
                <b>Email: </b>{{ $propiedad->email_arrendatario }}
            </td>
        </tr>
        <tr>
            <td style="border: solid 1px black;">
                <b>{{ $propiedad->tipo_propiedad }}: </b> {{ $propiedad->nomenclatura }}
            </td>
            <td style="border: solid 1px black;">
                <b>Cód. inmueble: </b> {{ $propiedad->codigo }}
            </td>
        </tr>
    </table>
</div>

<?php
    $total_debito = 0;
    $total_credito = 0;
    $saldo = 0;
    $j = 0;
    $i = 0;

    $tabla2 = '<table style="font-size: 14px; border: 1px solid; border-collapse: collapse;" border="1" width="100%">
                    <tr style="background: #ccc; font-weight: bold; text-align: center;">
                        <td>Fecha</td>
                        <td>Documento</td>
                        <td>Detalle</td>
                        <td>Cartera</td>
                        <td>Abono</td>
                        <td>Saldo</td>
                    </tr>';
    $tabla2.='';
    $j++;
    for ($i=0; $i < count($movimiento_cuenta) ; $i++) {           
        $debito = $movimiento_cuenta[$i]['debito'];
        $credito = $movimiento_cuenta[$i]['credito'];

        $saldo = $saldo_inicial + $debito + $credito;

        $tabla2.='<tr>
                        <td>
                           '.$movimiento_cuenta[$i]['fecha'].'
                        </td>
                        <td>
                           '.$movimiento_cuenta[$i]['documento'].'
                        </td>
                        <td>
                           '.$movimiento_cuenta[$i]['detalle_operacion'].'
                        </td>
                        <td>
                           '.number_format($debito, 0, ',', '.').'
                        </td>
                        <td>
                           '.number_format($credito * -1, 0, ',', '.').'
                        </td>
                        <td>
                           '.number_format( $saldo , 0, ',', '.').'
                        </td>
                    </tr>';

        $saldo_inicial = $saldo;
        $j++;
        if ($j==3) {
            $j=1;
        }
        $total_debito+=$debito;
        $total_credito+=$credito;

        if ( $saldo == 0) {
            $tabla2 = '<table style="font-size: 14px; border: 1px solid; border-collapse: collapse;" border="1" width="100%">
                    <tr style="background: #ccc; font-weight: bold; text-align: center;">
                        <td>Fecha</td>
                        <td>Documento</td>
                        <td>Detalle</td>
                        <td>Cartera</td>
                        <td>Abono</td>
                        <td>Saldo</td>
                    </tr>';
        }
    }

    $tabla2.='<tr  class="fila-'.$j.'" >
                        <td colspan="3">
                           &nbsp;
                        </td>
                        <td>
                           '.number_format($total_debito, 0, ',', '.').'
                        </td>
                        <td>
                           '.number_format($total_credito, 0, ',', '.').'
                        </td>
                        <td>
                           '.number_format($saldo, 0, ',', '.').'
                        </td>
                    </tr>';
    $tabla2.='</table>';

    echo $tabla2;
?>

<br><br>
<table width="100%" style="margin-top: 3px;">
    <tr>
        <td width="15%"> </td>
        <td width="30%"> _______________________ </td>
        <td width="10%"> </td>
        <td width="30%"> _______________________ </td>
        <td width="15%"> </td>
    </tr>
    <tr>
        <td width="15%"> </td>
        <td width="30%"> Impreso por: {{ explode("@",$elaboro)[0] }} </td>
        <td width="10%"> </td>
        <td width="30%"> &nbsp; </td>
        <td width="15%"> </td>
    </tr>
</table>