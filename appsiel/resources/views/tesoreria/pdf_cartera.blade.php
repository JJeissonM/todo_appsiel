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
    <?php
        //$url = config('configuracion.url_instancia_cliente').'/storage/app/escudos/escudo_'.$colegio->id.'.jpg?'.rand(1,1000);

        $url = '../storage/app/logos_empresas/'.$empresa->imagen;
        
    ?>
    <table class="banner" >
        <tr>
            <td width="250px" align="center">
                <img src="{{ asset($url) }}" width="160px" height="160px" />
            </td>

            <td align="center">
                <b>{{ $colegio->descripcion }}</b><br/>
                <b style="padding-top: -10px;">{{ $colegio->slogan }}</b><br/>
                Resolución No. {{ $colegio->resolucion }}<br/>
                {{ $colegio->direccion }}<br/>
                Teléfonos: {{ $colegio->telefonos }}<br/><br/>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center"><p style="font-size: 15px; font-weight: bold;">Estado de cuentas pendientes</p></td>
        </tr>
        <tr>
            <td>Concepto: {{ strtoupper($concepto) }}</td>
            <td>Mes: {{ nombre_mes($id) }}</td>
        </tr>
    </table>

    <table class="table table-bordered table-striped" style="font-size: 13px;">
        {{ Form::bsTableHeader( [ 'Doc. Identidad', 'Estudiante', 'Vlr. a pagar', 'Vlr. pagado', 'Saldo pendiente' ] ) }}
        <tbody>
            <?php 
                $total_pagado=0;
                $total_pendiente=0; 
            ?>
            @foreach($carteras as $cartera)
                <tr>
                    <td> {{ $cartera->doc_identidad }} </td>
                    <td> {{ $cartera->nombre_completo }} </td>
                    <td align="center">
                        {{ '$'.number_format($cartera->valor_cartera, 0, ',', '.') }}
                    </td>
                    <td align="center">
                        {{ '$'.number_format($cartera->valor_pagado, 0, ',', '.') }}
                    </td>
                    <td align="center">
                        <?php
                            $pendiente = $cartera->valor_cartera - $cartera->valor_pagado;
                        ?>
                        {{ '$'.number_format($pendiente, 0, ',', '.') }} 
                    </td>
                </tr>
                <?php 
                    $total_pagado = $total_pagado + $cartera->valor_pagado;
                    $total_pendiente = $total_pendiente + $pendiente; 
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td align="right"> <b>TOTALES</b></td>
                <td align="center"> 
                    {{'$'.number_format($total_pagado, 0, ',', '.')}}
                </td>
                <td align="center"> 
                    {{'$'.number_format($total_pendiente, 0, ',', '.')}}
                </td>
            </tr>
        </tfoot>
    </table>
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