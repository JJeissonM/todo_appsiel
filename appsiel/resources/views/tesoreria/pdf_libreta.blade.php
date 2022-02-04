<!DOCTYPE html>
<html>
<head>
    <title>Libreta de pago</title>
    <style>
        
        @page{
            size: 612pt 320pt;
            margin: 5px;
        }

        table {
            width:100%;
        }

        .page-break {
            page-break-after: always;
        }
    </style>    
</head>
<body>
    <?php

        if ($registro == 0) {
            $nombre_completo = ' ';
            $nom_curso = ' ';
            $codigo_matricula = ' ';
            $fecha_inicio = '01-02-2018';
            $valor_matricula = ' ';
            $valor_matricula_letras= ' ';
            $valor_pension_mensual = ' ';
            $valor_pension_mensual_letras= ' ';
            $cantidad_periodos=10;
        }else{
            $nombre_completo = $registro['campo1'];
            $nom_curso = $registro['campo2'];
            $codigo_matricula = $registro['campo3'];
            $fecha_inicio = $registro['campo4'];

            $valor_matricula = '$'.number_format($registro['campo5'], 0, ',', '.');
            $valor_matricula_letras= '('.NumerosEnLetras::convertir($registro['campo5'],'pesos',false).')';

            $valor_pension_mensual = '$'.number_format($registro['campo8'], 0, ',', '.');
            $valor_pension_mensual_letras= '('.NumerosEnLetras::convertir($registro['campo8'],'pesos',false).')';
            $cantidad_periodos=$registro['campo7'];
        }

        $entidad_financiera = $cuenta['entidad_financiera'];
        $tipo_cuenta = $cuenta['tipo_cuenta'];
        $numero_cuenta = $cuenta['descripcion'];
    ?>


    <!-- PORTADA -->
    <table style="margin: 30px 15px 0px 35px;">
        <tr>
            <td>
                @include('tesoreria.incluir.portada_libreta')
            </td>
        </tr>
    </table>
    <div class="page-break"></div>

    <?php
        $ancho_columna_1 = config('tesoreria.ancho_columna_1_libretas_pagos');
        $font_size = config('tesoreria.payment_book_font_size');
        //$ancho_columna_2 = 100 - $ancho_columna_1;
    ?>
    <!-- RECIBO DE MATRICULA -->
    <table style="margin: 30px 15px 0px 35px; font-size:{{$font_size}}px">
        <tr>
            <td style="width: {{$ancho_columna_1}}%;">
                @include('tesoreria.incluir.matricula',['etiqueta'=>'Copia Estudiante'])
            </td>
            <td>
                @include('tesoreria.incluir.matricula',['etiqueta'=>'Copia BANCO'])
            </td>
        </tr>
    </table>
    <div class="page-break"></div>


    <!-- RECIBO DE PENSIÓN -->

    <?php 
        $fecha = explode("-",$fecha_inicio);
        $num_mes = $fecha[1];
    ?>
        
    @for($i=0;$i<$cantidad_periodos;$i++)
        <?php
            $mes = nombre_mes($num_mes);
            $num_mes++;
            if($num_mes >= 13){
                $num_mes='01';
            }
        ?>

        <table style="margin: 30px 15px 0px 35px; font-size:{{$font_size}}px">
            <tr>
                <td style="width: {{$ancho_columna_1}}%;">
                    @include('tesoreria.incluir.pension',['etiqueta'=>'Copia Estudiante'])
                </td>
                <td>
                    @include('tesoreria.incluir.pension',['etiqueta'=>'Copia BANCO'])
                </td>
            </tr>
        </table>
        <div class="page-break"></div>
    @endfor
        
        <!-- PAZ Y SALVO -->
        <table style="margin: 30px 15px 0px 35px; font-size:1em;">
            <tr>
                <td style="width: {{$ancho_columna_1}}%;">
                    @include('tesoreria.incluir.pazysalvo')
                </td>
                <td>
                    @include('tesoreria.incluir.pazysalvo')
                </td>
            </tr>
        </table>
    </body>
</html>
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