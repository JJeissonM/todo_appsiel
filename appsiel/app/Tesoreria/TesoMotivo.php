<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TesoMotivo extends Model
{
    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','descripcion','movimiento','estado','teso_tipo_motivo','contab_cuenta_id'];

    public $encabezado_tabla = ['Descripción','Motivo','Movimiento','Cuenta contrapartida','Acción'];

    public static function consultar_registros()
    {
    	$select_raw = 'CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo4';

        $registros = TesoMotivo::leftJoin('contab_cuentas','contab_cuentas.id','=','teso_motivos.contab_cuenta_id')
        					->where('teso_motivos.core_empresa_id', Auth::user()->empresa_id)
                    ->select('teso_motivos.descripcion AS campo1','teso_motivos.teso_tipo_motivo AS campo2','teso_motivos.movimiento AS campo3',DB::raw($select_raw),'teso_motivos.id AS campo5')
		                    ->get()
		                    ->toArray();

        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = TesoMotivo::where('teso_motivos.estado','Activo')
                    ->select('teso_motivos.id','teso_motivos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function cuenta_contable()
    {
        return $this->belongsTo('App\Contabilidad\ContabCuenta');
    }
}
