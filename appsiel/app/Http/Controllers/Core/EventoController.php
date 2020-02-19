<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;

use App\Core\CoreEvento;


class EventoController extends Controller
{
    public function get_eventos()
    {
        //$eventos = CoreEvento::all()->toArray();

        /*$select_raw = 'CONCAT(core_eventos.fecha_inicio,"T",core_eventos.hora_inicio) AS start';
        $select_raw2 = 'CONCAT(core_eventos.fecha_fin,"T",core_eventos.hora_fin) AS end';
        */

        $select_raw = 'core_eventos.hora_inicio AS start';
        $select_raw2 = 'core_eventos.hora_fin AS end';

        $registros = CoreEvento::where('id','>',24)->select('core_eventos.descripcion AS title',DB::raw($select_raw),DB::raw($select_raw2),'core_eventos.color','core_eventos.dow')
                    ->get()
                    ->toArray();
        $cantidad = count($registros);
        for($i=0; $i < $cantidad; $i++)
        {
            $registros[$i]['dow'] = [$registros[$i]['dow']];
        }

        //echo $registros[0]['dow'];
        return response()->json($registros);
    }
}
