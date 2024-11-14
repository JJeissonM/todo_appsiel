
@include('calificaciones.boletines.formatos.banner_colegio_con_escudo')
<hr>


<table class="info">
    <tr>
        <td width="55%"><b style="font-size: 16px">{{ $empresa->descripcion }}</b></td>
        <td width="45%" colspan="">
            <b style="font-size: 16px">{{ $doc_encabezado->tipo_documento_app->descripcion }} N. {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</b>
        </td>
    </tr>
    <tr>
        <td>Dirección: {{ $empresa->direccion1 }}</td>
        <td colspan="">
            <p>{{ config("configuracion.tipo_identificador") }}: {{ $empresa->numero_identificacion }} - {{ $empresa->digito_verificacion }}</p>
        </td>
    </tr>
    <tr>
        <td>Telefono: {{ $empresa->telefono1 }}</td>
        <td>Mail: {{ $empresa->email }}</td>
    </tr>
</table>

<hr>
<table class="info">
    <tr>
        <td width="12%"><b>Cliente:</b></td>
        <td width="43%">{{ $doc_encabezado->tercero_nombre_completo }}</td>
        <td width="20%"><b>Fecha:</b></td>
        <td width="25%">
            <?php
                $fecha = date_create($doc_encabezado->fecha);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}
        </td>
    </tr>
    <tr>
        <td><b>CC:</b></td>
        <td>{{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }} </td>
        <td><b>Teléfono:</b></td>
        <td>{{ $doc_encabezado->telefono1 }}</td>
    </tr>
</table>

@include('matriculas.facturas.datos_estudiante_recaudo')