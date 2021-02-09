<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Nomina\NovedadTnl;

class NovedadesTnlController extends Controller
{

    public function validar_fecha_otras_novedades( $fecha_inicial_nueva, $fecha_final_nueva, $contrato_id, $novedad_id )
    {
        $novedades_empleado = NovedadTnl::where('nom_contrato_id',$contrato_id)->get();
        foreach ($novedades_empleado as $novedad)
        {
            if ( $novedad_id == $novedad->id )
            {
                continue;
            }
            
            // Fecha inicial de la NUEVA NOVEDAD está entre las fechas de alguna NOVEDAD CREADA
            if ( ($fecha_inicial_nueva >= $novedad->fecha_inicial_tnl) && ($fecha_inicial_nueva <= $novedad->fecha_final_tnl) )
            {
                return 1;
            }

            // Fecha final de la NUEVA NOVEDAD está entre las fechas de alguna NOVEDAD CREADA
            if ( ($fecha_final_nueva >= $novedad->fecha_inicial_tnl) && ($fecha_final_nueva <= $novedad->fecha_final_tnl) )
            {
                return 1;
            }

            // Fecha inicial de alguna NOVEDAD CREADA está entre las fechas de la NUEVA NOVEDAD
            if ( ($novedad->fecha_inicial_tnl >= $fecha_inicial_nueva) && ($novedad->fecha_inicial_tnl <= $fecha_final_nueva) )
            {
                return 1;
            }

            // Fecha final de la NUEVA NOVEDAD está entre las fechas de alguna NOVEDAD CREADA
            if ( ($novedad->fecha_final_tnl >= $fecha_inicial_nueva) && ($novedad->fecha_final_tnl <= $fecha_final_nueva) )
            {
                return 1;
            }
        }

        return 0;
    }

    public function get_options_incapacidades_anteriores( $fecha_inicial_nueva, $fecha_final_nueva, $contrato_id, $novedad_id )
    {
        $registros = NovedadTnl::where( [
                                            [ 'tipo_novedad_tnl', '=', 'incapacidad'],
                                            [ 'nom_contrato_id', '=', $contrato_id ],
                                            [ 'id', '<>', $novedad_id ]
                                        ])
                                ->orderBy('fecha_final_tnl','DESC')
                                ->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo)
        {
            $opciones .= '<option value="'.$campo->id.'">'.$campo->concepto->descripcion . ' (' . $campo->observaciones . ') ( Inicio: ' . $campo->fecha_inicial_tnl . ') </option>';
        }
        return $opciones;
    }
}