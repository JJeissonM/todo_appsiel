<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\Core\EncabezadoDocumentoTransaccion;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\Contabilidad\ContabMovimiento;

class TesoDocEncabezadoRecaudoCxc extends TesoDocEncabezado
{
    // Apunta a la misma tabla del modelo de Recaudos
    protected $table = 'teso_doc_encabezados'; 

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Tercero', 'Detalle', 'Valor Documento', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $transaccion_id = 32;

        $collection = TesoDocEncabezadoRecaudoCxc::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                                    ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                    ->where('teso_doc_encabezados.core_tipo_transaccion_id', $transaccion_id)
                                    ->select(
                                        'teso_doc_encabezados.fecha AS campo1',
                                        DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo2'),
                                        DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
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
        $transaccion_id = 32;
        $string = TesoDocEncabezadoRecaudoCxc::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('teso_doc_encabezados.core_tipo_transaccion_id', $transaccion_id)
            ->select(
                'teso_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS TERCERO'),
                'teso_doc_encabezados.descripcion AS DETALLE',
                'teso_doc_encabezados.valor_total AS VALOR_DOCUMENTO',
                'teso_doc_encabezados.estado AS ESTADO'
            )
            ->where("teso_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
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
        return "LISTADO DE RECAUDOS DE CXC";
    }

    public function crear_documento( $request, $modelo_id)
    {
        $request['core_tipo_transaccion_id'] = (int)config('tesoreria.recaudo_tipo_transaccion_id');
        $request['core_tipo_doc_app_id'] = (int)config('tesoreria.recaudo_tipo_doc_app_id');
        $request['creado_por'] = Auth::user()->email;
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $modelo_id );
        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

        $total_abonos_cxc = $doc_encabezado->almacenar_registros_cxc( $request );

        return $doc_encabezado->id;
    }

    /*
        Se almacenas lineas de registro por cada tabla de medios de pago enviada
    */
    public function almacenar_registros_cxc( $request )
    {
        $lineas_registros = json_decode($request->lineas_registros);

        array_pop($lineas_registros);

        $total_abonos_cxc = 0;
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $abono = (float)$lineas_registros[$i]->abono;
            $registro_documento_pendiente = CxcMovimiento::find( (int)$lineas_registros[$i]->id_doc );
            
            // Almacenar registro de abono
            $datos = ['core_tipo_transaccion_id' => $this->core_tipo_transaccion_id]+
                        ['core_tipo_doc_app_id' => $this->core_tipo_doc_app_id]+
                        ['consecutivo' => $this->consecutivo]+
                        ['core_empresa_id' => $this->core_empresa_id]+
                        ['core_tercero_id' => $this->core_tercero_id]+
                        ['modelo_referencia_tercero_index' => $registro_documento_pendiente->modelo_referencia_tercero_index]+
                        ['referencia_tercero_id' => $registro_documento_pendiente->referencia_tercero_id]+
                        ['fecha' => $this->fecha]+
                        ['doc_cxc_transacc_id' => $registro_documento_pendiente->core_tipo_transaccion_id]+
                        ['doc_cxc_tipo_doc_id' => $registro_documento_pendiente->core_tipo_doc_app_id]+
                        ['doc_cxc_consecutivo' => $registro_documento_pendiente->consecutivo]+
                        ['abono' => $abono]+
                        ['creado_por' => $this->creado_por];

            CxcAbono::create( $datos );

            // CONTABILIZAR
            $detalle_operacion = 'Abono factura de cliente';

            // 1.2. Para cada registro del documento, también se va actualizando el movimiento de contabilidad

            // MOVIMIENTO CREDITO: Cartera Cuenta por pagar. Cada Documento pagado puede tener cuenta por pagar distinta.
            // Del movimiento contable, Se llama al ID de la cuenta (moviento DB) afectada por el documento cxc
            $cta_x_cobrar_id = ContabMovimiento::where('core_tipo_transaccion_id',$registro_documento_pendiente->core_tipo_transaccion_id)
                                                ->where('core_tipo_doc_app_id',$registro_documento_pendiente->core_tipo_doc_app_id)
                                                ->where('consecutivo',$registro_documento_pendiente->consecutivo)
                                                ->where('core_tercero_id',$registro_documento_pendiente->core_tercero_id)
                                                ->where('valor_credito',0)
                                                ->value('contab_cuenta_id');

            if( is_null( $cta_x_cobrar_id ) )
            {
                $cta_x_cobrar_id = config('configuracion.cta_cartera_default');
            }
            
            $movimiento_contable = new ContabMovimiento();
            $detalle_operacion = 'Contabilización ' . $this->tipo_transaccion->descripcion . ' ' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;

            $movimiento_contable->contabilizar_linea_registro( array_merge( $request->all(), [ 'consecutivo' => $this->consecutivo, 'tipo_transaccion' => '' ] ), $cta_x_cobrar_id, $detalle_operacion, 0, $abono );

            //ContabilidadController::contabilizar_registro2( array_merge( $request->all(), [ 'consecutivo' => $this->consecutivo ] ), $cta_x_cobrar_id, $detalle_operacion, 0, $abono);

            // Se diminuye el saldo_pendiente en el documento pendiente, si saldo_pendiente == 0 se marca como pagado
            $registro_documento_pendiente->actualizar_saldos($abono);

            $total_abonos_cxc += $abono;
        }

        return $total_abonos_cxc;
    }
}
