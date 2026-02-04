<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use App\Inventarios\InvDocEncabezado;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\ResolucionFacturacion;

use Illuminate\Pagination\LengthAwarePaginator;

use App\Core\ModeloEavValor;
use App\Ventas\CondicionPago;

use App\VentasPos\FacturaPos;

use App\Matriculas\FacturaAuxEstudiante;
use App\Traits\FiltraRegistrosPorUsuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;

class VtasDocEncabezado extends Model
{
    use FiltraRegistrosPorUsuario;
    //protected $table = 'vtas_doc_encabezados'; 

    protected $fillable = [ 'core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'contacto_cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'hora_entrega', 'plazo_entrega_id', 'fecha_vencimiento', 'orden_compras', 'valor_total', 'total_efectivo_recibido','valor_ajuste_al_peso','valor_total_cambio', 'valor_total_bolsas', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Valor ingresos (sin iva)', 'Total factura', 'Forma de pago', 'Estado'];

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

    public function plazo_entrega()
    {
        return $this->belongsTo( PlazoEntrega::class, 'plazo_entrega_id' );
    }

    public function lineas_registros()
    {
        return $this->hasMany(VtasDocRegistro::class, 'vtas_doc_encabezado_id');
    }

    public function movimientos()
    {
        return $this->hasMany(VtasMovimiento::class);
    }

    public function actualizar_valor_total()
    {
        $this->valor_total = $this->lineas_registros->sum('precio_total');
        $this->save();
    }

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    } 

    public function get_link_pedido()
    {
        return '<a href="' . url('/') . '/vtas_pedidos/' . $this->id . '?id=13&id_modelo=175&id_transaccion=42" target="_blank"> ' . $this->get_label_documento() . ' </a>';
    }

    // Doc. desde el cual fue generado
    public function documento_ventas_padre()
    {
        /**
         * NOTA 1: Se puede dar el Caso en que el ID de registro en FacturaPos sea igual al ID de un Pedido (VtasDocEncabezado). Por tanto al buscar el doc_padre en VtasDocEncabezado arrojará el registro de un Pedido cualquiera ($doc_padre != null) y no se buscará la factura real en FacturaPos. Para esto se busca nuevamente en FacturaPos cuando la transaccion de Pedido es igual a la transaccion del doc_padre
         */
        $doc_padre = VtasDocEncabezado::find( $this->ventas_doc_relacionado_id );
        
        if ( $doc_padre == null )
        {
            if ( !Schema::hasTable( 'vtas_pos_doc_encabezados' ) )
            {
                return null;
            }

            $doc_padre = FacturaPos::find( $this->ventas_doc_relacionado_id );
        
            if ( $doc_padre == null )
            {
                return null;
            }
        }

        if ($doc_padre->core_tipo_transaccion_id == $this->core_tipo_transaccion_id)
        {
            if ( !Schema::hasTable( 'vtas_pos_doc_encabezados' ) )
            {
                return null;
            }

            // Buscar nuevamente en FacturaPos cuando la transaccion de Pedido es igual a la transaccion del doc_padre
            $doc_padre = FacturaPos::find( $this->ventas_doc_relacionado_id );
        
            if ( $doc_padre == null )
            {
                return null;
            }
        }

        return $doc_padre;
    }

