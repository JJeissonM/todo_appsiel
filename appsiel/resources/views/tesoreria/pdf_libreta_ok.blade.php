<style>

    img {
        padding-left:30px;
    }

    table {
        width:100%;
    }

    table.encabezado{
        padding:5px;
        border: 1px solid;
    }

    table.banner{
        font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
        font-style: italic;
        font-size: larger;
        border: 1px solid;
    }

    table.contenido td {
        border: 1px solid;
    }

    th {
        background-color: #E0E0E0;
        border: 1px solid;
    }

    ul{
        padding:0px;
        margin:0px;
    }

    li{
        list-style-type: none;
    }

    span.etiqueta{
        font-weight: bold;
        display: inline-block;
        width: 100px;
        text-align:right;
    }

    .page-break {
        page-break-after: always;
    }
</style>

    <?php

        if ($registro==0) {
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

    <table style="height: 33%;">
        <tr>
            <td>
                @include('tesoreria.incluir.portada_libreta')
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <table style="height: 33%;">
        <tr>
            <td>
                @include('tesoreria.incluir.matricula',['etiqueta'=>'Copia ALUMNO'])
            </td>
            <td>
                @include('tesoreria.incluir.matricula',['etiqueta'=>'Copia COLEGIO'])
            </td>
        </tr>
    </table>

<?php 
    $fecha = explode("-",$fecha_inicio);

        //echo "fecha ".;
        $num_mes = $fecha[1];
        ?>
        <table style="height: 33%;">
        <?php
        for($i=0;$i<$cantidad_periodos;$i++){
            $mes = nombre_mes($num_mes);
            $num_mes++;
            if($num_mes>=13){
                $num_mes='01';
            }

            
            ?>
            <tr>
                <td>
                    @include('tesoreria.incluir.pension',['etiqueta'=>'Copia ALUMNO'])
                </td>
                <td>
                    @include('tesoreria.incluir.pension',['etiqueta'=>'Copia COLEGIO'])
                </td>
            </tr>
            <?php
        }
        ?>
        </table>

        <br/><br/>
        <table style="height: 33%;">
            <tr>
                <td>
                    <div style="border: 1px solid #ccc; border-radius: 6px;">
                        @include('tesoreria.incluir.pazysalvo')
                    </div>
                </td>
            </tr>
        </table>

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