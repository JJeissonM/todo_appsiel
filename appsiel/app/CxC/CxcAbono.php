<?php

namespace App\CxC;

use App\Sistema\TipoTransaccion;
use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class CxcAbono extends Model
{
    // Tabla auxiliar para llevar el registro de los abonos a los documentos de cxc.
    // Normalmente son documentos de Recaudos de Tesorería
    protected $table = 'cxc_abonos';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'core_empresa_id', 'core_tercero_id', 'modelo_referencia_tercero_index', 'referencia_tercero_id', 'fecha', 'doc_cxc_transacc_id', 'doc_cxc_tipo_doc_id', 'doc_cxc_consecutivo', 'doc_cruce_transacc_id', 'doc_cruce_tipo_doc_id', 'doc_cruce_consecutivo', 'abono', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento recaudo', 'Proveedor', 'Documento CxC Abonado', 'Doc. cruce', 'Valor abono'];

    public $urls_acciones = '{"show":"no"}';

    public function account_receivable_document_header()
    {
        $transaction = TipoTransaccion::find($this->doc_cxc_transacc_id);
        return app($transaction->model->name_space)->where([
            ['core_tipo_transaccion_id','=',$this->doc_cxc_transacc_id],
            ['core_tipo_doc_app_id','=',$this->doc_cxc_tipo_doc_id],
            ['consecutivo','=',$this->doc_cxc_consecutivo],
            ])
            ->get()->first();
    }

    public static function consultar_registros( $nro_registros, $search )
    {
        $collection = CxcAbono::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_abonos.core_tipo_doc_app_id')
                        ->leftJoin('core_tipos_docs_apps AS tipo_docs_cxc', 'tipo_docs_cxc.id', '=', 'cxc_abonos.doc_cxc_tipo_doc_id')
                        ->leftJoin('core_tipos_docs_apps AS tipo_docs_cruce', 'tipo_docs_cruce.id', '=', 'cxc_abonos.doc_cruce_tipo_doc_id')
                        ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_abonos.core_tercero_id')
                        ->where('cxc_abonos.core_empresa_id', Auth::user()->empresa_id)
                        ->select(
                            'cxc_abonos.fecha AS campo1',
                            DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_abonos.consecutivo) AS campo2'),
                            DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                            DB::raw('CONCAT(tipo_docs_cxc.prefijo," ",cxc_abonos.doc_cxc_consecutivo) AS campo4'),
                            DB::raw('CONCAT(tipo_docs_cruce.prefijo," ",cxc_abonos.doc_cruce_consecutivo) AS campo5'),
                            'cxc_abonos.abono AS campo6',
                            'cxc_abonos.id AS campo7'
                        )
                        ->orderBy('cxc_abonos.created_at', 'DESC')
                        ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if ( self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7], $search)) {
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
        $string = CxcAbono::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_abonos.core_tipo_doc_app_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_docs_cxc', 'tipo_docs_cxc.id', '=', 'cxc_abonos.doc_cxc_tipo_doc_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_abonos.core_tercero_id')
            ->where('cxc_abonos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'cxc_abonos.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_abonos.consecutivo) AS DOCUMENTO_ABONO'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS TERCERO'),
                DB::raw('CONCAT(tipo_docs_cxc.prefijo," ",cxc_abonos.doc_cxc_consecutivo) AS DOCUMENTO_CARTERA'),
                'cxc_abonos.abono AS VALOR_ABONO',
                'cxc_abonos.id AS ID_REGISTRO'
            )
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_abonos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("cxc_abonos.fecha", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orderBy('cxc_abonos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCUMENTOS ABONADOS DE CXC";
    }


    /*
        Obtener datos de las FACTURA ( o documentos de CxC) afectadas por un recaudo específico 
    */
    public static function get_documentos_abonados($doc_recaudo_encabezado)
    {

        return CxcAbono::where('cxc_abonos.core_tipo_transaccion_id', $doc_recaudo_encabezado->core_tipo_transaccion_id)
            ->where('cxc_abonos.core_tipo_doc_app_id', $doc_recaudo_encabezado->core_tipo_doc_app_id)
            ->where('cxc_abonos.consecutivo', $doc_recaudo_encabezado->consecutivo)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_abonos.doc_cxc_tipo_doc_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_abonos.core_tercero_id')
            ->select(
                'cxc_abonos.id',
                'cxc_abonos.core_empresa_id',
                'cxc_abonos.core_tercero_id',
                'cxc_abonos.referencia_tercero_id',
                'cxc_abonos.doc_cxc_transacc_id',
                'cxc_abonos.doc_cxc_tipo_doc_id',
                'cxc_abonos.doc_cxc_consecutivo',
                'cxc_abonos.fecha',
                'cxc_abonos.abono',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_abonos.doc_cxc_consecutivo) AS documento_prefijo_consecutivo'),
                'core_terceros.descripcion AS tercero_nombre_completo',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1'
            )
            ->get();
    }


    /*
     * Obtener datos de los RECAUDOS hecho a una FACTURA (o documento de CxC) específica
     */
    public static function get_abonos_documento($doc_cxc_encabezado)
    {
        return CxcAbono::where('cxc_abonos.doc_cxc_transacc_id', $doc_cxc_encabezado->core_tipo_transaccion_id)
            ->where('cxc_abonos.doc_cxc_tipo_doc_id', $doc_cxc_encabezado->core_tipo_doc_app_id)
            ->where('cxc_abonos.doc_cxc_consecutivo', $doc_cxc_encabezado->consecutivo)
            ->leftJoin('core_tipos_docs_apps as doc_recaudo', 'doc_recaudo.id', '=', 'cxc_abonos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_abonos.core_tercero_id')
            ->select(
                'cxc_abonos.id',
                'cxc_abonos.core_empresa_id',
                'cxc_abonos.core_tercero_id',
                'cxc_abonos.core_tipo_transaccion_id',
                'cxc_abonos.core_tipo_doc_app_id',
                'cxc_abonos.consecutivo',
                'cxc_abonos.fecha',
                'cxc_abonos.abono',
                'doc_recaudo.descripcion AS documento_transaccion_descripcion',
                DB::raw('CONCAT(doc_recaudo.prefijo," ",cxc_abonos.consecutivo) AS documento_prefijo_consecutivo'),
                'core_terceros.descripcion AS tercero_nombre_completo',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1'
            )
            ->get();
    }
}
