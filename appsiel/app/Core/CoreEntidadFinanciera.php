<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class CoreEntidadFinanciera extends Model
{
    protected $table = 'core_entidades_financieras';

    protected $fillable = ['descripcion','estado'];

    public $encabezado_tabla = ['Código','Descripción','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = CoreEntidadFinanciera::select('core_entidades_financieras.id AS campo1','core_entidades_financieras.descripcion AS campo2','core_entidades_financieras.estado AS campo3','core_entidades_financieras.id AS campo4')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
