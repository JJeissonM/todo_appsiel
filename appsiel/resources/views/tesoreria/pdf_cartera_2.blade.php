@extends('layouts.pdf')

@section('estilos_1')
<style type="text/css">

    table {
        border: solid 1px;
        border-collapse: separate !important;
        border-spacing: 0;
        /*margin: 30px;*/
        width: 100%;
        font-size: 16px;
        line-height: 1.5;
        /*height: 48%;*/
    }

    table td{
        border: solid 1px;
    }
</style>
@endsection 

@section('content')

    <table>
        <tr>
            <td colspan="2" align="center"><p style="font-size: 15px; font-weight: bold;">Estado de cuentas pendientes</p></td>
        </tr>
        <tr>
            <td>Concepto: {{ strtoupper($concepto) }}</td>
            <td>Mes: {{ nombre_mes($fecha_vencimiento) }}</td>
        </tr>
    </table>

    <table class="table table-bordered table-striped" style="font-size: 13px;">
        {{ Form::bsTableHeader( [ 'Estudiante (Cód. Matrícula)', 'Curso', 'Vlr. a pagar', 'Vlr. pagado', 'Saldo pendiente' ] ) }}
        <tbody>
            <?php 
                $total_pagar=0;
                $total_pagado=0;
                $total_pendiente=0; 
            ?>
            @foreach($carteras as $cartera)
                <tr>
                    <td> {{ $cartera->nombre_completo }} ({{ $cartera->codigo_matricula }}) </td>
                    <td> {{ $cartera->nom_curso }} </td>
                    <td class="text-right">
                        {{ '$'.number_format($cartera->valor_cartera, 0, ',', '.') }}
                    </td>
                    <td class="text-right">
                        {{ '$'.number_format($cartera->valor_pagado, 0, ',', '.') }}
                    </td>
                    <td class="text-right">
                        <?php
                            $pendiente = $cartera->valor_cartera - $cartera->valor_pagado;
                        ?>
                        {{ '$'.number_format($pendiente, 0, ',', '.') }} 
                    </td>
                </tr>
                <?php 
                    $total_pagar += $cartera->valor_cartera;
                    $total_pagado += $cartera->valor_pagado;
                    $total_pendiente += $pendiente; 
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>&nbsp;</td>
                <td align="right"> <b>TOTALES</b></td>
                <td class="text-right"> 
                    {{'$'.number_format($total_pagar, 0, ',', '.')}}
                </td>
                <td class="text-right"> 
                    {{'$'.number_format($total_pagado, 0, ',', '.')}}
                </td>
                <td class="text-right"> 
                    {{'$'.number_format($total_pendiente, 0, ',', '.')}}
                </td>
            </tr>
        </tfoot>
    </table>
    <div style="font-size: 11px; text-align: right; width: 100%;">
                        Generado:  {{ date('Y-m-d, h:m:s') }}
                    </div>
@endsection

<?php
    function nombre_mes($num_mes){
        switch($num_mes){
            case '01':
                $mes="Enero";
                break;
            case '02':
                $mes="Febrero";
                break;
            case '03':
                $mes="Marzo";
                break;
            case '04':
                $mes="Abril";
                break;
            case '05':
                $mes="Mayo";
                break;
            case '06':
                $mes="Junio";
                break;
            case '07':
                $mes="Julio";
                break;
            case '08':
                $mes="Agosto";
                break;
            case '09':
                $mes="Septiembre";
                break;
            case '10':
                $mes="Octubre";
                break;
            case '11':
                $mes="Noviembre";
                break;
            case '12':
                $mes="Diciembre";
                break;
            default:
                $mes="----------";
                break;
        }
        return $mes;
    }
?>