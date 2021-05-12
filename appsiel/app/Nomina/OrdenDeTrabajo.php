<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\EmpleadoOrdenDeTrabajo;
use App\Nomina\ItemOrdenDeTrabajo;

class OrdenDeTrabajo extends Model
{
    protected $table = 'nom_ordenes_de_trabajo';
    
    protected $fillable = [ 'core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'nom_doc_encabezado_id', 'inv_doc_encabezado_id', 'core_tercero_id', 'fecha', 'descripcion', 'nom_concepto_id', 'inv_bodega_id', 'ubicacion_desarrollo_actividad', 'estado', 'creado_por', 'modificado_por'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Fecha', 'Orden de trabajo', 'Doc. nómina (Proyecto)', 'Tercero', 'Detalle', 'Concepto', 'Ubicación desarollo actividad', 'Estado'];

    public $urls_acciones = '{"create":"web/create","show":"nom_ordenes_trabajo/id_fila"}';

    public $vistas = '{"create":"nomina.ordenes_de_trabajo.create"}';

    public function empresa()
    {
        return $this->belongsTo('App\Core\Empresa', 'core_empresa_id');
    }

    public function documento_nomina()
    {
        return $this->belongsTo(NomDocEncabezado::class, 'nom_doc_encabezado_id');
    }

    public function documento_inventario()
    {
        return $this->belongsTo( 'App\Inventarios\InvDocEncabezado', 'inv_doc_encabezado_id');
    }

    public function tipo_trasaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public function bodega()
    {
        return $this->belongsTo( 'App\Inventarios\InvBodega', 'inv_bodega_id');
    }

    public function concepto()
    {
        return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
    }

    public function items()
    {
        return $this->hasMany(ItemOrdenDeTrabajo::class, 'orden_trabajo_id');
    }

