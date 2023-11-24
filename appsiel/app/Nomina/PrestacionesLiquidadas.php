<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PrestacionesLiquidadas extends Model
{
    protected $table = 'nom_prestaciones_liquidadas';
	
	protected $fillable = ['nom_doc_encabezado_id', 'nom_contrato_id', 'fecha_final_promedios', 'prestaciones_liquidadas', 'datos_liquidacion'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Documento', 'Empleado', 'Fecha final promedios'];

	public $urls_acciones = '{"show":"nom_prestaciones_liquidadas_show/id_fila"}';

	public static function consultar_registros($nro_registros, $search)
	{
	    return PrestacionesLiquidadas::leftJoin('nom_doc_encabezados','nom_doc_encabezados.id','=','nom_prestaciones_liquidadas.nom_doc_encabezado_id')
	    							->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'nom_doc_encabezados.core_tipo_doc_app_id')
            						->leftJoin('nom_contratos','nom_contratos.id','=','nom_prestaciones_liquidadas.nom_contrato_id')
	    							->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
	    							->select(
	    										DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",nom_doc_encabezados.consecutivo, " - ",nom_doc_encabezados.descripcion) AS campo1'),
	    										'core_terceros.descripcion AS campo2',
	    										'nom_prestaciones_liquidadas.fecha_final_promedios AS campo3',
	    										'nom_prestaciones_liquidadas.id AS campo4')
						            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
						            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
						            ->orWhere("nom_prestaciones_liquidadas.fecha_final_promedios", "LIKE", "%$search%")
						            ->orWhere("nom_prestaciones_liquidadas.prestaciones_liquidadas", "LIKE", "%$search%")
	    							->orderBy( 'nom_doc_encabezados.fecha', 'DESC')
	    							->paginate($nro_registros);

	}
	public static function sqlString($search)
	{
	    $string = PrestacionesLiquidadas::leftJoin('nom_doc_encabezados','nom_doc_encabezados.id','=','nom_prestaciones_liquidadas.nom_doc_encabezado_id')
	    							->leftJoin('nom_contratos','nom_contratos.id','=','nom_prestaciones_liquidadas.nom_contrato_id')
	    							->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
	    							->select(
	    										'nom_doc_encabezados.descripcion AS DOCUMENTO_NOMINA',
	    										'core_terceros.descripcion AS EMPLEADO',
	    										'core_terceros.numero_identificacion AS NUMERO_IDENTIFICACION',
	    										'nom_prestaciones_liquidadas.fecha_final_promedios AS FECHA_FINAL_PROMEDIOS',
	    										'nom_prestaciones_liquidadas.prestaciones_liquidadas AS PRESTACIONES',
	    										'nom_prestaciones_liquidadas.id AS ID')
	    							->orderBy( 'nom_doc_encabezados.fecha', 'DESC')
	    							->toSql();
	    return str_replace('?', '""%' . $search . '%""', $string);
	}
    
    public static function tituloExport()
    {
        return "LISTADO DE PRESTACIONE LIQUIDADAS";
    }

	public static function opciones_campo_select()
    {
        $opciones = PrestacionesLiquidadas::where('nom_prestaciones_liquidadas.estado','Activo')
                    ->select('nom_prestaciones_liquidadas.id','nom_prestaciones_liquidadas.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
