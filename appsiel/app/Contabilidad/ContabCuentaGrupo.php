<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ContabCuentaGrupo extends Model
{
    protected $fillable = ['core_empresa_id', 'contab_cuenta_clase_id', 'grupo_padre_id', 'descripcion', 'mostrar_en_reporte', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Clase', 'Padre', 'Descripción'];

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/contabilidad/funciones.js';

    public static function consultar_registros($nro_registros)
    {
        $registros = ContabCuentaGrupo::leftJoin('contab_cuenta_clases', 'contab_cuenta_clases.id', '=', 'contab_cuenta_grupos.contab_cuenta_clase_id')
            ->where('contab_cuenta_grupos.core_empresa_id', Auth::user()->empresa_id)
            ->select('contab_cuenta_clases.descripcion AS campo1', 'contab_cuenta_grupos.grupo_padre_id as campo2', 'contab_cuenta_grupos.descripcion AS campo3', 'contab_cuenta_grupos.id AS campo4')
            ->orderBy('contab_cuenta_grupos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function opciones_campo_select()
    {

        // MEJORAR PARA QUE MUESTRE LOS GRUPOS PADRES

        $opciones = ContabCuentaGrupo::where('core_empresa_id', Auth::user()->empresa_id)->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = DB::table('contab_cuenta_grupos')
            ->where('contab_cuenta_clase_id', $id_select_padre)
            ->where('core_empresa_id', '=', Auth::user()->empresa_id)
            ->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {
            $grupo = DB::table('contab_cuenta_grupos')
                ->where('id', $campo->grupo_padre_id)
                ->value('descripcion');

            $opciones .= '<option value="' . $campo->id . '">' . $grupo . ' > ' . $campo->descripcion . '</option>';
        }
        return $opciones;
    }
}
