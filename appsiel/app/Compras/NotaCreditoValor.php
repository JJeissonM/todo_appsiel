<?php

namespace App\Compras;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotaCreditoValor extends ComprasDocEncabezado
{
    protected $table = 'compras_doc_encabezados';

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento compra', 'Proveedor', 'Factura', 'Detalle', 'Valor total', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        return static::consulta_listado($search)->paginate($nro_registros);
    }

    protected static function consulta_listado($search, $exportar = false)
    {
        $documento = "CONCAT(COALESCE(core_tipos_docs_apps.prefijo, ''), ' ', compras_doc_encabezados.consecutivo)";
        $proveedor = "COALESCE(NULLIF(TRIM(core_terceros.razon_social), ''),
            NULLIF(TRIM(CONCAT_WS(' ', NULLIF(TRIM(core_terceros.nombre1), ''),
                NULLIF(TRIM(core_terceros.otros_nombres), ''), NULLIF(TRIM(core_terceros.apellido1), ''),
                NULLIF(TRIM(core_terceros.apellido2), ''))), ''),
            NULLIF(TRIM(core_terceros.descripcion), ''), '')";
        $factura = "CASE WHEN factura_relacionada.id IS NOT NULL
            THEN CONCAT(COALESCE(tipo_doc_factura.prefijo, ''), ' ', factura_relacionada.consecutivo)
            ELSE 'Sin factura relacionada' END";
        // Las notas históricas pueden tener solo el importe de la última línea en el encabezado.
        $total = "COALESCE((SELECT SUM(lineas_nota.precio_total) FROM compras_doc_registros AS lineas_nota
            WHERE lineas_nota.compras_doc_encabezado_id = compras_doc_encabezados.id), compras_doc_encabezados.valor_total)";

        $campos = [
            'compras_doc_encabezados.fecha', $documento, $proveedor, $factura,
            'compras_doc_encabezados.descripcion', $total, 'compras_doc_encabezados.estado',
        ];
        $alias = $exportar
            ? ['FECHA', 'DOCUMENTO_COMPRA', 'PROVEEDOR', 'FACTURA', 'DETALLE', 'VALOR_TOTAL', 'ESTADO']
            : ['campo1', 'campo2', 'campo3', 'campo4', 'campo5', 'campo6', 'campo7'];
        $seleccion = [];
        foreach ($campos as $i => $campo) {
            $seleccion[] = DB::raw($campo . ' AS ' . $alias[$i]);
        }
        if (!$exportar) {
            $seleccion[] = 'compras_doc_encabezados.id AS campo8';
        }

        $query = static::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            ->leftJoin('compras_doc_encabezados AS factura_relacionada', function ($join) {
                $join->on('factura_relacionada.id', '=', 'compras_doc_encabezados.compras_doc_relacionado_id')
                    ->on('factura_relacionada.core_empresa_id', '=', 'compras_doc_encabezados.core_empresa_id');
            })
            ->leftJoin('core_tipos_docs_apps AS tipo_doc_factura', 'tipo_doc_factura.id', '=', 'factura_relacionada.core_tipo_doc_app_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', 61)
            ->select($seleccion);

        $search = trim((string)$search);
        if ($search !== '') {
            $query->where(function ($filtro) use ($campos, $search) {
                foreach ($campos as $campo) {
                    $filtro->orWhere(DB::raw($campo), 'LIKE', '%' . $search . '%');
                }
                $filtro->orWhere('core_terceros.numero_identificacion', 'LIKE', '%' . $search . '%')
                    ->orWhere('core_terceros.descripcion', 'LIKE', '%' . $search . '%');
            });
        }

        return $query->orderBy('compras_doc_encabezados.created_at', 'DESC')
            ->orderBy('compras_doc_encabezados.id', 'DESC');
    }

    public static function sqlString($search)
    {
        $query = static::consulta_listado($search, true);
        $bindings = $query->getBindings();
        $pdo = $query->getConnection()->getPdo();
        $i = 0;
        // Exportar los mismos filtros, incluyendo empresa y transacción, con sus valores reales.
        return preg_replace_callback('/\?/', function () use ($bindings, $pdo, &$i) {
            return $pdo->quote((string)$bindings[$i++]);
        }, $query->toSql());
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE NOTAS CREDITO";
    }

    /*
        Obtener todas las notas crédito aplicadas a la factura
    */
    public static function get_notas_aplicadas_factura( $doc_encabezado_factura_id )
    {

        return NotaCreditoValor::where('compras_doc_encabezados.compras_doc_relacionado_id',$doc_encabezado_factura_id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
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
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS documento_prefijo_consecutivo' ),
                                'core_terceros.descripcion AS tercero_nombre_completo',
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get();
    }
}
