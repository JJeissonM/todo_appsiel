<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use Input;

use App\Inventarios\InvDocEncabezado;
use App\Core\EncabezadoDocumentoTransaccion;

use App\Contabilidad\ContabMovimiento;
use App\CxC\CxcMovimiento;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Inventarios\InvProducto;
use App\Inventarios\InvMovimiento;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\Core\ModeloEavValor;
use App\Ventas\CondicionPago;

use App\Matriculas\FacturaAuxEstudiante;

class VtasDocEncabezado extends Model
{
    //protected $table = 'vtas_doc_encabezados'; 

    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'contacto_cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'valor_total'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Detalle', 'Valor total', 'Forma de pago', 'Estado'];

    public $vistas = '{"index":"layouts.index3"}';

    public function tipo_transaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo('App\Core\TipoDocApp', 'core_tipo_doc_app_id');
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function contacto_cliente()
    {
        return $this->belongsTo(ContactoCliente::class, 'contacto_cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany(VtasDocRegistro::class, 'vtas_doc_encabezado_id');
    }

    public function movimientos()
    {
        return $this->hasMany(VtasMovimiento::class);
    }

    // Doc. desde el cual fue generado
    public function documento_ventas_padre()
    {
        $doc_padre = VtasDocEncabezado::find( $this->ventas_doc_relacionado_id );
        
        if ( is_null( $doc_padre ) )
        {
            return null;
        }

        return $doc_padre;
    }

    // Doc. que se generó a partir de este
    public function documento_ventas_hijo()
    {
        $doc_hijo = VtasDocEncabezado::where( 'ventas_doc_relacionado_id', $this->id )->get()->first();
        
        if ( is_null( $doc_hijo ) )
        {
            return null;
        }

        return $doc_hijo;
    }

    public function enlaces_remisiones_hijas()
    {
        $remisiones = $this->remisiones_hijas();
        $lista = '';
        $es_el_primero = true;
        foreach ($remisiones as $remision )
        {
            if ( $es_el_primero )
            {
                $lista = $remision->enlace_show_documento();
                $es_el_primero = false;
            }else{
                $lista .= ', ' . $remision->enlace_show_documento();
            }
        }
        return $lista;
    }

    public function remisiones_hijas()
    {
        return InvDocEncabezado::where( 'vtas_doc_encabezado_origen_id', $this->id )->get();
    }

    public function enlace_show_documento()
    {
        switch ( $this->core_tipo_transaccion_id )
        {
            case '23':
                $url = 'ventas/';
                break;
            
            
            case '42':
                $url = 'vtas_pedidos/';
                break;
            
            
            case '30':
                $url = 'vtas_cotizacion/';
                break;
            
            default:
                # code...
                break;
        }

        $enlace = '<a href="' . url( $url . $this->id . '?id=' . Input::get('id') . '&id_modelo=' . $this->tipo_transaccion->core_modelo_id . '&id_transaccion=' . $this->core_tipo_transaccion_id ) . '" target="_blank">' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo . '</a>';

        return $enlace;
    }

    public function texto_condicion_venta()
    {
        $registro_eav = ModeloEavValor::where( [ 
                                                "modelo_padre_id" => 155,
                                                "registro_modelo_padre_id" => $this->id,
                                                "modelo_entidad_id" => 0,
                                                "core_campo_id" => 1266
                                            ] )
                                    ->get()
                                    ->first();
        if ( is_null($registro_eav) )
        {
            return '';
        }

        return CondicionPago::find( $registro_eav->valor )->descripcion;
    }

    public function clonar_encabezado( $fecha, $core_tipo_transaccion_id, $core_tipo_doc_app_id, $descripcion, $modelo_id )
    {
        $datos = $this->toArray();

        if ( !is_null( $fecha ) )
        {
            $datos['fecha'] = $fecha;
        }

        if ( !is_null( $core_tipo_transaccion_id ) )
        {
            $datos['core_tipo_transaccion_id'] = $core_tipo_transaccion_id;
        }

        if ( !is_null( $core_tipo_doc_app_id ) )
        {
            $datos['core_tipo_doc_app_id'] = $core_tipo_doc_app_id;
        }

        if ( !is_null( $descripcion ) )
        {
            $datos['descripcion'] = $descripcion;
        }

        $datos['consecutivo'] = 0;
        $datos['id'] = 0;
        
        $encabezado_transaccion = new EncabezadoDocumentoTransaccion( $modelo_id );

        return $encabezado_transaccion->crear_nuevo( $datos );
    }

    public function clonar_lineas_registros( $vtas_doc_encabezado_id )
    {
        $lineas_registros = $this->lineas_registros;

        foreach ($lineas_registros as $linea)
        {
            $datos = $linea->toArray();
            $datos['vtas_doc_encabezado_id'] = $vtas_doc_encabezado_id;
            $datos['creado_por'] = 'paula@appsiel.com.co';
            if(Auth::user()){
                $datos['creado_por'] = Auth::user()->email;
            }   
            $datos['modificado_por'] = '';

            VtasDocRegistro::create( $datos );
        }
    }

    public function crear_movimiento_ventas()
    {
        $lineas_registros = $this->lineas_registros;
        foreach ($lineas_registros as $linea)
        {
            $datos = $this->toArray() + $linea->toArray();

            // Movimiento de Ventas
            $datos['estado'] = 'Activo';

            VtasMovimiento::create($datos);
        }
    }

    public function determinar_posibles_existencias_negativas()
    {
        $lineas_registros = $this->lineas_registros;
        foreach ($lineas_registros as $linea)
        {
            $existencia_actual = InvMovimiento::get_existencia_actual( $linea->inv_producto_id, $this->cliente->inv_bodega_id, $this->fecha );

            if ( ( $existencia_actual - abs($linea->cantidad) ) < 0 )
            {
                return 1;
            }
        }
        return 0;
    }

    public function datos_auxiliares_estudiante()
    {
        return $this->hasOne(FacturaAuxEstudiante::class, 'vtas_doc_encabezado_id');
    }

    public function contabilizar_movimiento_debito( $caja_banco_id = null )
    {
        $datos = $this->toArray();
        $datos['registros_medio_pago'] = [];

        $movimiento_contable = new ContabMovimiento();
        $detalle_operacion = 'Contabilización ' . $this->tipo_transaccion->descripcion . ' ' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;

        if ( $this->forma_pago == 'credito')
        {
            // La cuenta de CARTERA se toma de la clase del cliente
            $cta_x_cobrar_id = Cliente::get_cuenta_cartera( $this->cliente_id );
            $datos['tipo_transaccion'] = 'cxc';
            $movimiento_contable->contabilizar_linea_registro( $datos, $cta_x_cobrar_id, $detalle_operacion, $this->valor_total, 0);
        }
        
        // Agregar el movimiento a tesorería
        if ( $this->forma_pago == 'contado')
        {
            if( is_null( $caja_banco_id ) )
            {
                if ( empty( $datos['registros_medio_pago'] ) )
                {   
                    // Por defecto
                    $caja = TesoCaja::get()->first();
                    $teso_caja_id = $caja->id;
                    $teso_cuenta_bancaria_id = 0;
                    $contab_cuenta_id = $caja->contab_cuenta_id;
                }else{

                    // WARNING!!! Por ahora solo se está aceptando un solo medio de pago
                    $contab_cuenta_id = TesoCaja::find( 1 )->contab_cuenta_id;

                    $teso_caja_id = $datos['registros_medio_pago']['teso_caja_id'];
                    if ($teso_caja_id != 0)
                    {
                        $contab_cuenta_id = TesoCaja::find( $teso_caja_id )->contab_cuenta_id;
                    }

                    $teso_cuenta_bancaria_id = $datos['registros_medio_pago']['teso_cuenta_bancaria_id'];
                    if ($teso_cuenta_bancaria_id != 0)
                    {
                        $contab_cuenta_id = TesoCuentaBancaria::find( $teso_cuenta_bancaria_id )->contab_cuenta_id;
                    }                    
                }
            }else{
                // $caja_banco_id se manda desde Ventas POS
                $caja = TesoCaja::find( $caja_banco_id );
                $teso_caja_id = $caja->id;
                $teso_cuenta_bancaria_id = 0;
                $contab_cuenta_id = $caja->contab_cuenta_id;
            }
            
            $datos['teso_caja_id'] = $teso_caja_id;
            $datos['teso_cuenta_bancaria_id'] = $teso_cuenta_bancaria_id;
            $datos['tipo_transaccion'] = 'recaudo';
            $movimiento_contable->contabilizar_linea_registro( $datos, $contab_cuenta_id, $detalle_operacion, $this->valor_total, 0 );
        }
    }

    // Contabilizar Ingresos de ventas e Impuestos
    public function contabilizar_movimiento_credito()
    {
        $datos = $this->toArray();
        $detalle_operacion = 'Contabilización ' . $this->tipo_transaccion->descripcion . ' ' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;

        $lineas_registros = $this->lineas_registros;
        foreach ($lineas_registros as $linea)
        {
            $una_linea_registro = $datos + $linea->toArray();
            $una_linea_registro['creado_por'] = 'paula@appsiel.com.co';
            if(Auth::user()){
                $una_linea_registro['creado_por']  = Auth::user()->email;
            }   
            $una_linea_registro['modificado_por'] = '';
            $una_linea_registro['estado'] = 'Activo';
            $una_linea_registro['tipo_transaccion'] = 'facturacion_ventas';

            $movimiento_contable = new ContabMovimiento();

            // IVA generado (CR)
            // Si se ha liquidado impuestos en la transacción
            $valor_total_impuesto = 0;
            if ( $una_linea_registro['tasa_impuesto'] > 0 )
            {
                $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_ventas( $una_linea_registro['inv_producto_id'] );
                $valor_total_impuesto = abs( $una_linea_registro['valor_impuesto'] * $una_linea_registro['cantidad'] );

                $movimiento_contable->contabilizar_linea_registro( $una_linea_registro, $cta_impuesto_ventas_id, $detalle_operacion, 0, abs($valor_total_impuesto) );
            }

            // Contabilizar Ingresos (CR)
            // La cuenta de ingresos se toma del grupo de inventarios
            $cta_ingresos_id = InvProducto::get_cuenta_ingresos( $una_linea_registro['inv_producto_id'] );
            $movimiento_contable->contabilizar_linea_registro( $una_linea_registro, $cta_ingresos_id, $detalle_operacion, 0, $una_linea_registro['base_impuesto_total'] );
        }                
    }

    /*
        Movimiento de Tesoreria o Cartera de clientes (CxC)
    */
    public function crear_registro_pago()
    {
        $datos = $this->toArray();
        $detalle_operacion = $this->tipo_transaccion->descripcion . ' ' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
        $datos['registros_medio_pago'] = [];
        
        // Cargar la cuenta por cobrar (CxC)
        if ( $this->forma_pago == 'credito')
        {
            $datos['modelo_referencia_tercero_index'] = 'App\Ventas\Cliente';
            $datos['referencia_tercero_id'] = $this->cliente_id;
            $datos['valor_documento'] = $this->valor_total;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $this->valor_total;
            $datos['estado'] = 'Pendiente';
            CxcMovimiento::create( $datos );
        }

        if ( $this->forma_pago == 'contado')
        {
            $teso_movimiento = new TesoMovimiento();
            $teso_movimiento->almacenar_registro_pago_contado( $datos, $datos['registros_medio_pago'], 'entrada', $this->valor_total );
        }
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 23; // Facturas
        $string = VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS FECHA'),
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('core_terceros.descripcion AS CLIENTE'),
                'vtas_doc_encabezados.descripcion AS DETALLE',
                'vtas_doc_encabezados.valor_total AS VALOR_TOTAL',
                'vtas_doc_encabezados.forma_pago AS FORMA_DE_PAGO',
                'vtas_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("vtas_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.forma_pago", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_doc_encabezados.fecha', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CLIENTES";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 23; // Facturas

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

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {
        // ARREGLAR ESTO:     ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','vtas_doc_encabezados.condicion_pago_id')
        return VtasDocEncabezado::where('vtas_doc_encabezados.id', $id)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_doc_encabezados', 'inv_doc_encabezados.id', '=', 'vtas_doc_encabezados.remision_doc_encabezado_id')
            ->leftJoin('core_tipos_docs_apps AS doc_inventarios', 'doc_inventarios.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->select(
                'vtas_doc_encabezados.id',
                'vtas_doc_encabezados.core_empresa_id',
                'vtas_doc_encabezados.core_tercero_id',
                'vtas_doc_encabezados.cliente_id',
                'vtas_doc_encabezados.contacto_cliente_id',
                'vtas_doc_encabezados.remision_doc_encabezado_id',
                'vtas_doc_encabezados.core_tipo_transaccion_id',
                'vtas_doc_encabezados.core_tipo_doc_app_id',
                'vtas_doc_encabezados.consecutivo',
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS fecha'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_vencimiento,"%d-%m-%Y") AS fecha_vencimiento'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS fecha_entrega'),
                'vtas_doc_encabezados.vendedor_id',
                'vtas_doc_encabezados.descripcion',
                'vtas_doc_encabezados.estado',
                'vtas_doc_encabezados.creado_por',
                'vtas_doc_encabezados.modificado_por',
                'vtas_doc_encabezados.created_at',
                'vtas_doc_encabezados.orden_compras',
                'vtas_doc_encabezados.ventas_doc_relacionado_id',
                'vtas_doc_encabezados.forma_pago AS forma_pago',
                'vtas_doc_encabezados.forma_pago AS condicion_pago',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                'vtas_doc_encabezados.consecutivo AS documento_transaccion_consecutivo',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
                DB::raw('CONCAT(doc_inventarios.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_remision_prefijo_consecutivo'),
                DB::raw('core_terceros.descripcion AS tercero_nombre_completo'),
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1'
            )
            ->get()
            ->first();
    }

    public static function get_documentos_relacionados($doc_encabezado)
    {
        $mas_de_uno = false;
        $ids_documentos_relacionados = explode(',', $doc_encabezado->remision_doc_encabezado_id);

        $app_id = 13; // Ventas
        $modelo_doc_relacionado_id = 164; // Remisiones de ventas
        $transaccion_doc_relacionado_id = 24; // Remisión de ventas

        $cant_registros = count($ids_documentos_relacionados);

        $lista = '';
        $primer = true;
        for ($i = 0; $i < $cant_registros; $i++) {
            $un_documento = InvDocEncabezado::get_registro_impresion($ids_documentos_relacionados[$i]);
            if (!is_null($un_documento)) {
                if ($primer) {
                    $lista .= '<a href="' . url('inventarios/' . $un_documento->id . '?id=' . $app_id . '&id_modelo=' . $modelo_doc_relacionado_id . '&id_transaccion=' . $transaccion_doc_relacionado_id) . '" target="_blank">' . $un_documento->documento_transaccion_prefijo_consecutivo . '</a>';
                    $primer = false;
                } else {
                    $lista .= ', &nbsp; <a href="' . url('inventarios/' . $un_documento->id . '?id=' . $app_id . '&id_modelo=' . $modelo_doc_relacionado_id . '&id_transaccion=' . $transaccion_doc_relacionado_id) . '" target="_blank">' . $un_documento->documento_transaccion_prefijo_consecutivo . '</a>';
                    $mas_de_uno = true;
                }
            }
        }
        return [$lista, $mas_de_uno];
    }
}
