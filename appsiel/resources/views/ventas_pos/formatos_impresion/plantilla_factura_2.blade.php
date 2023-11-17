@extends('ventas_pos.formatos_impresion.plantilla_factura_default')

@section('estilos_adicionales')
    #tabla_productos_facturados,#tabla_totales
    {
        border-collapse: collapse;
    }

    #tabla_productos_facturados tbody tr td
    {
        border: 1px solid gray;
    }

    #tabla_productos_facturados thead tr th
    {
        border: 1px solid gray;
        background-color: #eaeaea;
    }

    #tabla_totales td
    {
        border: 1px solid gray;
    }

    #tr_total_factura{
        background-color: #eaeaea;
    }

    #tr_total_propina{
        background-color: #eaeaea;
    }
@endsection

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