<?php 

namespace App\Inventarios\Services;

class ItemsMandatariosSerices
{

    public function build_reference( $datos, $registro )
    {
        if (isset($datos['referencia'])) {
            return $datos['referencia'];
        }

        $reference = '';

        if ( $registro->prefijo_referencia != null ) {
            $reference .= $registro->prefijo_referencia->codigo;
        }
        

        if ( $registro->tipo_prenda != null ) {
            $reference .= $registro->tipo_prenda->codigo;
        }
        

        if ( $registro->paleta_color != null ) {
            $reference .= $registro->paleta_color->codigo;
        }
        

        if ( $registro->tipo_material != null ) {
            $reference .= $registro->tipo_material->codigo;
        }
        
        $reference .= '-' . $registro->id;
        
        return $reference;
    }
}