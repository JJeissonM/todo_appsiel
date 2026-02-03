<?php

namespace App\Nomina\ValueObjects;

use Input;
use DB;
use PDF;
use Auth;
use View;

use App\Sistema\Modelo;
use App\Nomina\NomDocRegistro;

class LapsoNomina
{
    public $fecha_inicial, $fecha_final;

    public function __construct( $fecha_final_mes )
    {
        $array_fecha = explode('-', $fecha_final_mes);

        $dia_inicio = '01';

        $dia_fin = '30';
        // Mes de febrero
        if ($array_fecha[1] == '02')
        {
            $dia_fin = '28';
        }

        $this->fecha_inicial = $array_fecha[0] . '-' . $array_fecha[1] . '-' . $dia_inicio;
        $this->fecha_final = $array_fecha[0] . '-' . $array_fecha[1] . '-' . $dia_fin;
    }

    public function get_empleados_con_movimiento()
    {
        return NomDocRegistro::whereBetween(
                                        'fecha',
                                        [ $this->fecha_inicial, $this->fecha_final ]
                                    )
                                ->whereHas('contrato', function ($query) {
                                    $query->where('excluir_documentos_nomina_electronica', false);
                                })
                                ->distinct('nom_contrato_id')
                                ->get()
                                ->unique('nom_contrato_id')
                                ->values()
                                ->all();
    }
}
