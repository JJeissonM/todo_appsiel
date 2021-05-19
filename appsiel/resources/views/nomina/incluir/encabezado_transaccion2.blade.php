
<?php

use App\Core\Tercero;

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];

    $tiempo_a_liquidar = ['120'=>'Una Quincena (120 horas)','240'=>'Un mes (240 horas)','9999'=>'Órdenes de trabajo'];
    $color = 'red';

    if ( $encabezado_doc->estado == 'Activo' )
    {
        $color = 'green';
    }
?>
@include('core.dis_formatos.plantillas.estiloestandar2', [ 'vista' => 'imprimir', 'doc_encabezado' => $encabezado_doc ] )
<table class="encabezado" width="100%">
    <tr>
        <td width="70%" rowspan="2">
            <?php
                $image = getimagesize($url);
                $ancho = $image[0];            
                $alto = $image[1];   
			 
                $palto = (60*100)/$alto;
				$ancho = $ancho*$palto/100;
				echo '<img src="'.$url.'" width="'.$ancho.'" height="60" />';
           
			?>	
            </td>
        <td>Teléfono: {{ $empresa->telefono1 }}</td>
    </tr>
    <tr>
        <td>Email: <a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a></td>
    </tr>
</table>
<table class="info">
    <tr>
        <td width="55%">{{ $empresa->descripcion }}</td>
        <td width="45%" rowspan="2">
            <b style="font-size: 16px">Documento Nomina {{ $encabezado_doc->documento_app }}</b>
        </td>
    </tr>
    <tr>
        <td>
            <p>{{ config("configuracion.tipo_identificador") }}: {{ $empresa->numero_identificacion }} - {{ $empresa->digito_verificacion }}</p>
        </td>
    </tr>
</table>
<hr>
<table class="info">
    <tr>
        @php 
            $fecha = explode("-",$encabezado_doc->fecha) 
        @endphp
        <td><b>Fecha:</b></td>
        <td>{{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}</td>
        <td><b>Liquidación:</b></td>
        <td>{{ $tiempo_a_liquidar[ $encabezado_doc->tiempo_a_liquidar ] }}</td>
    </tr>
    <tr>
        <td colspan="4"><b>Detalle: </b> &nbsp; {{ $encabezado_doc->descripcion }}</td>
    </tr>
</table>
<hr>
<table class="info">
    <tr>
        <td>
            <b>Total Devengos: </b>
        </td>
        <td>
             &nbsp; ${{ number_format( $encabezado_doc->total_devengos, '0','.',',') }}
        </td>
        <td>
            <b>Total Deducciones: </b>
        </td>
        <td>
             &nbsp; ${{ number_format( $encabezado_doc->total_deducciones, '0','.',',') }}
        </td>
        <td>
            <b>Valor Neto: </b> 
        </td>
        <td>
            &nbsp; ${{ number_format( $encabezado_doc->total_devengos - $encabezado_doc->total_deducciones, '0','.',',') }}
        </td>
    </tr>
</table>