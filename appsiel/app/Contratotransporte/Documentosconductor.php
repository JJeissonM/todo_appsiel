<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Documentosconductor extends Model
{
    protected $table = 'cte_documentosconductors';
    protected $fillable = ['id', 'conductor_id', 'licencia', 'documento', 'recurso', 'nro_documento', 'vigencia_inicio', 'vigencia_fin', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['Nro. Documento', 'Documento', 'Inicio Vigencia', 'Vence', 'Documento Conductor', 'Conductor', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}'; 

    public static function consultar_registros2()
    {
        return Documentosconductor::leftJoin('cte_conductors', 'cte_conductors.id', '=', 'cte_documentosconductors.conductor_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
            ->select(
                'cte_documentosconductors.nro_documento AS campo1',
                'cte_documentosconductors.documento AS campo2',
                'cte_documentosconductors.vigencia_inicio AS campo3',
                'cte_documentosconductors.vigencia_fin AS campo4',
                'core_terceros.numero_identificacion AS campo5',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo6'),
                'cte_documentosconductors.id AS campo7'
            )
            ->orderBy('cte_documentosconductors.created_at', 'DESC')
            ->paginate(100);
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class);
    }
}
