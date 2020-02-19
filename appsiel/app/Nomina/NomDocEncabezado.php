<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

class NomDocEncabezado extends Model
{
    //protected $table = 'nom_doc_encabezados';

    // tiempo_a_liquidar: cantidad de horas a liquidar en el documento !!! WARNING, puede haber conflicto cuando una empleado tiene una cantidad de horas_laborales al mes diferente a los demás, pued que todas sus horas se liquiden antes de cumplirse el mes. Ejemplo, si tiene en el contrato 120 horas (medio tiempo) y se hacen dos documentos con un tiempo_a_liquidar de 120 horas cada uno, al empleado se le liquidarán 240 horas !!!!
	protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'descripcion','tiempo_a_liquidar', 'total_devengos', 'total_deducciones', 'estado', 'creado_por', 'modificado_por'];
	
	public $encabezado_tabla = ['Documento', 'Fecha', 'Descripción', 'Total devengos', 'Total deducciones', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    
	    $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",nom_doc_encabezados.consecutivo) AS campo1';

	    $registros = NomDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'nom_doc_encabezados.core_tipo_doc_app_id')
                    ->select(DB::raw($select_raw), 'nom_doc_encabezados.fecha AS campo2', 'nom_doc_encabezados.descripcion AS campo3', 'nom_doc_encabezados.total_devengos AS campo4', 'nom_doc_encabezados.total_deducciones AS campo5', 'nom_doc_encabezados.estado AS campo6', 'nom_doc_encabezados.id AS campo7')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",nom_doc_encabezados.consecutivo) AS documento_app';

        $registro = NomDocEncabezado::where('nom_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'nom_doc_encabezados.core_tipo_doc_app_id')
                    ->select( DB::raw($select_raw),
                        'nom_doc_encabezados.id',
                        'nom_doc_encabezados.core_empresa_id',
                        'nom_doc_encabezados.fecha',
                        'nom_doc_encabezados.descripcion',
                        'nom_doc_encabezados.core_tipo_transaccion_id',
                        'nom_doc_encabezados.core_tipo_doc_app_id',
                        'nom_doc_encabezados.consecutivo',
                        'nom_doc_encabezados.total_devengos',
                        'nom_doc_encabezados.total_deducciones',
                        'nom_doc_encabezados.creado_por')
                    ->get()[0];

        return $registro;
    }
}
