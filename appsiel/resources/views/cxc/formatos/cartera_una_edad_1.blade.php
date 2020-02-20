<link rel="stylesheet" type="text/css" href="{{asset('assets/css/estilos_formatos.css')}}" media="screen" />

<?php

    $empresa = DB::table('core_empresas')
                ->where('id','=',$empresa_id)
                ->get()[0];

?>

<table width="100%" border="1px">
    <tr>
        <td width="60%" style="margin-left: 10px;">
            @include('core.dis_formatos.plantillas.banner_logo_datos_empresa')
        </td>
        <td>
            <h3>Estado de cuentas pendientes</h2>
            Cartera x Edades<br/>
            Fecha: {{ date('Y-m-d') }}
        </td>
    </tr>
</table>

<table width="100%" class="tabla_registros">
    <tr class="encabezado">
        <td>
            Tercero
        </td>
        <td>
            Documento
        </td>
        <td>
            Fecha
        </td>
        <td>
            Días vencidos
        </td>
        <td>
            Vlr. {{ $min }} a {{ $max }} días
        </td>
    </tr>

    <?php
        $total = 0;
        $i=1;
        $primer_id_tercero = null;
        foreach ($cartera as $registro) {
            $tercero = DB::table('core_terceros')
                ->where('id','=',$registro['core_tercero_id'])
                ->get()[0];

            if ($primer_id_tercero==null) {
                $primer_id_tercero = $registro['core_tercero_id'];
                $nombre = $tercero->razon_social.$tercero->nombre1." ".$tercero->otros_nombres." ".$tercero->apellido1." ".$tercero->apellido2;
            }else{
                if ($primer_id_tercero == $registro['core_tercero_id']) {
                    $nombre = '';
                }else{
                    $primer_id_tercero = $registro['core_tercero_id'];
                    $nombre = $tercero->razon_social.$tercero->nombre1." ".$tercero->otros_nombres." ".$tercero->apellido1." ".$tercero->apellido2;
                }
            }
            
            $tipo_doc_app = DB::table('core_tipos_docs_apps')
                ->where('id','=',$registro['core_tipo_doc_app_id'])
                ->get()[0];

            echo '<tr  class="fila-'.$i.'">
                    <td>
                        '.$nombre.'
                    </td>
                    <td>
                        '.$tipo_doc_app->prefijo." ".$registro['consecutivo'].'
                    </td>
                    <td>
                        '.$registro['fecha'].'
                    </td>
                    <td>
                        '.$registro['edad'].'
                    </td>
                    <td>
                        $'.number_format($registro['saldo_pendiente'], 0, ',', '.').'
                    </td>
                </tr>';
            $total+=$registro['saldo_pendiente'];
            $i++;
            if ($i==3) {
                $i=1;
            }
        }
          //print_r($cartera);  
    ?>
    <tr>
        <td colspan="4">
            <b>TOTAL CARTERA</b>
        </td>
        <td>
            <b>${{ number_format($total, 0, ',', '.') }}</b>
        </td>
    </tr>
</table>

<?php

    //print_r(array_count_values($cartera,));
?>