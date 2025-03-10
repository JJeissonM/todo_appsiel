<?php

namespace App\Ventas;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VtasPedido extends VtasDocEncabezado
{
    protected $table = 'vtas_doc_encabezados';

    // ventas_doc_relacionado_id: la factura que se facturó con base en el pedido.
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'contacto_cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'hora_entrega', 'plazo_entrega_id', 'fecha_vencimiento', 'orden_compras', 'valor_total'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente',  'Fecha entrega',  'Vendedor', 'Estado'];

    //public $vistas = '{"index":"layouts.index3"}';

    public $archivo_js = 'assets/js/ventas/pedidos.js';

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

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany(VtasDocRegistro::class, 'vtas_doc_encabezado_id');
    } 

    public function lineas_registros_to_show()
    {
        $lineas_registros = $this->lineas_registros;
        $lineas = [];

        foreach ($lineas_registros as $linea) {
            $aux = $linea->toArray();

            $aux['lbl_producto_descripcion'] = $linea->producto->get_value_to_show();
            $aux['cantidad'] = number_format( $linea->cantidad, 0, ',', '.');
            $aux['cantidad_pendiente'] = number_format( $linea->cantidad_pendiente, 0, ',', '.');
            $aux['precio_unitario'] = '$ ' . number_format( $linea->precio_unitario / ( 1 + $linea->tasa_impuesto / 100) , 0, ',', '.');
            $aux['tasa_impuesto'] = number_format( $linea->tasa_impuesto, 0, ',', '.').'%';
            
            $aux['precio_subtotal'] = '$ ' . number_format( $linea->precio_unitario / ( 1 + $linea->tasa_impuesto / 100) * $linea->cantidad, 0, ',', '.');
            
            $aux['valor_total_descuento'] = '$ ' . number_format( $linea->valor_total_descuento, 0, ',', '.');
            $aux['precio_total'] = '$ ' . number_format( $linea->precio_total, 0, ',', '.') . '<span class="precio_total" style="display: none;">' . $linea->precio_total . '</span>';

            $lineas[] = $aux;
        }

        return $lineas;
    }   

    public static function consultar_registros2($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 42; // Pedido de ventas
        
            
        $array_wheres = [
            ['vtas_doc_encabezados.core_empresa_id','=', Auth::user()->empresa_id],
            ['vtas_doc_encabezados.core_tipo_transaccion_id', '=', $core_tipo_transaccion_id]
        ];
        $user = Auth::user();

        if ( $user->hasRole('Vendedor') )
        {
            $vendedor = Vendedor::where([
                ['user_id', '=', $user->id]
            ])->get()->first();

            if ($vendedor != null) {
                $array_wheres = array_merge($array_wheres,[['vtas_doc_encabezados.vendedor_id','=', $vendedor->id]]);
            }            
        }

        $collection = VtasPedido::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
                ->leftJoin('vtas_vendedores', 'vtas_vendedores.id', '=', 'vtas_doc_encabezados.vendedor_id')
                ->leftJoin('core_terceros as terceros_vendedores', 'terceros_vendedores.id', '=', 'vtas_vendedores.core_tercero_id')
                ->where( $array_wheres )
                ->select(
                    DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                    DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                    DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS campo4'),
                    'terceros_vendedores.descripcion AS campo5',
                    'vtas_doc_encabezados.estado AS campo6',
                    'vtas_doc_encabezados.id AS campo7'
                )
                ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
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
        $core_tipo_transaccion_id = 42;
        
        $texto_busqueda = '%' . str_replace( " ", "%", $search ) . '%';

        $string = VtasPedido::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->leftJoin('core_terceros as terceros_vendedores', 'terceros_vendedores.id', '=', 'vtas_doc_encabezados.vendedor_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->having('nueva_cadena', 'LIKE', $texto_busqueda)
            ->select(
                DB::raw('CONCAT( vtas_doc_encabezados.fecha, " ", core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo, " ", core_terceros.descripcion, " ", vtas_doc_encabezados.descripcion, " ", vtas_doc_encabezados.valor_total, " ", vtas_doc_encabezados.forma_pago, " ", vtas_doc_encabezados.estado) AS nueva_cadena'),
                'vtas_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS DOCUMENTO'),
                'core_terceros.numero_identificacion AS CC_NIT',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS CLIENTE'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS FECHA_ENTREGA'),
                'terceros_vendedores.descripcion AS VENDEDOR',
                'vtas_doc_encabezados.descripcion AS DETALLE',
                'vtas_doc_encabezados.estado AS ESTADO'
            )
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->toSql();
            
        $string = str_replace('`vtas_doc_encabezados`.`core_empresa_id` = ?', '`vtas_doc_encabezados`.`core_empresa_id` = ' . Auth::user()->empresa_id, $string);
        
        $string = str_replace('`vtas_doc_encabezados`.`core_tipo_transaccion_id` = ?', '`vtas_doc_encabezados`.`core_tipo_transaccion_id` = ' . $core_tipo_transaccion_id, $string);

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PEDIDO DE VENTAS";
    }
}
