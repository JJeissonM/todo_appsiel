<?php

namespace App\Core; 

use Illuminate\Database\Eloquent\Model;

use DB;

class CoreEvento extends Model
{
    //protected $table = 'core_eventos'; 

    protected $fillable = ['descripcion','fecha_inicio','hora_inicio','fecha_fin','hora_fin','color','dow'];

    public $encabezado_tabla = ['Descripción','Inicia','Termina','Color','Día semana','Acción'];

    public static function consultar_registros()
    {
    	$select_raw = 'CONCAT(core_eventos.fecha_inicio," ",core_eventos.hora_inicio) AS campo2';
        $select_raw2 = 'CONCAT(core_eventos.fecha_fin," ",core_eventos.hora_fin) AS campo3';

        $registros = CoreEvento::select('core_eventos.descripcion AS campo1',DB::raw($select_raw),DB::raw($select_raw2),'core_eventos.color AS campo4','core_eventos.dow AS campo5','core_eventos.id AS campo6')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
