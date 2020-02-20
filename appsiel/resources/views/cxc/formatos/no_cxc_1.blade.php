<link rel="stylesheet" type="text/css" href="{{asset('assets/css/estilos_formatos.css')}}" media="screen" />

<?php
    $tabla_doc_encabezado = 'cxc_doc_encabezados';
    $tabla_doc_registros = 'cxc_doc_registros';
    $llave_foranea_doc_encabezado = 'cxc_doc_encabezado_id';

    $doc_encabezado = DB::table($tabla_doc_encabezado)
                ->where('core_empresa_id','=',$empresa_id)
                ->where('core_tipo_doc_app_id','=',$core_tipo_doc_app_id)
                ->where('consecutivo','=',$consecutivo)
                ->get()[0];

    $tipo_doc_app = DB::table('core_tipos_docs_apps')
                ->where('id','=',$doc_encabezado->core_tipo_doc_app_id)
                ->get()[0];

    $tercero = DB::table('core_terceros')
                ->where('id','=',$doc_encabezado->core_tercero_id)
                ->get()[0];
    
    $doc_registros = DB::table($tabla_doc_registros)
                ->where($llave_foranea_doc_encabezado,'=',$doc_encabezado->id)
                ->get();

    $empresa = DB::table('core_empresas')
                ->where('id','=',$doc_encabezado->core_empresa_id)
                ->get()[0];

    $ciudad = DB::table('core_ciudades')
                ->where('id','=',$empresa->codigo_ciudad)
                ->value('descripcion');

    $propiedad = DB::table('ph_propiedades')
                ->where('id','=',$doc_encabezado->ph_propiedad_id)
                ->get()[0];

    $cuenta = DB::table('teso_cuentas_bancarias')
                ->where('por_defecto','=','Si')
                ->get()[0];

    $cuenta = DB::table('teso_cuentas_bancarias')->leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('teso_cuentas_bancarias.por_defecto','Si')
                            ->select('teso_cuentas_bancarias.tipo_cuenta','teso_cuentas_bancarias.descripcion','teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get()[0];

    $entidad_financiera = $cuenta->entidad_financiera;
    $tipo_cuenta = $cuenta->tipo_cuenta;
    $numero_cuenta = $cuenta->descripcion;
?>

<table width="100%">
    <tr>
        <td width="60%" style="margin-left: 10px;">
            @include('core.dis_formatos.plantillas.banner_logo_datos_empresa')</td>
        <td>
            <div align="center" style="border: solid 1px; margin: 15px; font-size: 1.1em">
                <b>{{ $tipo_doc_app->descripcion }}</b>
                <br/>
                <b>{{ $tipo_doc_app->prefijo." ".$doc_encabezado->consecutivo }}<b>
            </div>                    
        </td>
    </tr>
</table>

<table class="con_borde">
    <tr>
        <td>
            @php $fecha=explode("-",$doc_encabezado->fecha) @endphp
            <b>Ciudad y Fecha: </b> &nbsp; {{ $ciudad }}, {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
        </td>
        <td>
            @php $fecha=explode("-",$doc_encabezado->fecha_vencimiento) @endphp
            <b>Pagar hasta: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Nombre: </b> {{ $tercero->razon_social.$tercero->nombre1." ".$tercero->otros_nombres." ".$tercero->apellido1." ".$tercero->apellido2 }}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>{{ $propiedad->tipo_propiedad }}: </b> {{ $propiedad->nomenclatura }}
        </td>
    </tr>
</table>


<table class="tabla_registros">
    <tr class="encabezado">
        <td>
           Concepto
        </td>
        <td>
           Valor
        </td>
    </tr>
        <?php 
            $total=0; 
            $i=1;
        ?>
        @foreach ($doc_registros as $registro)

            <tr class="fila-<?php echo $i; ?>">
                <td>
                   {{ $registro->descripcion }}
                </td>
                <td>
                   ${{ number_format($registro->valor_unitario, 0, ',', '.') }}
                </td>
            </tr>
            <?php
                $i++;
                if ($i==3) {
                    $i=1;
                }
                $total+=$registro->valor_unitario;
            ?>
        @endforeach                  
            <?php 
                $saldo_pendiente = DB::table('cxc_movimientos')
                        ->where('fecha','<>',$doc_encabezado->fecha)
                        ->where('ph_propiedad_id','=',$doc_encabezado->ph_propiedad_id)
                        ->where('estado','=','Vencido')
                        ->sum('saldo_pendiente');

                if ($saldo_pendiente>0) {
            ?>
                <tr class="fila-<?php echo $i; ?>">
                    <td>
                       Saldo anterior pendiente por pagar
                    </td>
                    <td>
                       ${{ number_format($saldo_pendiente, 0, ',', '.') }}
                    </td>
                </tr>
            <?php
                    $total+=$saldo_pendiente;
                } 
            ?> 
        <tr>
            <td>
               <b>TOTAL A PAGAR</b>
            </td>
            <td>
               <b>${{ number_format($total, 0, ',', '.') }}</b>
            </td>
        </tr>
        <tr>
            <td colspan="2">
               Son <?php echo NumerosEnLetras::convertir($total,'pesos',false); ?>
            </td>
        </tr>
</table>
<table class="con_borde" width="100%">
    <tr>
        <td width="50%">
            <br/>
        </td>
        <td>
            RECIBI CONFORME:
            <br/>
            _____________________________________
            <br/>
            C.C.:
        </td>
    </tr>
</table>

<div style="text-align: justify;">
    Consigne a la cuenta {{ $tipo_cuenta }} {{ $entidad_financiera }} No. {{ $numero_cuenta }} o en la administración ubicada en el lobby.<br/>
    Horarios de atención: lunes, jueves y viernes 3-5 p.m. Cel. {{ $empresa->telefono1 }} {{ $empresa->telefono2 }}.<br/>
     Enviar comprombante de pago al correo <a href="mailto:{{ $empresa-> email }}">{{ $empresa-> email }}</a> para la leaboración del recibo de caja. <br/>
    Pasada la fecha de vencimiento, se cobrará el 4% por interes de mora.
</div>