<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;

class CxcDocRegistro extends Model
{
    //protected $table = '';

    protected $fillable = ['cxc_doc_encabezado_id','core_tercero_id', 'codigo_referencia_tercero','cxc_motivo_id','cxc_servicio_id','valor_unitario','cantidad','valor_total','descripcion','estado'];


    /*[ 'name' => '', 'display' => 'none', 'etiqueta' => ''],
												[ 'name' => '', 'display' => 'none', 'etiqueta' => ''],
												[ 'name' => '', 'display' => 'none', 'etiqueta' => ''],

												[ 'name' => 'lbl_servicio', 'display' => '', 'etiqueta' => ''],
												[ 'name' => 'lbl_tercero', 'display' => '', 'etiqueta' => ''],
												[ 'name' => 'lbl_valor', 'display' => '', 'etiqueta' => '']*/

    public $campos_invisibles_linea_registro = ['servicio_id','tercero_id','valor']; // 3 campos

    public $campos_visibles_linea_registro = [ 
    											['Concepto/Servicio',''],
    											['Tercero',''],
    											['Valor',''],
    											['&nbsp;','10px']
    										]; // 4 campos

    public static function registros_del_encabezado($encabezado_id)
    {
    	return CxcDocRegistro::leftJoin('cxc_servicios', 'cxc_servicios.id', '=', 'cxc_doc_registros.cxc_servicio_id')
            ->where('cxc_doc_encabezado_id',$encabezado_id)
            ->select('cxc_doc_registros.descripcion','cxc_doc_registros.valor_unitario','cxc_servicios.descripcion as descripcion_servicio')
            ->get();
    }
}
