<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ContabCuenta extends Model
{
    protected $fillable = ['core_empresa_id','codigo','contab_cuenta_clase_id','contab_cuenta_grupo_id','descripcion','core_app_id','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['ID','Clase','Grupo','Código','Descripción','Aplicación asociada','Acción'];

    public static function consultar_registros()
    {
        $registros = ContabCuenta::leftJoin('contab_cuenta_clases', 'contab_cuenta_clases.id', '=', 'contab_cuentas.contab_cuenta_clase_id')
                    ->leftJoin('contab_cuenta_grupos', 'contab_cuenta_grupos.id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
                    ->leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'contab_cuentas.core_app_id')
                    ->where('contab_cuentas.core_empresa_id',Auth::user()->empresa_id)
                    ->select('contab_cuentas.id AS campo1','contab_cuenta_clases.descripcion AS campo2','contab_cuenta_grupos.descripcion AS campo3','contab_cuentas.codigo AS campo4','contab_cuentas.descripcion AS campo5','sys_aplicaciones.descripcion AS campo6','contab_cuentas.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/contabilidad/funciones.js';

    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = DB::table('contab_cuenta_grupos')
                    ->where( 'contab_cuenta_clase_id', $id_select_padre )
                    ->where('core_empresa_id','=',Auth::user()->empresa_id)
                    ->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {
            $grupo = DB::table('contab_cuenta_grupos')
                            ->where( 'id', $campo->grupo_padre_id )
                            ->value('descripcion');
                            
            $opciones .= '<option value="'.$campo->id.'">'.$grupo.' > '.$campo->descripcion.'</option>';
        }
        return $opciones;
    }

    public static function opciones_campo_select()
    {
        $opciones = ContabCuenta::where('contab_cuentas.core_empresa_id',Auth::user()->empresa_id)
                    ->select('contab_cuentas.id','contab_cuentas.codigo','contab_cuentas.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->codigo.' '.$opcion->descripcion;
        }

        return $vec;
    }

}
