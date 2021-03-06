<?php

namespace App\Nomina\ModosLiquidacion;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\NomConcepto;
use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;

use App\Nomina\ModosLiquidacion\ModoLiquidacion; // Facade

// Proceso (Subsistema)
class LiquidacionConcepto extends Model
{
    protected $concepto;
    protected $empleado;
    protected $documento_nomina;
    protected $fecha_final_promedios;

    public function __construct( $concepto_id, NomContrato $empleado, NomDocEncabezado $documento_nomina, $fecha_final_promedios = null )
    {
        $this->concepto = NomConcepto::find($concepto_id);
        $this->empleado = $empleado;
        $this->documento_nomina = $documento_nomina;
        $this->fecha_final_promedios= $fecha_final_promedios;
    }

    public function calcular($modo_liquidacion_id)
    {
        $fachada = new ModoLiquidacion;
        return $fachada->calcular($modo_liquidacion_id, $this);
    }

    public function retirar( $modo_liquidacion_id, $registro )
    {
        $fachada = new ModoLiquidacion;
        return $fachada->retirar( $modo_liquidacion_id, $registro );
    }
}
