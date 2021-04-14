<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class OrdenDeTrabajo extends Model
{
    protected $table = 'nom_ordenes_de_trabajo';
    
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'nom_doc_encabezado_id', 'cliente_id', 'fecha', 'descripcion', 'nom_concepto_id', 'ubicacion_desarrollo_actividad', 'estado', 'creado_por', 'modificado_por'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Fecha', 'Orden de trabajo', 'Doc. nómina', 'Cliente', 'Detalle', 'Concepto', 'Ubicación desarollo actividad', 'Estado'];

    public $urls_acciones = '{"create":"web/create"}';

    public $vistas = '{"create":"nomina.ordenes_de_trabajo.create"}';

    public function empresa()
    {
        return $this->belongsTo('App\Core\Empresa', 'core_empresa_id');
    }

    public function encabezado_documento()
    {
        return $this->belongsTo(NomDocEncabezado::class, 'nom_doc_encabezado_id');
    }

    public function tipo_trasaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function empleados()
    {
        return $this->hasMany(EmpleadoOrdenDeTrabajo::class, 'orden_trabajo_id');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'cliente_id');
    }

    public function concepto()
    {
        return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = OrdenDeTrabajo::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'nom_ordenes_de_trabajo.core_tipo_doc_app_id')
                            ->select(
	                            	'nom_ordenes_de_trabajo.fecha AS campo1',
                            		DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",nom_ordenes_de_trabajo.consecutivo) AS campo2'),
                            		'nom_ordenes_de_trabajo.nom_doc_encabezado_id AS campo3',
	                            	'nom_ordenes_de_trabajo.cliente_id AS campo4',
	                            	'nom_ordenes_de_trabajo.descripcion AS campo5',
	                            	'nom_ordenes_de_trabajo.nom_concepto_id AS campo6',
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
        $string = OrdenDeTrabajo::select('nom_ordenes_de_trabajo.core_tipo_transaccion_id AS campo1', 'nom_ordenes_de_trabajo.nom_doc_encabezado_id AS campo2', 'nom_ordenes_de_trabajo.cliente_id AS campo3', 'nom_ordenes_de_trabajo.fecha AS campo4', 'nom_ordenes_de_trabajo.descripcion AS campo5', 'nom_ordenes_de_trabajo.nom_concepto_id AS campo6', 'nom_ordenes_de_trabajo.ubicacion_desarrollo_actividad AS campo7', 'nom_ordenes_de_trabajo.estado AS campo8', 'nom_ordenes_de_trabajo.id AS campo9')
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
}