    public function empleados()
    {
        return $this->hasMany(EmpleadoOrdenDeTrabajo::class, 'orden_trabajo_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = OrdenDeTrabajo::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'nom_ordenes_de_trabajo.core_tipo_doc_app_id')
                            ->leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_ordenes_de_trabajo.nom_doc_encabezado_id')
                            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_ordenes_de_trabajo.nom_concepto_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_ordenes_de_trabajo.core_tercero_id')
                            ->select(
	                            	'nom_ordenes_de_trabajo.fecha AS campo1',
                            		DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",nom_ordenes_de_trabajo.consecutivo) AS campo2'),
                            		'nom_doc_encabezados.descripcion AS campo3',
	                            	'core_terceros.descripcion AS campo4',
	                            	'nom_ordenes_de_trabajo.descripcion AS campo5',
	                            	'nom_conceptos.descripcion AS campo6',
	                            	'nom_ordenes_de_trabajo.ubicacion_desarrollo_actividad AS campo7',
	                            	'nom_ordenes_de_trabajo.estado AS campo8',
	                            	'nom_ordenes_de_trabajo.id AS campo9')
                            ->orderBy('nom_ordenes_de_trabajo.fecha','DESC')
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
        $string = OrdenDeTrabajo::select('nom_ordenes_de_trabajo.core_tipo_transaccion_id AS campo1', 'nom_ordenes_de_trabajo.nom_doc_encabezado_id AS campo2', 'nom_ordenes_de_trabajo.core_tercero_id AS campo3', 'nom_ordenes_de_trabajo.fecha AS campo4', 'nom_ordenes_de_trabajo.descripcion AS campo5', 'nom_ordenes_de_trabajo.nom_concepto_id AS campo6', 'nom_ordenes_de_trabajo.ubicacion_desarrollo_actividad AS campo7', 'nom_ordenes_de_trabajo.estado AS campo8', 'nom_ordenes_de_trabajo.id AS campo9')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE ÓRDENES DE TRABAJO";
    }

    public static function opciones_campo_select()
    {
        $opciones = OrdenDeTrabajo::where('nom_ordenes_de_trabajo.estado','Activo')
                    ->select('nom_ordenes_de_trabajo.id','nom_ordenes_de_trabajo.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional( $datos, $registro )
    {
        $tabla_empleados = json_decode( $datos['tabla_empleados'] );
        
        $cantidad_empleados = count($tabla_empleados);
        for ($i = 0; $i < $cantidad_empleados; $i++)
        {
            $linea_empleado = [ 
                                'orden_trabajo_id' => $registro->id,
                                'nom_concepto_id' => (int)$datos['nom_concepto_id'],
                                'nom_contrato_id' => (int)$tabla_empleados[$i]->nom_contrato_id,
                                'cantidad_horas' => (float)$tabla_empleados[$i]->cantidad_horas,
                                'valor_por_hora' => (float)$tabla_empleados[$i]->valor_unitario,
                                'valor_devengo' => (float)$tabla_empleados[$i]->valor_total,
                                'estado' => 'Pendiente',
                                'creado_por' => Auth::user()->email
                            ];

            EmpleadoOrdenDeTrabajo::create( $linea_empleado  );

            // Crear registro en documento de nomina
            $contrato = NomContrato::find( (int)$tabla_empleados[$i]->nom_contrato_id );
            $datos_registro_doc_nomina = [
                        'nom_doc_encabezado_id' => $registro->nom_doc_encabezado_id,
                        'orden_trabajo_id' => $registro->id,
                        'nom_contrato_id' => (int)$tabla_empleados[$i]->nom_contrato_id,
                        'core_tercero_id' => $contrato->core_tercero_id,
                        'fecha' => $registro->documento_nomina->fecha,
                        'core_empresa_id' => $registro->core_empresa_id,
                        'detalle' => 'Orden de trabajo ' . $registro->tipo_documento_app->prefijo . ' ' . $registro->consecutivo,
                        'nom_concepto_id' => $registro->nom_concepto_id,
                        'cantidad_horas' => (float)$tabla_empleados[$i]->cantidad_horas,
                        'valor_devengo' => (float)$tabla_empleados[$i]->valor_total,
                        'valor_deduccion' => 0,
                        'estado' => 'Activo',
                        'creado_por' => Auth::user()->email
                    ];
            NomDocRegistro::create( $datos_registro_doc_nomina );

            $registro->documento_nomina->actualizar_totales();
        }

        // PARA LOS ITEMS INGRESADOS
        $lineas_registros = json_decode( $datos['movimiento'] );

        // Quitar primera línea
        array_shift( $lineas_registros );

        // Quitar las dos últimas líneas
        array_pop($lineas_registros);
        array_pop($lineas_registros);

        $cantidad_registros = count($lineas_registros);
        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            $inv_motivo_id = (int)explode( "-", $lineas_registros[$i]->motivo )[0];
            $cantidad = (float) substr( $lineas_registros[$i]->cantidad, 0, strpos( $lineas_registros[$i]->cantidad, " ") );
            
            if ( $cantidad == 0 )
            {
                continue;
            }
            
            $linea_datos = [
                                'orden_trabajo_id' => $registro->id,
                                'inv_motivo_id' => $inv_motivo_id,
                                'inv_producto_id' => (float)$lineas_registros[$i]->inv_producto_id,
                                'costo_unitario' => (float)substr($lineas_registros[$i]->costo_unitario, 1),
                                'cantidad' => $cantidad,
                                'costo_total' => (float)substr($lineas_registros[$i]->costo_total, 1),
                                'estado' => 'Pendiente',
                                'creado_por' => Auth::user()->email
                            ];

            ItemOrdenDeTrabajo::create( $linea_datos );
        }

        $registro->core_tercero_id = $datos['core_tercero_id'];
        $registro->inv_bodega_id = $datos['inv_bodega_id_aux2'];
        $registro->creado_por = Auth::user()->email;
        $registro->save();
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $modelo_padre_id = Modelo::where('modelo', 'Area')->value('id');

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $lista_campos[$i]['name'], "core_campo_id") !== false ) 
            {
                $core_campo_id = $lista_campos[$i]['id']; // Atributo_ID

                $registro_eav = ModeloEavValor::where(
                                                    [ 
                                                        "modelo_padre_id" => $modelo_padre_id,
                                                        "registro_modelo_padre_id" => $registro->id,
                                                        "core_campo_id" => $core_campo_id
                                                    ]
                                                )
                                            ->get()
                                            ->first();
                if( !is_null( $registro_eav ) )
                {
                    $lista_campos[$i]['value'] = $registro_eav->valor;
                }
            }

        }

        return $lista_campos;
    }

    public function update_adicional( $datos, $id )
    {
        $modelo_padre_id = Modelo::where('modelo', 'Area')->value('id');

        $this->almacenar_registros_eav( $datos, $modelo_padre_id, $id );        
    }
}
