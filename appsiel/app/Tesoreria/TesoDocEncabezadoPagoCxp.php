<?php

namespace App\Tesoreria;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TesoDocEncabezadoPagoCxp extends TesoDocEncabezado
{
    // Apunta a la misma tabla de los modelos de tesorería
    protected $table = 'teso_doc_encabezados'; 

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_empresa_id','core_tercero_id','codigo_referencia_tercero','teso_tipo_motivo','documento_soporte','descripcion','teso_medio_recaudo_id','teso_caja_id','teso_cuenta_bancaria_id','valor_total','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Tercero', 'Detalle', 'Valor', 'Estado'];

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 33;

        $collection = TesoDocEncabezadoPagoCxp::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                                    ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                    ->where('teso_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                    ->select(
                                        'teso_doc_encabezados.fecha AS campo1',
                                        DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo2'),
                                        DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS campo3'),
                                        'teso_doc_encabezados.descripcion AS campo4',
                                        'teso_doc_encabezados.valor_total AS campo5',
                                        'teso_doc_encabezados.estado AS campo6',
                                        'teso_doc_encabezados.id AS campo7'
                                    )
                                    ->orderBy('teso_doc_encabezados.created_at', 'DESC')
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
        $core_tipo_transaccion_id = 33;
        $string = TesoDocEncabezadoPagoCxp::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('teso_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'teso_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS TERCERO'),
                'teso_doc_encabezados.descripcion AS DETALLE',
                'teso_doc_encabezados.valor_total AS VALOR',
                'teso_doc_encabezados.estado AS ESTADO'
            )
            ->where("teso_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")")'), "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('teso_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PAGOS CxP";
    }

    public function caja()
    {
        return $this->belongsTo( TesoCaja::class, 'teso_caja_id');
    }

    public function cuenta_bancaria()
    {
        return $this->belongsTo( TesoCuentaBancaria::class, 'teso_cuenta_bancaria_id');
    }

    public function medio_recaudo()
    {
        return $this->belongsTo(TesoMedioRecaudo::class, 'teso_medio_recaudo_id');
    }


    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS documento';

        return TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->where('teso_doc_encabezados.id', $id)
                    ->select(DB::raw($select_raw),'teso_doc_encabezados.fecha','core_terceros.descripcion AS tercero',
                        'teso_doc_encabezados.descripcion AS detalle',
                        'teso_doc_encabezados.documento_soporte',
                        'teso_doc_encabezados.core_tipo_transaccion_id',
                        'teso_doc_encabezados.core_tipo_doc_app_id',
                        'teso_doc_encabezados.id',
                        'teso_doc_encabezados.creado_por',
                        'teso_doc_encabezados.created_at',
                        'teso_doc_encabezados.consecutivo',
                        'teso_doc_encabezados.core_empresa_id',
                        'teso_doc_encabezados.core_tercero_id',
                        'teso_doc_encabezados.teso_tipo_motivo')
                    ->get()[0];
    }
}
