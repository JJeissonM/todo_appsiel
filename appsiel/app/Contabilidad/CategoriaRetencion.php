<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use Schema;

class CategoriaRetencion extends Model
{
    protected $table = 'contab_categorias_retenciones';
    
    protected $fillable = ['descripcion', 'nombre_corto', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Descripción', 'Nombre corto', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return CategoriaRetencion::select('contab_categorias_retenciones.descripcion AS campo1', 'contab_categorias_retenciones.nombre_corto AS campo2', 'contab_categorias_retenciones.estado AS campo3', 'contab_categorias_retenciones.id AS campo4')
        ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = CategoriaRetencion::select('contab_categorias_retenciones.descripcion AS campo1', 'contab_categorias_retenciones.nombre_corto AS campo2', 'contab_categorias_retenciones.estado AS campo3', 'contab_categorias_retenciones.id AS campo4')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE CATEGORÍAS DE RETENCIONES";
    }

    public static function opciones_campo_select()
    {
        $opciones = CategoriaRetencion::where('contab_categorias_retenciones.estado','Activo')
                    ->select('contab_categorias_retenciones.id','contab_categorias_retenciones.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"contab_retenciones",
                                    "llave_foranea":"categoria_retenciones_id",
                                    "mensaje":"Categoría está asociada a una Retención."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla)
        {

            if ( !Schema::hasTable( $una_tabla->tabla ) )
            {
                continue;
            }

            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro))
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
