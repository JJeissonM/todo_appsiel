<?php 

namespace App\Inventarios\ValueObjects;

class FiltrosMovin
{
    
    // SIN TERMINAR


    private $fecha_ini;
    private $fecha_fin;
    private $bodega_id;
    private $inv_grupo_id;
    private $item_id;

    public function __construct( $fecha_ini, $fecha_fin, $bodega_id, $inv_grupo_id, $item_id )
    {
        $this->lista = [];
    }
}
