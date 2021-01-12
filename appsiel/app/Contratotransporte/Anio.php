<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Anio extends Model
{
    protected $table = 'cte_anios';
    protected $fillable = ['id', 'anio', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Año', 'Creado', 'Modificado'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Anio::all();
        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->anio;
        }

        return $vec;
    }


    public static function consultar_registros2($nro_registros, $search)
    {
        return Anio::select(
            'cte_anios.anio AS campo1',
            'cte_anios.created_at AS campo2',
            'cte_anios.updated_at AS campo3',
            'cte_anios.id AS campo4'
        )->where("cte_anios.anio", "LIKE", "%$search%")
            ->orWhere("cte_anios.created_at", "LIKE", "%$search%")
            ->orWhere("cte_anios.updated_at", "LIKE", "%$search%")
            ->orderBy('cte_anios.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Anio::select(
            'cte_anios.anio AS AÑO',
            'cte_anios.created_at AS CREADO',
            'cte_anios.updated_at AS ACTUALIZADO'
        )->where("cte_anios.anio", "LIKE", "%$search%")
            ->orWhere("cte_anios.created_at", "LIKE", "%$search%")
            ->orWhere("cte_anios.updated_at", "LIKE", "%$search%")
            ->orderBy('cte_anios.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE AÑOS PARA MANTENIMIENTOS";
    }

    public function anioperiodos()
    {
        return $this->hasMany(Anioperiodo::class);
    }
}
