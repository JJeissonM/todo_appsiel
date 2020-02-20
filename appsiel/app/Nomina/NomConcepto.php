<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomConcepto extends Model
{
    //protected $table = 'nom_conceptos';
	protected $fillable = ['modo_liquidacion_id','naturaleza', 'porcentaje_sobre_basico', 'descripcion', 'abreviatura', 'forma_parte_basico', 'nom_agrupacion_id', 'estado'];

	public $encabezado_tabla = ['Modo Liquidación', 'Descripción', 'Abreviatura', 'Porc. sobre el básico', 'Naturaleza', 'Estado', 'Acción'];
	
    public static function consultar_registros()
	{
	    $registros = NomConcepto::leftJoin('nom_modos_liquidacion', 'nom_modos_liquidacion.id', '=', 'nom_conceptos.modo_liquidacion_id')
            ->select('nom_modos_liquidacion.descripcion AS campo1', 'nom_conceptos.descripcion AS campo2', 'nom_conceptos.abreviatura AS campo3', 'nom_conceptos.porcentaje_sobre_basico AS campo4', 'nom_conceptos.naturaleza AS campo5', 'nom_conceptos.estado AS campo6', 'nom_conceptos.id AS campo7')
		    ->get()
		    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = NomConcepto::where('estado','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function conceptos_del_documento($encabezado_doc_id)
    {
        return NomConcepto::leftJoin('nom_doc_registros','nom_doc_registros.nom_concepto_id','=','nom_conceptos.id')->where('nom_doc_registros.nom_doc_encabezado_id',$encabezado_doc_id)->select('nom_doc_registros.nom_concepto_id','nom_conceptos.descripcion','nom_conceptos.abreviatura','nom_conceptos.naturaleza')->distinct('nom_doc_registros.nom_concepto_id')->orderBy('nom_conceptos.id','ASC')->get();
    }
}
