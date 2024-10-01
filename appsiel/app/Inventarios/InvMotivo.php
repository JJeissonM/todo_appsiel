<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvMotivo extends Model
{
    //protected $table = 'inv_motivos'; 

    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','descripcion','movimiento','cta_contrapartida_id','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Transacción asociada', 'Movimiento', 'Cta. Contrapartida', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = InvMotivo::leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'inv_motivos.core_tipo_transaccion_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'inv_motivos.cta_contrapartida_id')
            ->where('inv_motivos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_motivos.id AS campo1',
                'inv_motivos.descripcion AS campo2',
                'sys_tipos_transacciones.descripcion AS campo3',
                'inv_motivos.movimiento AS campo4',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo5'),
                'inv_motivos.estado AS campo6',
                'inv_motivos.id AS campo7'
            )
            ->where("inv_motivos.id", "LIKE", "%$search%")
            ->orWhere("inv_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_motivos.movimiento", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere("inv_motivos.estado", "LIKE", "%$search%")
            ->orderBy('inv_motivos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = InvMotivo::leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'inv_motivos.core_tipo_transaccion_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'inv_motivos.cta_contrapartida_id')
            ->where('inv_motivos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_motivos.id AS CÓDIGO',
                'inv_motivos.descripcion AS DESCRIPCIÓN',
                'sys_tipos_transacciones.descripcion AS TRANSACCIÓN_ASOCIADA',
                'inv_motivos.movimiento AS MOVIMIENTO',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS CTA_CONTRAPARTIDA'),
                'inv_motivos.estado AS ESTADO'
            )
            ->where("inv_motivos.id", "LIKE", "%$search%")
            ->orWhere("inv_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_motivos.movimiento", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere("inv_motivos.estado", "LIKE", "%$search%")
            ->orderBy('inv_motivos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOTIVOS DE INVENTARIO";
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvMotivo::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public static function get_motivos_transaccion( $transaccion_id )
    {
        $motivos = InvMotivo::where('core_tipo_transaccion_id',$transaccion_id)
                            ->where('estado','Activo')
                            ->get();      
        $vec_m = [];
        foreach ($motivos as $fila) {
            $vec_m[$fila->id.'-'.$fila->movimiento]=$fila->descripcion; 
        }
        
        return $vec_m;
    }

}
