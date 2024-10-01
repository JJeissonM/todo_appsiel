<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use App\Core\Tercero;
use App\Core\TipoDocApp;
use App\Sistema\TipoTransaccion;
use App\Contabilidad\ContabCuenta;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContabMovimiento extends Model
{
    // tipo_transaccion se refiere al tipo de transacción de la línea
    protected $fillable = [ 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'id_registro_doc_tipo_transaccion', 'fecha', 'core_empresa_id', 'core_tercero_id', 'codigo_referencia_tercero', 'documento_soporte', 'contab_cuenta_id', 'valor_operacion', 'valor_debito', 'valor_credito', 'valor_saldo', 'detalle_operacion', 'tipo_transaccion', 'inv_producto_id', 'cantidad', 'tasa_impuesto', 'base_impuesto', 'valor_impuesto', 'teso_caja_id', 'teso_cuenta_bancaria_id', 'estado', 'creado_por', 'modificado_por', 'fecha_vencimiento', 'inv_bodega_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Tercero', 'Producto', 'Detalle', 'Cuenta', 'Tasa impuesto', 'Base impuesto', 'Débito', 'Crédito'];

    public $vistas = '{"index":"layouts.index3"}';

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'core_tercero_id');
    }

    public function cuenta()
    {
        return $this->belongsTo(ContabCuenta::class, 'contab_cuenta_id');
    }

    public function tipo_transaccion()
    {
        return $this->belongsTo(TipoTransaccion::class, 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo(TipoDocApp::class, 'core_tipo_doc_app_id');
    }

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    }

    public function tasa_impuesto()
    {
        return $this->hasOne(Impuesto::class,'tasa_impuesto','tasa_impuesto');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'contab_movimientos.inv_producto_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'contab_movimientos.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS campo2'),
                'core_terceros.descripcion AS campo3',
                DB::raw('CONCAT(inv_productos.id," ",inv_productos.descripcion) AS campo4'),
                'contab_movimientos.detalle_operacion AS campo5',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo6'),
                'contab_movimientos.tasa_impuesto AS campo7',
                'contab_movimientos.base_impuesto AS campo8',
                'contab_movimientos.valor_debito AS campo9',
                'contab_movimientos.valor_credito AS campo10',
                'contab_movimientos.id AS campo11'
            )
            ->orWhere("contab_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(inv_productos.id," ",inv_productos.descripcion)'), "LIKE", "%$search%")
            ->orWhere("contab_movimientos.detalle_operacion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere("contab_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("contab_movimientos.base_impuesto", "LIKE", "%$search%")
            ->orWhere("contab_movimientos.valor_debito", "LIKE", "%$search%")
            ->orWhere("contab_movimientos.valor_credito", "LIKE", "%$search%")
            ->orderBy('contab_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'contab_movimientos.inv_producto_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'contab_movimientos.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS DOCUMENTO'),
                'core_terceros.descripcion AS TERCERO',
                DB::raw('CONCAT(inv_productos.id," ",inv_productos.descripcion) AS PRODUCTO'),
                'contab_movimientos.detalle_operacion AS DETALLE',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS CUENTA'),
                'contab_movimientos.tasa_impuesto AS TASA_IMPUESTO',
                'contab_movimientos.base_impuesto AS BASE_IMPUESTO',
                'contab_movimientos.valor_debito AS DÉBITO',
                'contab_movimientos.valor_credito AS CRÉDITO'
            )
            ->orWhere("contab_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(inv_productos.id," ",inv_productos.descripcion)'), "LIKE", "%$search%")
            ->orWhere("contab_movimientos.detalle_operacion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere("contab_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("contab_movimientos.base_impuesto", "LIKE", "%$search%")
            ->orWhere("contab_movimientos.valor_debito", "LIKE", "%$search%")
            ->orWhere("contab_movimientos.valor_credito", "LIKE", "%$search%")
            ->orderBy('contab_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTOS CONTABLES";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $collection = ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'contab_movimientos.inv_producto_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
            //->whereIn('contab_movimientos.core_tipo_transaccion_id', [9,8,17] )
            ->select(
                'contab_movimientos.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS campo2'),
                'core_terceros.descripcion AS campo3',
                DB::raw('CONCAT(inv_productos.id," ",inv_productos.descripcion) AS campo4'),
                'contab_movimientos.detalle_operacion AS campo5',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo6'),
                'contab_movimientos.tasa_impuesto AS campo7',
                'contab_movimientos.base_impuesto AS campo8',
                'contab_movimientos.valor_debito AS campo9',
                'contab_movimientos.valor_credito AS campo10',
                'contab_movimientos.id AS campo11'
            )
            ->orderBy('contab_movimientos.created_at', 'DESC')
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


    public static function get_saldo_inicial($fecha_inicial, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $empresa_id)
    {
        $saldo_inicial_sql = ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')->where('contab_movimientos.fecha', '<', $fecha_inicial)
            ->where('contab_cuentas.codigo', 'LIKE', $contab_cuenta_id)
            ->where('core_terceros.numero_identificacion', 'LIKE', $numero_identificacion)
            ->where('contab_movimientos.codigo_referencia_tercero', $operador, $codigo_referencia_tercero)
            ->where('contab_movimientos.core_empresa_id', '=', $empresa_id)
            ->select(DB::raw('sum(contab_movimientos.valor_saldo) AS valor_saldo'))
            ->get()
            ->toArray()[0];

        return $saldo_inicial_sql['valor_saldo'];
    }

    public static function get_saldo_inicial_v2($fecha_desde, $cuenta_id, $tercero_id, $grupo_cuenta_id, $clase_cuenta_id)
    {
        $array_wheres = [
            ['fecha', '<', $fecha_desde],
            ['core_empresa_id', '=', Auth::user()->empresa_id]
        ];

        if (!is_null($tercero_id)) {
            $array_wheres = array_merge($array_wheres, [['core_tercero_id', '=', $tercero_id]]);
        }

        if (!is_null($clase_cuenta_id)) {
            $arr_ids_cuentas_de_la_clase = ContabCuenta::where('contab_cuenta_clase_id',$clase_cuenta_id)->get()->pluck('id')->toArray();
            return ContabMovimiento::where($array_wheres)
                            ->whereIn('contab_cuenta_id',$arr_ids_cuentas_de_la_clase)
                            ->sum('valor_saldo');
        }

        if (!is_null($grupo_cuenta_id)) {
            $arr_ids_cuentas_del_grupo = ContabCuenta::where('contab_cuenta_grupo_id',$grupo_cuenta_id)->get()->pluck('id')->toArray();
            return ContabMovimiento::where($array_wheres)
                            ->whereIn('contab_cuenta_id',$arr_ids_cuentas_del_grupo)
                            ->sum('valor_saldo');
        }

        if (!is_null($cuenta_id)) {
            $array_wheres = array_merge($array_wheres, [['contab_cuenta_id', '=', $cuenta_id]]);
        }

        return ContabMovimiento::where($array_wheres)->sum('valor_saldo');
    }

    public static function get_movimiento_contable($fecha_desde, $fecha_hasta, $cuenta_id, $tercero_id, $grupo_cuenta_id, $clase_cuenta_id)
    {
        $array_wheres = [
            ['core_empresa_id', '=', Auth::user()->empresa_id]
        ];

        if (!is_null($tercero_id)) {
            $array_wheres = array_merge($array_wheres, ['core_tercero_id' => $tercero_id]);
        }

        if (!is_null($clase_cuenta_id)) {
            $arr_ids_cuentas_de_la_clase = ContabCuenta::where('contab_cuenta_clase_id',$clase_cuenta_id)->get()->pluck('id')->toArray();
            return ContabMovimiento::whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->where($array_wheres)
                            ->whereIn('contab_cuenta_id',$arr_ids_cuentas_de_la_clase)
                            ->orderBy('fecha')
                            ->get();
        }

        if (!is_null($grupo_cuenta_id)) {
            $arr_ids_cuentas_del_grupo = ContabCuenta::where('contab_cuenta_grupo_id',$grupo_cuenta_id)->get()->pluck('id')->toArray();
            
            return ContabMovimiento::whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                                ->where($array_wheres)
                                ->whereIn('contab_cuenta_id',$arr_ids_cuentas_del_grupo)
                                ->orderBy('fecha')
                                ->get();
        }

        if (!is_null($cuenta_id)) {
            $array_wheres = array_merge($array_wheres, ['contab_cuenta_id' => $cuenta_id]);
        }

        return ContabMovimiento::whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->where($array_wheres)
            ->orderBy('fecha')
            ->get();
    }

    public static function get_saldo_movimiento_clase_cuenta($fecha_desde, $fecha_hasta, $clase_cuenta_id )
    {
        $array_wheres = [
            ['contab_movimientos.core_empresa_id', '=', Auth::user()->empresa_id]
        ];

        if( !is_null($clase_cuenta_id) )
        {
            $array_wheres = array_merge($array_wheres, ['contab_cuentas.contab_cuenta_clase_id' => $clase_cuenta_id]);
        }

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
                                        ->whereBetween('contab_movimientos.fecha', [$fecha_desde, $fecha_hasta])
                                        ->where($array_wheres)
                                        ->selectRaw('sum(contab_movimientos.valor_saldo) AS valor_saldo, contab_movimientos.contab_cuenta_id')
                                        ->groupBy('contab_cuentas.id')
                                        ->get();
    }



    public static function get_movimiento_cuenta($fecha_inicial, $fecha_final, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $empresa_id)
    {
        return ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
            ->where('contab_movimientos.fecha', '>=', $fecha_inicial)
            ->where('contab_movimientos.fecha', '<=', $fecha_final)
            ->where('contab_cuentas.codigo', 'LIKE', $contab_cuenta_id)
            ->where('core_terceros.numero_identificacion', 'LIKE', $numero_identificacion)
            ->where('contab_movimientos.codigo_referencia_tercero', $operador, $codigo_referencia_tercero)
            ->where('contab_movimientos.core_empresa_id', '=', $empresa_id)
            ->select(
                'contab_movimientos.fecha',
                'contab_movimientos.detalle_operacion',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS documento'),
                'core_terceros.descripcion as tercero',
                'contab_movimientos.valor_debito AS debito',
                'contab_movimientos.valor_credito AS credito',
                'contab_movimientos.codigo_referencia_tercero',
                'contab_movimientos.core_tercero_id',
                'contab_movimientos.core_empresa_id',
                'contab_movimientos.core_tipo_transaccion_id',
                'contab_movimientos.core_tipo_doc_app_id',
                'contab_movimientos.consecutivo',
                'contab_movimientos.documento_soporte',
                'contab_movimientos.detalle_operacion'
            )
            ->orderBy('fecha')
            ->get()
            ->toArray();
    }

    public static function get_movimiento_arbol_grupo_cuenta($empresa_id, $fecha_inicial, $fecha_final, $grupo_abuelo_id, $tipo_reporte)
    {
        if ( $tipo_reporte )
        {
            return ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                    ->leftJoin('contab_arbol_grupos_cuentas', 'contab_arbol_grupos_cuentas.hijo_id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
                    ->where('contab_movimientos.core_empresa_id', $empresa_id)
                    ->whereBetween( 'contab_movimientos.fecha', [ $fecha_inicial, $fecha_final])
                    ->where('contab_arbol_grupos_cuentas.abuelo_id', $grupo_abuelo_id)
                    ->groupBy('contab_movimientos.contab_cuenta_id')
                    ->selectRaw('sum(contab_movimientos.valor_saldo) AS valor_saldo, contab_arbol_grupos_cuentas.abuelo_descripcion, contab_arbol_grupos_cuentas.padre_descripcion, contab_arbol_grupos_cuentas.hijo_descripcion, contab_arbol_grupos_cuentas.abuelo_id, contab_arbol_grupos_cuentas.padre_id, contab_arbol_grupos_cuentas.hijo_id, contab_cuentas.descripcion AS cuenta_descripcion, contab_cuentas.id AS cuenta_id, contab_cuentas.codigo AS cuenta_codigo')
                    ->get()
                    ->toArray();
        }

        return ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                    ->leftJoin('contab_arbol_grupos_cuentas', 'contab_arbol_grupos_cuentas.hijo_id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
                    ->where('contab_movimientos.core_empresa_id', $empresa_id)
                    ->where('contab_movimientos.fecha', '<=', $fecha_final)
                    ->where('contab_arbol_grupos_cuentas.abuelo_id', $grupo_abuelo_id)
                    ->groupBy('contab_movimientos.contab_cuenta_id')
                    ->selectRaw('sum(contab_movimientos.valor_saldo) AS valor_saldo, contab_arbol_grupos_cuentas.abuelo_descripcion, contab_arbol_grupos_cuentas.padre_descripcion, contab_arbol_grupos_cuentas.hijo_descripcion, contab_arbol_grupos_cuentas.abuelo_id, contab_arbol_grupos_cuentas.padre_id, contab_arbol_grupos_cuentas.hijo_id, contab_cuentas.descripcion AS cuenta_descripcion, contab_cuentas.id AS cuenta_id, contab_cuentas.codigo AS cuenta_codigo')
                    ->get()
                    ->toArray();
    }

    public static function get_registros_contables($core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo)
    {
        return ContabMovimiento::leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
            ->where('contab_movimientos.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->where('contab_movimientos.core_tipo_doc_app_id', $core_tipo_doc_app_id)
            ->where('contab_movimientos.consecutivo', $consecutivo)
            ->groupBy('contab_movimientos.contab_cuenta_id')
            ->selectRaw('contab_cuentas.descripcion AS cuenta_descripcion, contab_cuentas.codigo AS cuenta_codigo, sum(contab_movimientos.valor_debito) AS valor_debito, sum(contab_movimientos.valor_credito) AS valor_credito, CONCAT(core_terceros.numero_identificacion," ",core_terceros.descripcion) AS tercero')
            ->get()->toArray();
    }

    public static function get_movimiento_impuestos($vec_tipos_transaccion_ids, $fecha_inicial, $fecha_final, $nivel_detalle)
    {
        /**/
        switch ($nivel_detalle) {
            case 'ninguno':
                $nivel_detalle = ['contab_impuestos.id'];

                break;

            case 'cuentas':
                $nivel_detalle = ['contab_impuestos.id', 'contab_movimientos.contab_cuenta_id'];

                break;

            case 'productos':
                $nivel_detalle = ['contab_impuestos.id', 'inv_productos.id'];

                break;

            default:
                # code...
                break;
        }


        return ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
            ->leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'contab_movimientos.core_tipo_transaccion_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'contab_movimientos.inv_producto_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->whereIn('contab_movimientos.core_tipo_transaccion_id', $vec_tipos_transaccion_ids)
            ->where('contab_movimientos.fecha', '>=', $fecha_inicial)
            ->where('contab_movimientos.fecha', '<=', $fecha_final)
            ->groupBy($nivel_detalle)
            ->selectRaw(
                'sys_tipos_transacciones.descripcion AS transaccion_descripcion,
                                contab_impuestos.descripcion AS impuesto_descripcion,
                                contab_impuestos.tasa_impuesto AS impuesto_tasa,
                                contab_cuentas.descripcion AS cuenta_descripcion,
                                contab_cuentas.codigo AS cuenta_codigo,
                                inv_productos.descripcion AS producto_descripcion,
                                inv_productos.unidad_medida1 AS producto_unidad_medida,
                                contab_movimientos.tasa_impuesto AS movimiento_tasa,
                                sum(contab_movimientos.valor_debito) AS valor_debito,
                                sum(contab_movimientos.valor_credito) AS valor_credito'
            )
            ->orderBy('sys_tipos_transacciones.id')
            ->orderBy('contab_impuestos.id')
            ->get();
    }

    public function contabilizar_linea_registro( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito )
    {
        ContabMovimiento::create( 
                                $datos + 
                                [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                                [ 'detalle_operacion' => $detalle_operacion] + 
                                [ 'valor_debito' => $valor_debito] + 
                                [ 'valor_credito' => ($valor_credito * -1) ] + 
                                [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ]
                            );
    }
}