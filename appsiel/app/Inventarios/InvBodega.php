<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InvBodega extends Model
{
    //protected $table = 'inv_bodegas'; 

    protected $fillable = ['core_empresa_id','descripcion','estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = InvBodega::where('inv_bodegas.core_empresa_id', Auth::user()->empresa_id)
                            ->select(
                                'inv_bodegas.descripcion AS campo1',
                                'inv_bodegas.estado AS campo2',
                                'inv_bodegas.id AS campo3'
                            )
                            ->orderBy('inv_bodegas.created_at', 'DESC')
                            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = InvBodega::where('inv_bodegas.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_bodegas.descripcion AS DESCRIPCIÓN',
                'inv_bodegas.estado AS ESTADO'
            )
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_bodegas.estado", "LIKE", "%$search%")
            ->orderBy('inv_bodegas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE BODEGAS";
    }

    public function movimientos()
    {
        return $this->hasMany('App\Inventarios\InvMovimiento');
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvBodega::where( 'estado','Activo' )
                            ->where( 'core_empresa_id', Auth::user()->empresa_id )
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
