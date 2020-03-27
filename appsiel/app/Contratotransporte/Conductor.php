<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Conductor extends Model
{
    protected $table = 'cte_conductors';
    protected $fillable = ['id', 'tercero_id', 'estado', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['Tipo Documento', 'Número Documento', 'Conductor', 'Estado', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Conductor::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
            ->select('cte_conductors.id', 'core_terceros.descripcion', 'core_terceros.numero_identificacion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->numero_identificacion . ' ' . $opcion->descripcion;
        }

        return $vec;
    }

    public static function consultar_registros2()
    {
        return Conductor::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                'core_tipos_docs_id.abreviatura AS campo1',
                'core_terceros.numero_identificacion AS campo2',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'cte_conductors.estado AS campo4',
                'cte_conductors.id AS campo5'
            )
            ->orderBy('cte_conductors.created_at', 'DESC')
            ->paginate(100);
    }

    public function documentosconductors()
    {
        return $this->hasMany(Documentosconductor::class);
    }
}
