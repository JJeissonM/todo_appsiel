<?php

namespace App\Tesoreria;

use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;

use App\Contabilidad\ContabMovimiento;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TesoDocEncabezadoTraslado extends TesoDocEncabezado
{
    // Apunta a la misma tabla del modelo de Recaudos
    protected $table = 'teso_doc_encabezados';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'codigo_referencia_tercero', 'teso_tipo_motivo', 'documento_soporte', 'descripcion', 'teso_medio_recaudo_id', 'teso_caja_id', 'teso_cuenta_bancaria_id', 'valor_total', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Fecha', 'Tercero', 'Detalle', 'Valor total', 'Estado'];

    public $vistas = '{"create":"tesoreria.traslados_efectivo.create"}';

    public function tipo_transaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo('App\Core\TipoDocApp', 'core_tipo_doc_app_id');
    }

    public function caja()
    {
        return $this->belongsTo(TesoCaja::class, 'teso_caja_id');
    }

    public function cuenta_bancaria()
    {
        return $this->belongsTo(TesoCuentaBancaria::class, 'teso_cuenta_bancaria_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany( TesoDocRegistro::class, 'teso_encabezado_id');
    }

    public function recontabilizar()
    {
        // Eliminar ontabilizacion anterior
        ContabMovimiento::where([
                                    ['core_tipo_transaccion_id', $this->core_tipo_transaccion_id],
                                    ['core_tipo_doc_app_id', $this->core_tipo_doc_app_id],
                                    ['consecutivo', $this->consecutivo]
                                ])
                        ->delete();

        $lineas_registros = $this->lineas_registros;
        foreach( $lineas_registros AS $linea_registro )
        {
            if ($linea_registro->teso_caja_id != 0)
            {
                $contab_cuenta_id = $linea_registro->caja->contab_cuenta_id;
            }

            if ($linea_registro->teso_cuenta_bancaria_id != 0)
            {
                $contab_cuenta_id = $linea_registro->cuenta_bancaria->contab_cuenta_id;
            }

            if ( $linea_registro->motivo->movimiento == 'entrada' )
            {
                $valor_debito = $linea_registro->valor;
                $valor_credito = 0;
            }else{
                $valor_debito = 0;
                $valor_credito = $linea_registro->valor * -1;
            }

            $datos = $this->toArray();
            $datos['tipo_transaccion'] = 'causacion_tesoreria';
            $datos['teso_caja_id'] = $linea_registro->teso_caja_id;
            $datos['teso_cuenta_bancaria_id'] = $linea_registro->teso_cuenta_bancaria_id;

            $movimiento_contable = new ContabMovimiento();
            $detalle_operacion = 'Recontabilización ' . $this->tipo_transaccion->descripcion . ' ' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
            $movimiento_contable->contabilizar_linea_registro( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);
        }
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 43;

        $collection = TesoDocEncabezadoPago::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
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
        $core_tipo_transaccion_id = 43;
        $string = TesoDocEncabezadoPago::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('teso_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'teso_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS TERCERO'),
                'teso_doc_encabezados.descripcion AS DETALLE',
                'teso_doc_encabezados.valor_total AS VALOR_TOTAL',
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
        return "LISTADO DE PAGOS";
    }


    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS documento';

        return TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.id', $id)
            ->select(DB::raw($select_raw), 'teso_doc_encabezados.fecha', 'core_terceros.descripcion AS tercero', 'teso_doc_encabezados.descripcion AS detalle', 'teso_doc_encabezados.documento_soporte', 'teso_doc_encabezados.core_tipo_transaccion_id', 'teso_doc_encabezados.core_tipo_doc_app_id', 'teso_doc_encabezados.id', 'teso_doc_encabezados.creado_por', 'teso_doc_encabezados.consecutivo', 'teso_doc_encabezados.core_empresa_id', 'teso_doc_encabezados.core_tercero_id', 'teso_doc_encabezados.teso_tipo_motivo')
            ->get()[0];
    }

    public function store_adicional($datos, $registro)
    {
        $datos['consecutivo'] = $registro->consecutivo;

        $registros = json_decode($datos['lineas_registros']);
        $total = 0;
        foreach ($registros as $item)
        {
            $motivo = explode('-', $item->teso_motivo_id);
            $aux = TesoMotivo::where([['teso_tipo_motivo', 'traslado-efectivo'], ['movimiento', $motivo[0]]])->first();
            $medio_recaudo = explode('-', $item->teso_medio_recaudo_id);
            $caja = explode('-', $item->teso_caja_id);
            $cuenta = explode('-', $item->teso_cuenta_bancaria_id);
            $valor = explode('$', $item->valor);

            $teso_registro = new TesoDocRegistro();
            $teso_registro->teso_encabezado_id = $registro->id;
            $teso_registro->teso_motivo_id = $aux->id;
            $teso_registro->teso_cuenta_bancaria_id = $cuenta[0];
            $teso_registro->core_tercero_id = $registro->core_tercero_id;
            if ($medio_recaudo[1] != 'Tarjeta bancaria')
            {
                $teso_registro->teso_caja_id = $caja[0];
                $teso_registro->teso_cuenta_bancaria_id = 0;
            } else {
                $teso_registro->teso_caja_id = 0;
                $teso_registro->teso_cuenta_bancaria_id = $cuenta[0];
            }
            $teso_registro->teso_medio_recaudo_id = $medio_recaudo[0];
            $teso_registro->valor = $valor[1];
            $teso_registro->estado = 'Activo';
            $teso_registro->detalle_operacion = 0;
            $total += abs($teso_registro->valor);
            $result = $teso_registro->save();
            if ($result)
            {
                $movimiento = new TesoMovimiento();
                $movimiento->fecha = $registro->fecha;
                $movimiento->core_empresa_id = $registro->core_empresa_id;
                $movimiento->core_tercero_id = $registro->core_tercero_id;
                $movimiento->core_tipo_transaccion_id = $registro->core_tipo_transaccion_id;
                $movimiento->core_tipo_doc_app_id = $registro->core_tipo_doc_app_id;
                $movimiento->teso_motivo_id = $teso_registro->teso_motivo_id;
                $movimiento->teso_caja_id = $teso_registro->teso_caja_id;
                $movimiento->teso_cuenta_bancaria_id = $teso_registro->teso_cuenta_bancaria_id;
                $movimiento->valor_movimiento = $teso_registro->valor;
                $movimiento->consecutivo = $registro->consecutivo;
                $movimiento->estado = 'Activo';
                $movimiento->creado_por = $registro->creado_por;
                $movimiento->save();
            }

            /*
                **  Determinar la cuenta contable DB (CAJA O BANCOS)
            */
            if ($teso_registro->teso_caja_id != 0)
            {
                $sql_contab_cuenta_id = TesoCaja::find($teso_registro->teso_caja_id);
                $contab_cuenta_id = $sql_contab_cuenta_id->contab_cuenta_id;
            }

            if ($teso_registro->teso_cuenta_bancaria_id != 0) {
                $sql_contab_cuenta_id = TesoCuentaBancaria::find($teso_registro->teso_cuenta_bancaria_id);
                $contab_cuenta_id = $sql_contab_cuenta_id->contab_cuenta_id;
            }

            $detalle_operacion = $datos['descripcion'];
            $motivo = TesoMotivo::find($teso_registro->teso_motivo_id);

            if ( $motivo->movimiento == 'entrada' )
            {
                $valor_debito = $teso_registro->valor;
                $valor_credito = 0;
            }else{
                $valor_debito = 0;
                $valor_credito = $teso_registro->valor * -1;
            }

            $this->contabilizar_registro($datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);
        }

        $registro->valor_total = $total / 2;
        $registro->estado = 'Activo';
        $registro->save();

        return redirect('web' . '?id=' . $datos['url_id'] . '&id_modelo=' . $datos['url_id_modelo'])->with('flash_message', 'Registro CREADO correctamente.');
    }



    public function contabilizar_registro($datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0)
    {
        ContabMovimiento::create(
            $datos +
                ['contab_cuenta_id' => $contab_cuenta_id] +
                ['detalle_operacion' => $detalle_operacion] +
                ['valor_debito' => $valor_debito] +
                ['valor_credito' => ($valor_credito * -1)] +
                ['valor_saldo' => ($valor_debito - $valor_credito)] +
                ['teso_caja_id' => $teso_caja_id] +
                ['teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id]
        );
    }
}
