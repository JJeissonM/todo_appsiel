<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class ContabPeriodoEjercicio extends Model
{
    protected $table='contab_periodos_ejercicio';

    protected $fillable = ['core_empresa_id','numero', 'descripcion','fecha_desde','fecha_hasta','estado','cerrado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empresa', 'Descripcion', 'Fecha desde', 'Fecha hasta', 'Cerrado', 'Estado'];

    public static function consultar_registros($nro_registros)
    {

        $select_raw1 = 'CONCAT(core_empresas.nombre1," ",core_empresas.otros_nombres," ",core_empresas.apellido1," ",core_empresas.apellido2," ",core_empresas.razon_social) AS campo1';

        $select_raw2 = 'IF(contab_periodos_ejercicio.cerrado=0,REPLACE(contab_periodos_ejercicio.cerrado,0,"No"),REPLACE(contab_periodos_ejercicio.cerrado,1,"Si")) AS campo5';

        $registros = ContabPeriodoEjercicio::leftJoin('core_empresas', 'core_empresas.id', '=', 'contab_periodos_ejercicio.core_empresa_id')
            ->where('contab_periodos_ejercicio.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                $select_raw1,
                'contab_periodos_ejercicio.descripcion AS campo2',
                'contab_periodos_ejercicio.fecha_desde AS campo3',
                'contab_periodos_ejercicio.fecha_hasta AS campo4',
                DB::raw($select_raw2),
                'contab_periodos_ejercicio.estado AS campo6',
                'contab_periodos_ejercicio.id AS campo7'
            )
            ->orderBy('contab_periodos_ejercicio.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }
}
