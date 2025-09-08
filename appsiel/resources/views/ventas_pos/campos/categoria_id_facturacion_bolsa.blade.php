<?php
    $string_categorias = '';
    $categoria_id_facturacion_bolsa = config('ventas_pos.categoria_id_facturacion_bolsa');
    if( is_array( $categoria_id_facturacion_bolsa ) )
    {
        $es_el_primero = true;
        foreach( $categoria_id_facturacion_bolsa AS $key => $value )
        {
            if ($es_el_primero) {
                $string_categorias = $value;
                $es_el_primero = false;
            }else{
                $string_categorias .=  ',' . $value;
            }
        }
    }
?>

<input type="hidden" name="categoria_id_facturacion_bolsa" id="categoria_id_facturacion_bolsa"
        value="{{ $string_categorias }}">