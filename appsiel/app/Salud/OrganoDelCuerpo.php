<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class OrganoDelCuerpo extends Model
{
    protected $table = 'salud_organos_del_cuerpo';
	protected $fillable = ['descripcion', 'detalle', 'organo_padre_id'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Detalle'];
    public static function consultar_registros($nro_registros)
    {

        $registros = OrganoDelCuerpo::select('salud_organos_del_cuerpo.descripcion AS campo1', 'salud_organos_del_cuerpo.detalle AS campo2', 'salud_organos_del_cuerpo.id AS campo3')
            ->orderBy('salud_organos_del_cuerpo.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }
    
    public static function opciones_campo_select()
    {

        $opciones = OrganoDelCuerpo::select('salud_organos_del_cuerpo.id','salud_organos_del_cuerpo.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
