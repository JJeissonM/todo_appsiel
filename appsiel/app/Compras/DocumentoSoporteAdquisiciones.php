<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class DocumentoSoporteAdquisiciones extends ComprasDocEncabezado
{
    protected $table = 'compras_doc_encabezados';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento compra', 'Proveedor', 'Factura', 'Detalle', 'Valor total', 'Forma de pago', 'Estado'];

    public $urls_acciones = '{"create":"compras/create","show":"compras/id_fila","store":"compras"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 48;

        $collection = DocumentoSoporteAdquisiciones::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
                                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
                                            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                            ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                            ->select(
                                                'compras_doc_encabezados.fecha AS campo1',
                                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS campo2'),
                                                'core_terceros.descripcion AS campo3',
                                                DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo) AS campo4'),
                                                'compras_doc_encabezados.descripcion AS campo5',
                                                'compras_doc_encabezados.valor_total AS campo6',
                                                'compras_doc_encabezados.forma_pago AS campo7',
                                                'compras_doc_encabezados.estado AS campo8',
                                                'compras_doc_encabezados.id AS campo9'
                                            )
                                            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
                                            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if ( self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if ( empty($nuevaColeccion) )
        {
            return $array = new LengthAwarePaginator([], 1, 1, 1, [
                                                                    'path' => $request->url(),
                                                                    'query' => $request->query(),
                                                                ]);
        }
        
        //obtenemos el numero de la página actual, por defecto 1
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $total = count($nuevaColeccion); //Total para contar los registros mostrados
        $starting_point = ($page * $nro_registros) - $nro_registros; // punto de inicio para mostrar registros
        $array = $nuevaColeccion->slice($starting_point, $nro_registros); //indicamos desde donde y cuantos registros mostrar
        $array = new LengthAwarePaginator($array, $total, $nro_registros, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]); //finalmente se pagina y organiza la coleccion a devolver con todos los datos

        return $array;
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param array $valores_campos_seleccionados de campos donde se busca
     * @param string $searchTerm termino de busqueda
     * @return bool
     */
    public static function likePhp($valores_campos_seleccionados, $searchTerm)
    {
        $encontrado = false;
        $searchTerm = str_slug($searchTerm); // Para eliminar acentos
        foreach ($valores_campos_seleccionados as $valor_campo)
        {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false)
            {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 48;
        $string = DocumentoSoporteAdquisiciones::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'compras_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS DOCUMENTO_COMPRA'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS PROVEEDOR'),
                DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo) AS FACTURA'),
                'compras_doc_encabezados.descripcion AS DETALLE',
                'compras_doc_encabezados.valor_total AS VALOR_TOTAL',
                'compras_doc_encabezados.forma_pago AS FORMA_PAGO',
                'compras_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("compras_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCUMENTO SOPRTE EN ADQUISICIONES";
    }
}
