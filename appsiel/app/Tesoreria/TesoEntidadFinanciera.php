<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoEntidadFinanciera extends Model
{
    protected $table = 'teso_entidades_financieras';

    protected $fillable = ['id','descripcion','estado'];

    public $encabezado_tabla = ['Código','Descripción','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = TesoEntidadFinanciera::select('teso_entidades_financieras.id AS campo1','teso_entidades_financieras.descripcion AS campo2','teso_entidades_financieras.estado AS campo3','teso_entidades_financieras.id AS campo4')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
