<?php

namespace App\Compras;

use App\FacturacionElectronica\ResultadoEnvioDocumentoSoporte;
use Illuminate\Database\Eloquent\Model;

use App\Sistema\TipoTransaccion;

use App\Inventarios\InvDocEncabezado;
use App\Ventas\ResolucionFacturacion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComprasDocEncabezado extends Model
{
    //protected $table = 'compras_doc_encabezados';
	protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'cotizacion_id', 'compras_doc_relacionado_id', 'entrada_almacen_id', 'proveedor_id', 'comprador_id', 'forma_pago', 'fecha_recepcion', 'fecha_vencimiento', 'doc_proveedor_prefijo', 'doc_proveedor_consecutivo', 'descripcion', 'creado_por', 'modificado_por', 'estado','valor_total'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Proveedor', 'Fact. Proveedor', 'Detalle', 'Valor total',  'Forma de pago', 'Estado'];

    public function tipo_transaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo('App\Core\TipoDocApp', 'core_tipo_doc_app_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function entrada_almacen()
    {
        return $this->belongsTo(InvDocEncabezado::class, 'entrada_almacen_id');
    }

    public function movimientos()
    {
        return $this->hasMany(ComprasMovimiento::class);
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

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany( ComprasDocRegistro::class, 'compras_doc_encabezado_id' );
    }

    public function resolucion_facturacion()
    {
        return ResolucionFacturacion::where( 'tipo_doc_app_id', $this->core_tipo_doc_app_id )
                            ->where('estado','Activo')
                            ->get()
                            ->last();
    }

    public function resultados_envios_fe_doc_soporte()
    {
        return ResultadoEnvioDocumentoSoporte::where([
            ['core_tipo_transaccion_id','=',$this->core_tipo_transaccion_id],
            ['core_tipo_doc_app_id','=',$this->core_tipo_doc_app_id],
            ['consecutivo','=',$this->consecutivo],
        ])->get();

    }

    public function enviado_electronicamente()
    {
        $procesado = false;
        
        $resultados_envios_fe_doc_soporte = $this->resultados_envios_fe_doc_soporte();

        foreach ($resultados_envios_fe_doc_soporte as $resultado) {
            if($resultado->esValidoDian == 1){
                $procesado = true;
            }
        }
        return $procesado;
    }

	public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 25; // Facturas de compras
        
        $collection = ComprasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
                                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
                                    ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                    ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                    ->select(
                                        'compras_doc_encabezados.fecha AS campo1',
                                        DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS campo2'),
                                        'core_terceros.descripcion AS campo3',
                                        DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," ",compras_doc_encabezados.doc_proveedor_consecutivo) AS campo4'),
                                        'compras_doc_encabezados.descripcion AS campo5',
                                        'compras_doc_encabezados.valor_total AS campo6',
                                        'compras_doc_encabezados.forma_pago AS campo7',
                                        'compras_doc_encabezados.estado AS campo8',
                                        'compras_doc_encabezados.id AS campo9'
                                    )
                                    ->orderBy('compras_doc_encabezados.fecha', 'DESC')
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
        $core_tipo_transaccion_id = 25; // Facturas de compras
        $string = ComprasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            //->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            //->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'compras_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS DOCUMENTO_COMPRA'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS PROVEEDOR'),
                DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," ",compras_doc_encabezados.doc_proveedor_consecutivo) AS FACTURA'),
                'compras_doc_encabezados.descripcion AS DETALLE',
                'compras_doc_encabezados.valor_total AS VALOR_TOTAL',
                'compras_doc_encabezados.forma_pago AS FORMA_DE_PAGO',
                'compras_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("compras_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," ",compras_doc_encabezados.doc_proveedor_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.forma_pago", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('compras_doc_encabezados.fecha', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE COMPRAS ENCABEZADO";
    }
	

    public static function opciones_campo_select()
    {
        $opciones = ComprasDocEncabezado::where('compras_doc_encabezados.estado','Activo')
                    ->select('compras_doc_encabezados.id','compras_doc_encabezados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {
        
        // ARREGLAR ESTO:     ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','compras_doc_encabezados.condicion_pago_id')
        return ComprasDocEncabezado::where('compras_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_doc_encabezados', 'inv_doc_encabezados.id', '=', 'compras_doc_encabezados.entrada_almacen_id')
                    ->leftJoin('core_tipos_docs_apps AS doc_inventarios', 'doc_inventarios.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->select(
                                'compras_doc_encabezados.id',
                                'compras_doc_encabezados.core_empresa_id',
                                'compras_doc_encabezados.entrada_almacen_id',
                                'compras_doc_encabezados.core_tercero_id',
                                'compras_doc_encabezados.proveedor_id',
                                'compras_doc_encabezados.core_tipo_transaccion_id',
                                'compras_doc_encabezados.core_tipo_doc_app_id',
                                'compras_doc_encabezados.consecutivo',
                                'compras_doc_encabezados.fecha',
                                'compras_doc_encabezados.fecha_vencimiento',
                                'compras_doc_encabezados.fecha_recepcion',
                                'compras_doc_encabezados.cotizacion_id',
                                'compras_doc_encabezados.descripcion',
                                'compras_doc_encabezados.compras_doc_relacionado_id',
                                'compras_doc_encabezados.estado',
                                'compras_doc_encabezados.creado_por',
                                'compras_doc_encabezados.modificado_por',
                                'compras_doc_encabezados.created_at',
                                'compras_doc_encabezados.valor_total',
                                'compras_doc_encabezados.doc_proveedor_prefijo',
                                'compras_doc_encabezados.doc_proveedor_consecutivo',
                                'compras_doc_encabezados.forma_pago AS condicion_pago',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                'compras_doc_encabezados.consecutivo AS documento_transaccion_consecutivo',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                DB::raw( 'CONCAT(doc_inventarios.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_remision_prefijo_consecutivo' ),
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social," (",core_terceros.descripcion,")") AS tercero_nombre_completo' ),
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get()
                    ->first();
    }

    // Devuelve un array de dos posiciones, la primera posicién es una lista de enlaces (<a></a>) con cada uno de las entradas de almacén con que se hizo la factura. La segunda posición es un indicador tipo boolean para decir si hay más de una remisión en la OC
    public static function get_documentos_relacionados( $doc_encabezado )
    {
        $mas_de_uno = false;

        $ids_documentos_relacionados = explode( ',', $doc_encabezado->entrada_almacen_id );
        
        $app_id = 9;

        $cant_registros = count($ids_documentos_relacionados);

        $lista = '';
        $primer = true;
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $un_documento = InvDocEncabezado::get_registro_impresion( $ids_documentos_relacionados[$i] );
            if ( !is_null($un_documento) )
            {
                $transaccion = TipoTransaccion::find( $un_documento->core_tipo_transaccion_id );

                $modelo_doc_relacionado_id = $transaccion->core_modelo_id;
                $transaccion_doc_relacionado_id = $transaccion->id;
                
                if ($primer)
                {
                    $lista .= '<a href="'.url( 'inventarios/'.$un_documento->id.'?id='.$app_id.'&id_modelo='.$modelo_doc_relacionado_id.'&id_transaccion='.$transaccion_doc_relacionado_id ).'" target="_blank">'.$un_documento->documento_transaccion_prefijo_consecutivo.'</a>';
                    $primer = false;
                }else{
                    $lista .= ', &nbsp; <a href="'.url( 'inventarios/'.$un_documento->id.'?id='.$app_id.'&id_modelo='.$modelo_doc_relacionado_id.'&id_transaccion='.$transaccion_doc_relacionado_id ).'" target="_blank">'.$un_documento->documento_transaccion_prefijo_consecutivo.'</a>';
                    $mas_de_uno = true;
                }
            }
        }
        return [$lista,$mas_de_uno];
    }
}
