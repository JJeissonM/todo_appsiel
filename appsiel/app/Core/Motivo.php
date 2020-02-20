<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Motivo extends Model
{
    protected $table = 'inv_motivos'; 

    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','descripcion','mov_origen','mov_destino','estado'];

    public $encabezado_tabla = ['Transacción','Descripción Motivo','Mov. bodega origen','Mov. bodega destino','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = Motivo::leftJoin('sys_tipos_transacciones','sys_tipos_transacciones.id','=','inv_motivos.core_tipo_transaccion_id')
                    ->where('inv_motivos.core_empresa_id',Auth::user()->empresa_id)
                    ->select('sys_tipos_transacciones.descripcion AS campo1','inv_motivos.descripcion AS campo2','inv_motivos.mov_origen AS campo3','inv_motivos.mov_destino AS campo4','inv_motivos.estado AS campo5','inv_motivos.id AS campo6')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function transaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion','core_tipo_transaccion_id');
    }
}
