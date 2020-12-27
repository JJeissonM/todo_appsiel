<?php

namespace App\Sistema\Html;

use View;
use DB;

class TablaIngresoLineaRegistros
{
    // Variables que se enviarán a la vista
    public $datos;

    // Ubicación del archivo para dibujar la vista
    public $ruta_ubicacion = 'layouts.elementos.tabla_ingreso_lineas_registros';

    public function __construct( $datos )
    {
        $this->datos = $datos;
    }

    public function dibujar( )
    {
    	return View::make($this->ruta_ubicacion, [ 'datos' => $this->datos ]);
    }
}
