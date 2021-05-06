<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

class ControlCheque extends Model
{
    protected $table = 'teso_control_cheques';

    protected $fillable = ['fuente', 'tercero_id', 'fecha_emision', 'fecha_cobro', 'numero_cheque', 'referencia_cheque', 'entidad_financiera_id', 'valor', 'detalle', 'creado_por', 'modificado_por', 'core_tipo_transaccion_id_origen', 'core_tipo_doc_app_id_origen', 'consecutivo', 'teso_caja_id', 'tipo', 'estado'];		
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Fecha emisión', 'Tercero', 'Fuente', 'Fecha cobro', 'Número cheque', 'Referencia', 'Valor', 'Caja', 'Doc. relacionado', 'Estado'];		

    public static function consultar_registros($nro_registros, $search)
    {
        return ControlCheque::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_control_cheques.core_tipo_doc_app_id_origen')
			            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_control_cheques.tercero_id')
			            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_control_cheques.teso_caja_id')
			            ->select(
			            			'teso_control_cheques.fecha_emision AS campo1',
			            			DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo2'),
			            			'teso_control_cheques.fuente AS campo3',
			            			'teso_control_cheques.fecha_cobro AS campo4',
			            			'teso_control_cheques.numero_cheque AS campo5',
			            			'teso_control_cheques.referencia_cheque AS campo6',
			            			'teso_control_cheques.valor AS campo7',
			            			'teso_cajas.descripcion AS campo8',
			            			DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_control_cheques.consecutivo) AS campo9'),
			            			'teso_control_cheques.estado AS campo10',
			            			'teso_control_cheques.id AS campo11')
			            ->orderBy('teso_control_cheques.fecha_emision','DESC')
        				->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ControlCheque::select('teso_control_cheques.fuente AS campo1', 'teso_control_cheques.tercero_id AS campo2', 'teso_control_cheques.fecha_emision AS campo3', 'teso_control_cheques.fecha_cobro AS campo4', 'teso_control_cheques.numero_cheque AS campo5', 'teso_control_cheques.referencia_cheque AS campo6', 'teso_control_cheques.valor AS campo7', 'teso_control_cheques.detalle AS campo8', 'teso_control_cheques.core_tipo_transaccion_id_origen AS campo9', 'teso_control_cheques.estado AS campo10', 'teso_control_cheques.id AS campo11')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE CHEQUES";
    }

    public static function opciones_campo_select()
    {
        $opciones = ControlCheque::where('teso_control_cheques.estado','Activo')
                    ->select('teso_control_cheques.id','teso_control_cheques.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
