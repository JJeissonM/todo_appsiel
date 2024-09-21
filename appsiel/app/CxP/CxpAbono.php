<?php

namespace App\CxP;

use App\Compras\ComprasDocEncabezado;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class CxpAbono extends Model
{

    // Tabla auxiliar para llevar el registro de los abonos a los documentos de CxP, normalmente son documentos de Pagos de Tesorería
	protected $table = 'cxp_abonos';

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','core_empresa_id','core_tercero_id','modelo_referencia_tercero_index','referencia_tercero_id','fecha','doc_cxp_transacc_id','doc_cxp_tipo_doc_id','doc_cxp_consecutivo', 'doc_cruce_transacc_id', 'doc_cruce_tipo_doc_id', 'doc_cruce_consecutivo','abono','creado_por','modificado_por'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento pago', 'Proveedor', 'Documento de CxP', 'Documento Cruce', 'Valor abono'];

    public $urls_acciones = '{"show":"no"}';

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

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    } 

    public function enlace_show_documento()
    {
        $vista = '';
        $doc_transaccion = app($this->tipo_transaccion->modelo->name_space)->where([
            ['core_tipo_transaccion_id', '=', $this->core_tipo_transaccion_id ],
            ['core_tipo_doc_app_id', '=', $this->core_tipo_doc_app_id ],
            ['consecutivo', '=', $this->consecutivo ]
        ])->get()->first();
        
        if ( $doc_transaccion == null ) {
            return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
        }
        
        switch ( $this->core_tipo_transaccion_id )
        {

            case '25': // Factura de compras
                $url = 'compras/';
                break;
  
            case '33': // Pagos de CxP
                $url = 'tesoreria/pagos_cxp/';
            break;

            case '36': // Nota credito de compras
                $url = 'compras/';
                $vista = '&vista=compras.notas_credito.show';
                break;
            
            case '48': // Documentos soporte en adquisiciones efectuadas a SNOAEF
                $url = 'compras/';
                break;        
        
            case '17': // Pagos de Tesoreria
                $url = 'tesoreria/pagos/';
                break;        
        
            case '9': // Nota de contabilidad
                $url = 'contabilidad/';
                break;
            
            default:
                $url = 'compras/';
                break;
        }
  
        $enlace = '<a href="' . url( $url . $doc_transaccion->id . '?id=' . Input::get('id') . '&id_modelo=' . $this->tipo_transaccion->core_modelo_id . '&id_transaccion=' . $this->core_tipo_transaccion_id . $vista ) . '" target="_blank" title="' . $this->tipo_transaccion->descripcion . '">' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo . '</a>';
  
        return $enlace;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = CxpAbono::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_abonos.core_tipo_doc_app_id')
                        ->leftJoin('core_tipos_docs_apps AS tipo_docs_cxp', 'tipo_docs_cxp.id', '=', 'cxp_abonos.doc_cxp_tipo_doc_id')
                        ->leftJoin('core_tipos_docs_apps AS tipo_docs_cruce', 'tipo_docs_cruce.id', '=', 'cxp_abonos.doc_cruce_tipo_doc_id')
                        ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_abonos.core_tercero_id')
                        ->where('cxp_abonos.core_empresa_id', Auth::user()->empresa_id)
                        ->select(
                            'cxp_abonos.fecha AS campo1',
                            DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.consecutivo) AS campo2'),
                            DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                            DB::raw('CONCAT(tipo_docs_cxp.prefijo," ",cxp_abonos.doc_cxp_consecutivo) AS campo4'),
                            DB::raw('CONCAT(tipo_docs_cruce.prefijo," ",cxp_abonos.doc_cruce_consecutivo) AS campo5'),
                            'cxp_abonos.abono AS campo6',
                            'cxp_abonos.id AS campo7'
                        )
                        ->orderBy('cxp_abonos.created_at', 'DESC')
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
        $string = CxpAbono::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_abonos.core_tipo_doc_app_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_docs_cxp', 'tipo_docs_cxp.id', '=', 'cxp_abonos.doc_cxp_tipo_doc_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_docs_cruce', 'tipo_docs_cruce.id', '=', 'cxp_abonos.doc_cruce_tipo_doc_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_abonos.core_tercero_id')
            ->where('cxp_abonos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'cxp_abonos.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                DB::raw('CONCAT(tipo_docs_cxp.prefijo," ",cxp_abonos.doc_cxp_consecutivo) AS campo4'),
                DB::raw('CONCAT(tipo_docs_cruce.prefijo," ",cxp_abonos.doc_cruce_consecutivo) AS campo5'),
                'cxp_abonos.abono AS campo6',
                'cxp_abonos.id AS campo7'
            )
            ->where("cxp_abonos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(tipo_docs_cxp.prefijo," ",cxp_abonos.doc_cxp_consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(tipo_docs_cruce.prefijo," ",cxp_abonos.doc_cruce_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("cxp_abonos.abono", "LIKE", "%$search%")
            ->orderBy('cxp_abonos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCUMENTOS ABONADOS DE CXP";
    }

    /*
        Obtener los registro de abonos hechos por $doc_encabezado
    */
    public static function get_documentos_abonados( $doc_encabezado )
    {

        return CxpAbono::where('cxp_abonos.core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                    ->where('cxp_abonos.core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                    ->where('cxp_abonos.consecutivo',$doc_encabezado->consecutivo)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_abonos.doc_cxp_tipo_doc_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_abonos.core_tercero_id')
                    ->select(
                                'cxp_abonos.id',
                                'cxp_abonos.core_empresa_id',
                                'cxp_abonos.core_tercero_id',
                                'cxp_abonos.referencia_tercero_id',
                                'cxp_abonos.doc_cxp_transacc_id',
                                'cxp_abonos.doc_cxp_tipo_doc_id',
                                'cxp_abonos.doc_cxp_consecutivo',
                                'cxp_abonos.fecha',
                                'cxp_abonos.abono',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.doc_cxp_consecutivo) AS documento_prefijo_consecutivo' ),
                                'core_terceros.descripcion AS tercero_nombre_completo',
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get();
    }

    /*
     * Obtener datos de los PAGOS hechos a una FACTURA (o documento de CxP) específica
     */
    public static function get_abonos_documento( $doc_cxp_encabezado )
    {

        return CxpAbono::where('cxp_abonos.doc_cxp_transacc_id', $doc_cxp_encabezado->core_tipo_transaccion_id)
                    ->where('cxp_abonos.doc_cxp_tipo_doc_id', $doc_cxp_encabezado->core_tipo_doc_app_id)
                    ->where('cxp_abonos.doc_cxp_consecutivo', $doc_cxp_encabezado->consecutivo)
                    ->leftJoin('core_tipos_docs_apps as doc_pago', 'doc_pago.id', '=', 'cxp_abonos.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_abonos.core_tercero_id')
                    ->select(
                                'cxp_abonos.id',
                                'cxp_abonos.core_empresa_id',
                                'cxp_abonos.core_tercero_id',
                                'cxp_abonos.core_tipo_transaccion_id',
                                'cxp_abonos.core_tipo_doc_app_id',
                                'cxp_abonos.consecutivo',
                                'cxp_abonos.fecha',
                                'cxp_abonos.abono',
                                'doc_pago.descripcion AS documento_transaccion_descripcion',
                                DB::raw( 'CONCAT(doc_pago.prefijo," ",cxp_abonos.consecutivo) AS documento_prefijo_consecutivo' ),
                                'core_terceros.descripcion AS tercero_nombre_completo',
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get();
    }

    public function get_encabezado_documento_cxp()
    {
        return ComprasDocEncabezado::where([
                                        [ 'core_tipo_transaccion_id', '=', $this->doc_cxp_transacc_id ],
                                        [ 'core_tipo_doc_app_id', '=', $this->doc_cxp_tipo_doc_id ],
                                        [ 'consecutivo', '=', $this->doc_cxp_consecutivo ]
                                    ])
                                    ->get()
                                    ->first();
    }
}
