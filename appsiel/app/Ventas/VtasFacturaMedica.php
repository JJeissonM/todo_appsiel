<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Ventas\DocEncabezadoTieneFormulaMedica;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class VtasFacturaMedica extends VtasDocEncabezado
{
    protected $table = 'vtas_doc_encabezados';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Detalle', 'Valor total', 'Forma de pago', 'Estado'];

    public $vistas = '{}';

    public $urls_acciones = '{"create":"factura_medica/create","show":"factura_medica/id_fila","store":"ventas"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 44; // Facturas

        $collection = VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                'core_terceros.descripcion AS campo3',
                'vtas_doc_encabezados.descripcion AS campo4',
                'vtas_doc_encabezados.valor_total AS campo5',
                'vtas_doc_encabezados.forma_pago AS campo6',
                'vtas_doc_encabezados.estado AS campo7',
                'vtas_doc_encabezados.id AS campo8'
            )
            ->orderBy('vtas_doc_encabezados.fecha', 'DESC')
            ->orderBy('vtas_doc_encabezados.created_at')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
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
        $core_tipo_transaccion_id = 44; // Facturas Médicas
        $string = VtasFacturaMedica::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS PACIENTE'),
                'vtas_doc_encabezados.descripcion AS DETALLE',
                'vtas_doc_encabezados.valor_total AS VALOR_TOTAL',
                'vtas_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("vtas_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE FACTURAS MEDICAS";
    }

    // Solo se creó un registro vacío en la tabla clientes
    public function store_adicional($datos, $doc_encabezado)
    {
        // Se asocia la formula seleccionada a la factura de ventas
        if (isset($datos['formula_id'])) {
            DocEncabezadoTieneFormulaMedica::create(
                [
                    'vtas_doc_encabezado_id' => $doc_encabezado->id,
                    'formula_medica_id' => $datos['formula_id']
                ]
            );
        }

        // Cuando el cliente no es un paciente, se almacenan sus datos de formula médica en un campo
        if ($datos['no_es_paciente']) {
            $cadena = '{"esfera_ojo_derecho":"' . $datos['esfera_ojo_derecho'] . '", "cilindro_ojo_derecho":"' . $datos['cilindro_ojo_derecho'] . '", "eje_ojo_derecho":"' . $datos['eje_ojo_derecho'] . '", "adicion_ojo_derecho":"' . $datos['adicion_ojo_derecho'] . '", "agudeza_visual_ojo_derecho":"' . $datos['agudeza_visual_ojo_derecho'] . '", "distancia_pupilar_ojo_derecho":"' . $datos['distancia_pupilar_ojo_derecho'] . '", "esfera_ojo_izquierdo":"' . $datos['esfera_ojo_izquierdo'] . '", "cilindro_ojo_izquierdo":"' . $datos['cilindro_ojo_izquierdo'] . '", "eje_ojo_izquierdo":"' . $datos['eje_ojo_izquierdo'] . '", "adicion_ojo_izquierdo":"' . $datos['adicion_ojo_izquierdo'] . '", "agudeza_visual_ojo_izquierdo":"' . $datos['agudeza_visual_ojo_izquierdo'] . '", "distancia_pupilar_ojo_izquierdo":"' . $datos['distancia_pupilar_ojo_izquierdo'] . '"}';

            DocEncabezadoTieneFormulaMedica::create(
                [
                    'vtas_doc_encabezado_id' => $doc_encabezado->id,
                    'formula_medica_id' => 0,
                    'contenido_formula' => $cadena
                ]
            );
        }
    }
}