    // Doc. que se generó a partir de este
    public function documento_ventas_hijo()
    {
        // 42 = Pedido de ventas
        if ($this->core_tipo_transaccion_id == 42 && $this->estado == 'Pendiente') {
            //return null;
        }

        $doc_hijo = VtasDocEncabezado::where( 'ventas_doc_relacionado_id', $this->id )->get()->first();
        
        if ( $doc_hijo == null )
        {
            if ( !Schema::hasTable( 'vtas_pos_doc_encabezados' ) )
            {
                return null;
            }

            $doc_hijo = FacturaPos::where( 'ventas_doc_relacionado_id', $this->id )->get()->first();

            //dd($this,$doc_hijo);

            if ( $doc_hijo == null )
            {
                return null;
            }
        }

        if ($doc_hijo->core_tipo_transaccion_id == $this->core_tipo_transaccion_id) {
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
        
        
            case '52':
                $url = 'fe_factura/';
                break;
        
        
            case '53':
                $url = 'fe_nota_credito/';
                break;
            
            default:
                $url = 'ventas/';
                break;
        }
        
        if( $this->tipo_documento_app == null )
        {
            dd('Error en Tipo de Documento (tipo_documento_app)', $this);
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

    public function almacenar_lineas_registros( array $lineas_registros )
    {
        $cantidad_registros = count($lineas_registros);
        for ($i=0; $i < $cantidad_registros; $i++) 
        {
            $linea_datos = [ 'vtas_motivo_id' => (int)$lineas_registros[$i]->inv_motivo_id ] +
                            [ 'inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id ] +
                            [ 'precio_unitario' => (float)$lineas_registros[$i]->precio_unitario ] +
                            [ 'cantidad' => (float)$lineas_registros[$i]->cantidad ] +
                            //[ 'cantidad_pendiente' => (float)$lineas_registros[$i]->cantidad_pendiente ] +
                            //[ 'cantidad_devuelta' => (float)$lineas_registros[$i]->cantidad_devuelta ] +
                            [ 'precio_total' => (float)$lineas_registros[$i]->precio_total ] +
                            [ 'base_impuesto' => (float)$lineas_registros[$i]->base_impuesto ] +
                            [ 'tasa_impuesto' => (float)$lineas_registros[$i]->tasa_impuesto ] +
                            [ 'valor_impuesto' => (float)$lineas_registros[$i]->valor_impuesto ] +
                            [ 'base_impuesto_total' => (float)$lineas_registros[$i]->base_impuesto_total ] +
                            [ 'tasa_descuento' => (float)$lineas_registros[$i]->tasa_descuento ] +
                            [ 'valor_total_descuento' => (float)$lineas_registros[$i]->valor_total_descuento ] +
                            [ 'creado_por' => Auth::user()->email ] +
                            [ 'estado' => 'Activo' ];

            VtasDocRegistro::create( 
                                        [ 'vtas_doc_encabezado_id' => $this->id ] +
                                        $linea_datos
                                    );
        }       
    }

    public function datos_auxiliares_estudiante()
    {
        return $this->hasOne(FacturaAuxEstudiante::class, 'vtas_doc_encabezado_id');
    }

    public function resolucion_facturacion()
    {
        return ResolucionFacturacion::where( 'tipo_doc_app_id', $this->core_tipo_doc_app_id )
                                //->where('estado','Activo')
                                ->get()
                                ->last();
    }

    // SOLO PARA UN MOVIMIENTO. No funciona Si se paga con varios medios de pago
    public function movimiento_tesoreria()
    {
        return TesoMovimiento::where( [
                                        ['core_tipo_transaccion_id', $this->core_tipo_transaccion_id ],
                                        ['core_tipo_doc_app_id', $this->core_tipo_doc_app_id ],
                                        ['consecutivo', $this->consecutivo ]
                                    ] 
                                )
                                ->get()
                                ->first();
    }

    public function medio_pago()
    {
        $movimiento_tesoreria = $this->movimiento_tesoreria();

        if ( is_null($movimiento_tesoreria) )
        {
            return 'No definido';
        }

        $medio_pago = $movimiento_tesoreria->medio_pago;
        if ( is_null($medio_pago) )
        {
            return 'No definido';
        }

        return $medio_pago->descripcion;
    }

    public function caja_banco()
    {
        $movimiento_tesoreria = $this->movimiento_tesoreria();

        if ( is_null($movimiento_tesoreria) )
        {
            return 'No definido';
        }

        $caja = $movimiento_tesoreria->caja;
        if ( !is_null($caja) )
        {
            return $caja->descripcion;
        }

        $cuenta_bancaria = $movimiento_tesoreria->cuenta_bancaria;
        if ( !is_null($cuenta_bancaria) )
        {
            return 'Cuenta ' . $cuenta_bancaria->tipo_cuenta . ' ' . $cuenta_bancaria->entidad_financiera->descripcion . ' No. ' . $cuenta_bancaria->descripcion;
        }
    }

    /*
        Movimiento de Tesoreria o Cartera de clientes (CxC)
    */
    public function get_valor_base_iva_total_documento()
    {
        return $this->lineas_registros->sum('base_impuesto_total');
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 23; // Facturas
        
        $texto_busqueda = '%' . str_replace( " ", "%", $search ) . '%';

        $query = VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->having('nueva_cadena', 'LIKE', $texto_busqueda)
            ->select(
                DB::raw('CONCAT( vtas_doc_encabezados.fecha, " ", core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo, " ", core_terceros.descripcion, " ", vtas_doc_encabezados.descripcion, " ", vtas_doc_encabezados.valor_total, " ", vtas_doc_encabezados.forma_pago, " ", vtas_doc_encabezados.estado) AS nueva_cadena'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS FECHA'),
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('core_terceros.numero_identificacion AS CC_NIT'),
                DB::raw('core_terceros.descripcion AS CLIENTE'),
                'vtas_doc_encabezados.descripcion AS DETALLE',
                'vtas_doc_encabezados.valor_total AS VALOR_TOTAL',
                'vtas_doc_encabezados.forma_pago AS FORMA_DE_PAGO',
                'vtas_doc_encabezados.estado AS ESTADO'
            );

        $query = self::aplicarFiltroCreadoPor($query, 'vtas_doc_encabezados.creado_por');

        $string = $query->orderBy('vtas_doc_encabezados.fecha', 'DESC')->toSql();
            
        $string = str_replace('`vtas_doc_encabezados`.`core_empresa_id` = ?', '`vtas_doc_encabezados`.`core_empresa_id` = ' . Auth::user()->empresa_id, $string);
        
        $string = str_replace('`vtas_doc_encabezados`.`core_tipo_transaccion_id` = ?', '`vtas_doc_encabezados`.`core_tipo_transaccion_id` = ' . $core_tipo_transaccion_id, $string);

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE FACTURAS DE VENTAS";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 23; // Facturas

        $query = VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                'core_terceros.descripcion AS campo3',
                'vtas_doc_encabezados.valor_total AS campo4',
                'vtas_doc_encabezados.valor_total AS campo5',
                'vtas_doc_encabezados.forma_pago AS campo6',
                'vtas_doc_encabezados.estado AS campo7',
                'vtas_doc_encabezados.id AS campo8'
            );

        $query = self::aplicarFiltroCreadoPor($query, 'vtas_doc_encabezados.creado_por');

        $collection = $query->orderBy('vtas_doc_encabezados.fecha', 'DESC')
            ->orderBy('vtas_doc_encabezados.created_at')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0)
        {
            if (strlen($search) > 0)
            {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        foreach( $nuevaColeccion AS $register_collect )
        {
            $doc_venta = VtasDocEncabezado::find( $register_collect->campo8 );
            $register_collect->campo4 = '$' . number_format( $doc_venta->get_valor_base_iva_total_documento(), 0, ',', '.' );

            $register_collect->campo5 = '$' . number_format( $register_collect->campo5, 0, ',', '.' );
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
                'vtas_doc_encabezados.valor_total',
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS fecha'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_vencimiento,"%d-%m-%Y") AS fecha_vencimiento'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS fecha_entrega'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.hora_entrega,"%H:%i %p") AS hora_entrega'),
                'vtas_doc_encabezados.plazo_entrega_id',
                'vtas_doc_encabezados.vendedor_id',
                'vtas_doc_encabezados.descripcion',
                'vtas_doc_encabezados.estado',
                'vtas_doc_encabezados.creado_por',
                'vtas_doc_encabezados.modificado_por',
                'vtas_doc_encabezados.created_at',
                'vtas_doc_encabezados.updated_at',
                'vtas_doc_encabezados.orden_compras',
                'vtas_doc_encabezados.ventas_doc_relacionado_id',
                'vtas_doc_encabezados.forma_pago AS forma_pago',
                'vtas_doc_encabezados.forma_pago AS condicion_pago',
                'vtas_doc_encabezados.total_efectivo_recibido',
                'vtas_doc_encabezados.valor_ajuste_al_peso',
                'vtas_doc_encabezados.valor_total_cambio',
                'vtas_doc_encabezados.valor_total_bolsas',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                'vtas_doc_encabezados.consecutivo AS documento_transaccion_consecutivo',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
                DB::raw('CONCAT(doc_inventarios.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_remision_prefijo_consecutivo'),
                DB::raw('core_terceros.descripcion AS tercero_nombre_completo'),
                'core_terceros.numero_identificacion',
                'core_terceros.digito_verificacion',
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
