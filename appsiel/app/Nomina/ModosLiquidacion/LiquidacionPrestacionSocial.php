<?php

namespace App\Nomina\ModosLiquidacion;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\NomConcepto;
use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;

use App\Nomina\ModosLiquidacion\ModoLiquidacionPrestacion; // Facade

// Proceso (Subsistema)
class LiquidacionPrestacionSocial extends Model
{
    protected $prestacion;
    protected $empleado;
    protected $documento_nomina;

    public function __construct( $prestacion, NomContrato $empleado, NomDocEncabezado $documento_nomina )
    {
        $this->prestacion = $prestacion;
        $this->empleado = $empleado;
        $this->documento_nomina = $documento_nomina;
    }

    public function calcular($prestacion)
    {
        $fachada = new ModoLiquidacionPrestacion;
        return $fachada->calcular($prestacion, $this);
    }

    public function retirar( $prestacion, $registro )
    {
        $fachada = new ModoLiquidacionPrestacion;
        return $fachada->retirar( $prestacion, $registro );
    }
}
