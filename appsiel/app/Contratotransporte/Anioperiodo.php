<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Anioperiodo extends Model
{
    protected $table = 'cte_anioperiodos';
    protected $fillable = ['id', 'anio_id', 'inicio', 'fin', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Año', 'Período', 'Creado', 'Modificado'];

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

    public static function consultar_registros2($nro_registros)
    {
        return Anioperiodo::leftJoin('cte_anios', 'cte_anios.id', '=', 'cte_anioperiodos.anio_id')
            ->select(
                'cte_anios.anio AS campo1',
                DB::raw('CONCAT(cte_anioperiodos.inicio," - ",cte_anioperiodos.fin) AS campo2'),
                'cte_anioperiodos.created_at AS campo3',
                'cte_anioperiodos.updated_at AS campo4',
                'cte_anioperiodos.id AS campo5'
            )
            ->orderBy('cte_anioperiodos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public function anio()
    {
        return $this->belongsTo(Anio::class);
    }
}
