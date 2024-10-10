<?php
    $string_cactegorias = '';
    $categoria_id_platillos_con_contornos = config('inventarios.categoria_id_platillos_con_contornos');
    if( is_array( $categoria_id_platillos_con_contornos ) )
    {
        $es_el_primero = true;
        foreach( $categoria_id_platillos_con_contornos AS $key => $value )
        {
            if ($es_el_primero) {
                $string_cactegorias = $value;
                $es_el_primero = false;
            }else{
                $string_cactegorias .=  ',' . $value;
            }
        }
    }
?>

<input type="hidden" name="categoria_id_platillos_con_contornos" id="categoria_id_platillos_con_contornos"
        value="{{ $string_cactegorias }}">