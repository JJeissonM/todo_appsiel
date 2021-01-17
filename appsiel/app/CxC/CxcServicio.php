<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;

class CxcServicio extends Model
{
    //protected $table = 'inv_productos'; 

    protected $fillable = ['core_empresa_id','descripcion','precio_venta','contab_cuenta_id','estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Cuenta Contrapartida', 'Precio', 'Estado'];

    public static function consultar_registros($nro_registros)
    {
        $select_raw = 'CONCAT(contab_cuentas.codigo," ", contab_cuentas.descripcion) AS campo3';


        $registros = CxcServicio::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'cxc_servicios.contab_cuenta_id')->where('cxc_servicios.core_empresa_id', Auth::user()->empresa_id)
            ->select('cxc_servicios.id AS campo1', 'cxc_servicios.descripcion AS campo2', DB::raw($select_raw), 'cxc_servicios.precio_venta AS campo4', 'cxc_servicios.estado AS campo5', 'cxc_servicios.id AS campo6')
            ->orderBy('cxc_servicios.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public function cuenta_contable()
    {
        return $this->belongsTo('App\Contabilidad\ContabCuenta');
    }

    public static function opciones_campo_select()
    {
        $opciones = CxcServicio::where('cxc_servicios.core_empresa_id',Auth::user()->empresa_id)
                    ->select('cxc_servicios.id','cxc_servicios.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->codigo.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
