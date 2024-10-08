<?php

namespace App\Inventarios;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RemisionVentas extends InvDocEncabezado
{
    protected $table = 'inv_doc_encabezados';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Bodega', 'Tercero', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 24; // Remisión de ventas

        $collection = RemisionVentas::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                                ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                                ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                ->select(
                                    DB::raw('DATE_FORMAT(inv_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2'),
                                    'inv_bodegas.descripcion AS campo3',
                                    DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS campo4'),
                                    'inv_doc_encabezados.estado AS campo5',
                                    'inv_doc_encabezados.id AS campo6'
                                )
                                ->orderBy('inv_doc_encabezados.created_at', 'DESC')
                                ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if (empty($nuevaColeccion)) {
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
        foreach ($valores_campos_seleccionados as $valor_campo) {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false) {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    public static function sqlString($search)
    {
        $string = $core_tipo_transaccion_id = 24; // Remisión de ventas
        return RemisionVentas::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
            ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'inv_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS DOCUMENTO'),
                'inv_bodegas.descripcion AS BODEGA',
                DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS TERCERO'),
                'inv_doc_encabezados.descripcion AS DETALLE',
                'inv_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("inv_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")")'), 'LIKE', '%$search%')
            ->orWhere("inv_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('inv_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE REMISION VENTAS";
    }

    public function crear_nueva( $datos, $parametros = null )
    {
        // Paso 1: Crear encabezado
        if ( is_null( $parametros ) )
        {
            // Llamar a los parámetros del archivo de configuración
            $parametros = config('ventas');
        }

        $datos['core_tipo_transaccion_id'] = $parametros['rm_tipo_transaccion_id'];
        $datos['core_tipo_doc_app_id'] = $parametros['rm_tipo_doc_app_id'];
        
        $datos['estado'] = 'Facturada';
        $encabezado_documento = $this->crear_encabezado( $parametros['rm_modelo_id'], $datos );

        // Paso 2
        // El campo lineas_registros viene del request
        $lineas_registros = json_decode( $datos['lineas_registros'] );
        $this->crear_lineas_registros( $datos, $encabezado_documento, $lineas_registros );

        // Paso 3
        $this->contabilizar( $encabezado_documento );

        return $encabezado_documento;
    }

}
