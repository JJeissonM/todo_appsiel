<?php

namespace App\Nomina\NominaElectronica;

use Illuminate\Database\Eloquent\Model;

use DB;
use App\Nomina\EquivalenciaContable;

class ConceptoDian extends Model
{
    protected $table = 'nom_elect_cat_cptos_dian';

    protected $fillable = [ 'naturaleza', 'codigo', 'porcentaje_del_basico', 'porcentaje_monto_no_salarial', 'liquida_dias', 'liquida_horas', 'liquida_fechas', 'estado', 'creado_por', 'modificado_por' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', '% del básico', '% NO salarial', 'Naturaleza', 'Liq. días', 'Liq. horas', 'Liq. fechas', 'Estado' ];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return ConceptoDian::select(
                'nom_elect_cat_cptos_dian.codigo AS campo1',
                'nom_elect_cat_cptos_dian.porcentaje_del_basico AS campo2',
                'nom_elect_cat_cptos_dian.porcentaje_monto_no_salarial AS campo3',
                'nom_elect_cat_cptos_dian.naturaleza AS campo4',
                DB::raw('IF(nom_elect_cat_cptos_dian.liquida_dias=0,REPLACE(nom_elect_cat_cptos_dian.liquida_dias,0,"No"),REPLACE(nom_elect_cat_cptos_dian.liquida_dias,1,"Si")) AS campo5'),
                DB::raw('IF(nom_elect_cat_cptos_dian.liquida_horas=0,REPLACE(nom_elect_cat_cptos_dian.liquida_horas,0,"No"),REPLACE(nom_elect_cat_cptos_dian.liquida_horas,1,"Si")) AS campo6'),
                DB::raw('IF(nom_elect_cat_cptos_dian.liquida_fechas=0,REPLACE(nom_elect_cat_cptos_dian.liquida_fechas,0,"No"),REPLACE(nom_elect_cat_cptos_dian.liquida_fechas,1,"Si")) AS campo7'),
                'nom_elect_cat_cptos_dian.estado AS campo8',
                'nom_elect_cat_cptos_dian.id AS campo9'
            )
            ->orderBy('nom_elect_cat_cptos_dian.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ConceptoDian::leftJoin('nom_modos_liquidacion', 'nom_modos_liquidacion.id', '=', 'nom_elect_cat_cptos_dian.modo_liquidacion_id')
            ->leftJoin('nom_agrupaciones_conceptos', 'nom_agrupaciones_conceptos.id', '=', 'nom_elect_cat_cptos_dian.nom_agrupacion_id')
            ->select(
                'nom_elect_cat_cptos_dian.codigo AS DESCRIPCIÓN',
                'nom_elect_cat_cptos_dian.porcentaje_del_basico AS %_del_básico',
                'nom_elect_cat_cptos_dian.porcentaje_monto_no_salarial AS %_NO_salarial',
                'nom_elect_cat_cptos_dian.naturaleza AS Naturaleza',
                DB::raw('IF(nom_elect_cat_cptos_dian.liquida_dias=0,REPLACE(nom_elect_cat_cptos_dian.liquida_dias,0,"No"),REPLACE(nom_elect_cat_cptos_dian.liquida_dias,1,"Si")) AS Liq._dias'),
                DB::raw('IF(nom_elect_cat_cptos_dian.liquida_horas=0,REPLACE(nom_elect_cat_cptos_dian.liquida_horas,0,"No"),REPLACE(nom_elect_cat_cptos_dian.liquida_horas,1,"Si")) AS Liq_horas'),
                DB::raw('IF(nom_elect_cat_cptos_dian.liquida_fechas=0,REPLACE(nom_elect_cat_cptos_dian.liquida_fechas,0,"No"),REPLACE(nom_elect_cat_cptos_dian.liquida_fechas,1,"Si")) AS Liq_fechas'),
                'nom_elect_cat_cptos_dian.estado AS ESTADO',
                'nom_elect_cat_cptos_dian.id AS ID'
            )
            ->orderBy('nom_elect_cat_cptos_dian.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONCEPTOS DIAN";
    }

    public static function opciones_campo_select()
    {
        $opciones = ConceptoDian::where('estado', 'Activo')->orderBy('codigo')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->codigo;
        }

        return $vec;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_conceptos",
                                    "llave_foranea":"nom_elect_concepto_dian_id",
                                    "mensaje":"Está asociado a un concepto de liquidación."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
