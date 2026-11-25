<?php

namespace App\FacturacionElectronica;

use App\Compras\ComprasDocEncabezado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Nota de ajuste al documento soporte (DIAN CreditNote 95): otro registro de compras_doc_encabezados.
class NotaAjusteDocSoporte extends ComprasDocEncabezado
{
    const TRANSACCION_ID = 62;
    const TRANSACCION_DOC_SOPORTE_ID = 48;
    const CONCEPTOS = [
        1 => 'Devolución parcial de los bienes y/o no aceptación parcial del servicio',
        2 => 'Anulación del documento soporte',
        3 => 'Rebaja o descuento parcial o total',
        4 => 'Ajuste de precio',
        5 => 'Otros',
    ];

    protected $table = 'compras_doc_encabezados';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Proveedor', 'Documento soporte', 'Detalle', 'Valor total', 'Estado'];

    public function __construct(array $attributes = [])
    {
        $this->fillable[] = 'nota_ajuste_concepto';
        parent::__construct($attributes);
    }

    public function doc_soporte()
    {
        return $this->belongsTo(DocSoporte::class, 'compras_doc_relacionado_id');
    }

    public static function notas_activas_de($doc_soporte_id)
    {
        return static::where('core_tipo_transaccion_id', self::TRANSACCION_ID)
            ->where('compras_doc_relacionado_id', $doc_soporte_id)
            ->where('estado', '<>', 'Anulado')
            ->get();
    }

    public function concepto_descripcion()
    {
        return isset(self::CONCEPTOS[$this->nota_ajuste_concepto]) ? self::CONCEPTOS[$this->nota_ajuste_concepto] : '';
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return static::consulta_listado($search)->paginate($nro_registros);
    }

    protected static function consulta_listado($search, $exportar = false)
    {
        $campos = [
            'compras_doc_encabezados.fecha',
            'CONCAT(core_tipos_docs_apps.prefijo, " ", compras_doc_encabezados.consecutivo)',
            'core_terceros.descripcion',
            'CONCAT(tipo_doc_ds.prefijo, " ", ds.consecutivo)',
            'compras_doc_encabezados.descripcion',
            'compras_doc_encabezados.valor_total',
            'compras_doc_encabezados.estado',
        ];
        $alias = $exportar
            ? ['FECHA', 'DOCUMENTO', 'PROVEEDOR', 'DOCUMENTO_SOPORTE', 'DETALLE', 'VALOR_TOTAL', 'ESTADO']
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
            ->leftJoin('compras_doc_encabezados AS ds', 'ds.id', '=', 'compras_doc_encabezados.compras_doc_relacionado_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_doc_ds', 'tipo_doc_ds.id', '=', 'ds.core_tipo_doc_app_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', self::TRANSACCION_ID)
            ->select($seleccion);

        $search = trim((string)$search);
        if ($search !== '') {
            $query->where(function ($filtro) use ($campos, $search) {
                foreach ($campos as $campo) {
                    $filtro->orWhere(DB::raw($campo), 'LIKE', '%' . $search . '%');
                }
            });
        }

        return $query->orderBy('compras_doc_encabezados.created_at', 'DESC')->orderBy('compras_doc_encabezados.id', 'DESC');
    }

    public static function sqlString($search)
    {
        $query = static::consulta_listado($search, true);
        $bindings = $query->getBindings();
        $pdo = $query->getConnection()->getPdo();
        $i = 0;
        return preg_replace_callback('/\?/', function () use ($bindings, $pdo, &$i) {
            return $pdo->quote((string)$bindings[$i++]);
        }, $query->toSql());
    }

    public static function tituloExport()
    {
        return 'LISTADO DE NOTAS DE AJUSTE AL DOCUMENTO SOPORTE';
    }
}
