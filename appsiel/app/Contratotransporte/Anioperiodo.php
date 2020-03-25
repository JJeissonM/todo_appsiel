<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Anioperiodo extends Model
{
    protected $table = 'cte_anioperiodos';
    protected $fillable = ['id', 'anio_id', 'inicio', 'fin', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['ID', 'Año', 'Período', 'Creado', 'Modificado', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Anioperiodo::leftJoin('cte_anios', 'cte_anios.id', '=', 'cte_anioperiodos.anio_id')
            ->select('cte_anios.anio', 'cte_anioperiodos.inicio', 'cte_anioperiodos.fin', 'cte_anioperiodos.id')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->anio . ' (' . $opcion->inicio . ' - ' . $opcion->fin . ')';
        }

        return $vec;
    }

    public static function consultar_registros2()
    {
        return Anioperiodo::leftJoin('cte_anios', 'cte_anios.id', '=', 'cte_anioperiodos.anio_id')
            ->select(
                'cte_anioperiodos.id AS campo1',
                'cte_anios.anio AS campo2',
                DB::raw('CONCAT(cte_anioperiodos.inicio," - ",cte_anioperiodos.fin) AS campo3'),
                'cte_anioperiodos.created_at AS campo4',
                'cte_anioperiodos.updated_at AS campo5',
                'cte_anioperiodos.id AS campo6'
            )
            ->orderBy('cte_anioperiodos.created_at', 'DESC')
            ->paginate(100);
    }
}
