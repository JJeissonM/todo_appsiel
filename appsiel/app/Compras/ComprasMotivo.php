<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComprasMotivo extends Model
{
    //protected $table = 'compras_motivos'; 

    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','descripcion','cta_contrapartida_id','estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Transacción asociada', 'Movimiento', 'Cta. Contrapartida', 'Estado'];

    public static function consultar_registros($nro_registros)
    {
        $registros = ComprasMotivo::leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'compras_motivos.core_tipo_transaccion_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'compras_motivos.cta_contrapartida_id')
            ->where('compras_motivos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'compras_motivos.id AS campo1',
                'compras_motivos.descripcion AS campo2',
                'sys_tipos_transacciones.descripcion AS campo3',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo4'),
                'compras_motivos.estado AS campo5',
                'compras_motivos.id AS campo6')
            ->orderBy('compras_motivos.created_at',
            'DESC')
            ->paginate($nro_registros);

        return $registros;
    }


    public static function get_motivos_transaccion( $transaccion_id )
    {
        $motivos = ComprasMotivo::where('core_tipo_transaccion_id',$transaccion_id)
                            ->where('estado','Activo')
                            ->get();      
        $vec_m = [];
        foreach ($motivos as $fila) {
            $vec_m[$fila->id.'-'.$fila->movimiento]=$fila->descripcion; 
        }
        
        return $vec_m;
    }

}
