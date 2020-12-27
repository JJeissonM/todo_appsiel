<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxcInteresMora extends Model
{
    protected $table = 'cxc_intereses_mora';

    protected $fillable = ['core_empresa_id', 'core_tercero_id', 'codigo_referencia_tercero', 'fecha_corte', 'calculado_sobre', 'saldo_vencido', 'tasa_interes', 'cxc_servicio_id', 'valor_interes', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tercero', 'Fecha corte', 'Calculado sobre', 'Saldo vencido', 'Tasa interés', 'Valor interés'];


    // Se consultan los documentos para la empresa que tiene asignada el usuario
    public static function consultar_registros($nro_registros)
    {

        return CxcInteresMora::leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_intereses_mora.core_tercero_id')
            ->where('cxc_intereses_mora.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'core_terceros.descripcion as campo1',
                'cxc_intereses_mora.fecha_corte AS campo2',
                'cxc_intereses_mora.calculado_sobre AS campo3',
                'cxc_intereses_mora.saldo_vencido AS campo4',
                'cxc_intereses_mora.tasa_interes AS campo5',
                'cxc_intereses_mora.valor_interes AS campo6',
                'cxc_intereses_mora.id AS campo7'
            )
            ->orderBy('cxc_intereses_mora.created_at', 'DESC')
            ->paginate($nro_registros);
    }
}
