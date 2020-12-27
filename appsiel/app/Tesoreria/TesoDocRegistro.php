<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

class TesoDocRegistro extends Model
{
    protected $fillable = ['teso_encabezado_id','teso_motivo_id','core_tercero_id','teso_medio_recaudo_id','teso_caja_id','teso_cuenta_bancaria_id','detalle_operacion','valor','estado'];

    public static function get_registros_impresion( $doc_encabezado_id )
    {
        return TesoDocRegistro::leftJoin('teso_motivos','teso_motivos.id','=','teso_doc_registros.teso_motivo_id')
                            ->leftJoin('teso_medios_recaudo','teso_medios_recaudo.id','=','teso_doc_registros.teso_medio_recaudo_id')
                            ->leftJoin('teso_cajas','teso_cajas.id','=','teso_doc_registros.teso_caja_id')
                            ->leftJoin('teso_cuentas_bancarias','teso_cuentas_bancarias.id','=','teso_doc_registros.teso_cuenta_bancaria_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_registros.core_tercero_id')
                            ->where('teso_encabezado_id',$doc_encabezado_id)
                            ->select(
                                    'core_terceros.descripcion AS tercero',
                                    'core_terceros.descripcion AS tercero_nombre_completo',
                                    'teso_medios_recaudo.descripcion AS medio_recaudo',
                                    'core_terceros.id AS tercero_id',
                                    'core_terceros.numero_identificacion',
                                    'teso_motivos.descripcion AS motivo',
                                    'teso_motivos.id AS motivo_id',
                                    'teso_cajas.descripcion AS caja',
                                    'teso_cuentas_bancarias.descripcion AS cuenta_bancaria',
                            		'teso_doc_registros.detalle_operacion',
                            		'teso_doc_registros.valor AS valor')
                            ->get();
    }
}
