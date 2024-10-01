<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Retencion extends Model
{
    protected $table = 'contab_retenciones';
    protected $fillable = [ 'categoria_retenciones_id', 'descripcion', 'nombre_corto', 'tasa_retencion', 'cta_ventas_id', 'cta_ventas_devol_id', 'cta_compras_id', 'cta_compras_devol_id', 'estado' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Categoría', 'Descripción', 'Nombre corto', 'Tasa', 'Cta. Ventas', 'Cta. Compras', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public function categoria_retencion()
    {
        return $this->belongsTo( CategoriaRetencion::class, 'categoria_retenciones_id' );
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return Retencion::leftJoin('contab_cuentas AS ctas_ventas', 'ctas_ventas.id', '=', 'contab_retenciones.cta_ventas_id')
						->leftJoin('contab_cuentas AS ctas_compras', 'ctas_compras.id', '=', 'contab_retenciones.cta_compras_id')
						->leftJoin('contab_categorias_retenciones', 'contab_categorias_retenciones.id', '=', 'contab_retenciones.categoria_retenciones_id')    
    						->select(
					                'contab_categorias_retenciones.descripcion AS campo1',
					                'contab_retenciones.descripcion AS campo2',
					                'contab_retenciones.nombre_corto AS campo3',
					                'contab_retenciones.tasa_retencion AS campo4',
					                DB::raw("CONCAT( ctas_ventas.codigo,' ',ctas_ventas.descripcion ) AS campo5"),
					                DB::raw("CONCAT( ctas_compras.codigo,' ',ctas_compras.descripcion ) AS campo6"),
					                'contab_retenciones.estado AS campo7',
					                'contab_retenciones.id AS campo8'
					            )
					            ->where("contab_retenciones.descripcion", "LIKE", "%$search%")
					            ->orWhere("contab_categorias_retenciones.descripcion", "LIKE", "%$search%")
					            ->orWhere("contab_retenciones.tasa_retencion", "LIKE", "%$search%")
					            ->orWhere(DB::raw("CONCAT( ctas_ventas.codigo,' ',ctas_ventas.descripcion )"), "LIKE", "%$search%")
					            ->orWhere(DB::raw("CONCAT( ctas_compras.codigo,' ',ctas_compras.descripcion )"), "LIKE", "%$search%")
					            ->orWhere("contab_retenciones.estado", "LIKE", "%$search%")
					            ->orderBy('contab_retenciones.created_at', 'DESC')
					            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Retencion::select('contab_retenciones.categoria_retenciones_id AS campo1', 'contab_retenciones.descripcion AS campo2', 'contab_retenciones.nombre_corto AS campo3', 'contab_retenciones.tasa_retencion AS campo4', 'contab_retenciones.cta_ventas_id AS campo5', 'contab_retenciones.cta_compras_id AS campo6', 'contab_retenciones.estado AS campo7', 'contab_retenciones.id AS campo8')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE RETENCIONES";
    }

    public static function opciones_campo_select()
    {
        $opciones = Retencion::where('contab_retenciones.estado','Activo')
                    ->select('contab_retenciones.id','contab_retenciones.descripcion')
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
		                                    "tabla":"contab_registros_retenciones",
		                                    "llave_foranea":"contab_retencion_id",
		                                    "mensaje":"Retención está asociada a una registros de retenciones."
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
