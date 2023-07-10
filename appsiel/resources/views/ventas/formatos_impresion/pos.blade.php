@extends('ventas_pos.formatos_impresion.plantilla_factura_default')

@section('columnas_encabezado')
    <?php
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where( 'id', $empresa->codigo_ciudad )->get()[0];
    ?>
    <td width="15%">
        <img src="{{ $url_img }}" width="120px;" />
    </td>
    <td>
        @include('ventas_pos.formatos_impresion.datos_encabezado_factura')
    </td>
@endsection