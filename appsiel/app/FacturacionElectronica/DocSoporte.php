<?php

namespace App\FacturacionElectronica;

use App\Compras\ComprasDocEncabezado;
use App\FacturacionElectronica\DATAICO\DocSoporte as DATAICODocSoporte;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocSoporte extends ComprasDocEncabezado
{
    protected $table = 'compras_doc_encabezados';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'cotizacion_id', 'compras_doc_relacionado_id', 'entrada_almacen_id', 'proveedor_id', 'comprador_id', 'forma_pago', 'fecha_recepcion', 'fecha_vencimiento', 'doc_proveedor_prefijo', 'doc_proveedor_consecutivo', 'descripcion', 'creado_por', 'modificado_por', 'estado','valor_total'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Proveedor', 'Fact. Proveedor', 'Detalle', 'Valor total',  'Forma de pago', 'Estado'];

    //public $urls_acciones = '{"create":"web/create","store":"fe_factura","show":"fe_factura/id_fila"}';

    //public $vistas = '{"index":"layouts.index3","create":"facturacion_electronica.facturas.create"}';

    // ¡Extiende métodos!

    public static function consultar_registros2($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 52; // Factura Electrónica
        $collection = ComprasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'compras_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('core_terceros.descripcion AS campo3'),
                'compras_doc_encabezados.descripcion AS campo4',
                'compras_doc_encabezados.valor_total AS campo5',
                'compras_doc_encabezados.forma_pago AS campo6',
                'compras_doc_encabezados.estado AS campo7',
                'compras_doc_encabezados.id AS campo8'
            )
            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
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

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 52; // Factura Electrónica
        
        $texto_busqueda = '%' . str_replace( " ", "%", $search ) . '%';
        
        $string = ComprasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->having('nueva_cadena', 'LIKE', $texto_busqueda)
            ->select(
                DB::raw('CONCAT( compras_doc_encabezados.fecha, " ", core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo, " ", core_terceros.descripcion, " ", compras_doc_encabezados.descripcion, " ", compras_doc_encabezados.valor_total, " ", compras_doc_encabezados.forma_pago, " ", compras_doc_encabezados.estado) AS nueva_cadena'),
                'compras_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('core_terceros.numero_identificacion AS CC_NIT'),
                DB::raw('core_terceros.descripcion AS CLIENTE'),
                'compras_doc_encabezados.descripcion AS DETALLE',
                'compras_doc_encabezados.valor_total AS VALOR_TOTAL',
                'compras_doc_encabezados.forma_pago AS FORMA_PAGO',
                'compras_doc_encabezados.estado AS ESTADO'
            )
            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
            ->toSql();
            
        $string = str_replace('`compras_doc_encabezados`.`core_empresa_id` = ?', '`compras_doc_encabezados`.`core_empresa_id` = ' . Auth::user()->empresa_id, $string);
        
        $string = str_replace('`compras_doc_encabezados`.`core_tipo_transaccion_id` = ?', '`compras_doc_encabezados`.`core_tipo_transaccion_id` = ' . $core_tipo_transaccion_id, $string);

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE FACTURAS ELECTRONICA DE VENTAS";
    }

    public function enviar_al_proveedor_tecnologico()
    {
        switch ( config('facturacion_electronica.proveedor_tecnologico_default') )
        {
            case 'DATAICO':
                $factura_dataico = new DATAICODocSoporte( $this, 'support_doc' );
                $mensaje = $factura_dataico->procesar_envio_factura();
                break;
            
            default:
                // code...
                break;
        }
                
        return $mensaje;
    }

    public function validate_customer_data()
    {
        $error = false;
        $message = '<ul>';

        $tercero = $this->proveedor->tercero;

        /*
        if ($tercero->descripcion == '') {
            $error = true;
            $message .= '<li>El NOMBRE DE ESTABLECIMIENTO está vacío.</li>';
        }

        if ($tercero->nombre1 == '') {
            $error = true;
            $message .= '<li>El PRIMER NOMBRE está vacío.</li>';
        }

        if ($tercero->apellido1 == '') {
            $error = true;
            $message .= '<li>El PRIMER APELLIDO está vacío.</li>';
        }

        if ($tercero->direccion1 == '') {
            $error = true;
            $message .= '<li>La DIRECCIÓN está vacía.</li>';
        }

        if ($tercero->telefono1 == '' || (int)$tercero->telefono1 == 0) {
            $error = true;
            $message .= '<li>El TELÉFONO no es válido. Debe ser un valor numérico.: ' . $tercero->telefono1 . '</li>';
        }
        */

        if (!filter_var($tercero->email, FILTER_VALIDATE_EMAIL)) {
            $error = true;
            $message .= '<li>El EMAIL no es válido: ' . $tercero->email . '</li>';
        }

        $message .= '</ul>';

        return (object)[
            'error' => $error,
            'message' => $message
        ];
    }
}
