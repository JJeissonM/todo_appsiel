<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

class TesoDocRegistro extends Model
{
    //protected $table = 'teso_doc_registros';

    protected $fillable = ['teso_encabezado_id','teso_motivo_id','core_tercero_id','teso_medio_recaudo_id','teso_caja_id','teso_cuenta_bancaria_id','detalle_operacion','valor','estado'];

    public static function get_registros_impresion( $doc_encabezado_id )
    {

    	$select_raw2 = 'CONCAT(core_terceros.numero_identificacion," ",core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero';

        return TesoDocRegistro::leftJoin('teso_motivos','teso_motivos.id','=','teso_doc_registros.teso_motivo_id')
                            ->leftJoin('teso_medios_recaudo','teso_medios_recaudo.id','=','teso_doc_registros.teso_medio_recaudo_id')
                            ->leftJoin('teso_cajas','teso_cajas.id','=','teso_doc_registros.teso_caja_id')
                            ->leftJoin('teso_cuentas_bancarias','teso_cuentas_bancarias.id','=','teso_doc_registros.teso_cuenta_bancaria_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_registros.core_tercero_id')
                            ->where('teso_encabezado_id',$doc_encabezado_id)
                            ->select(DB::raw($select_raw2),
                                    'teso_medios_recaudo.descripcion AS medio_recaudo',
                                    'teso_motivos.descripcion AS motivo',
                                    'teso_motivos.id AS motivo_id',
                                    'teso_cajas.descripcion AS caja',
                                    'teso_cuentas_bancarias.descripcion AS cuenta_bancaria',
                            		'teso_doc_registros.detalle_operacion',
                            		'teso_doc_registros.valor AS valor')
                            ->get();
    }
}
