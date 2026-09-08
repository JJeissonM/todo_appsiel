<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class ContabDocEncabezado extends Model
{
    //protected $table = 'contab_doc_encabezados'; 

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'documento_soporte', 'descripcion', 'valor_total', 'estado', 'creado_por', 'modificado_por', 'codigo_referencia_tercero'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Tercero', 'Detalle', 'Valor documento', 'Estado'];

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

    public function registros()
    {
        return $this->hasMany('App\Contabilidad\ContabDocRegistro');
    }

    public function lineas_registros()
    {
        return $this->hasMany(ContabDocRegistro::class, 'contab_doc_encabezado_id');
    }

    public function actualizar_valor_total()
    {
        $this->valor_total = $this->lineas_registros->sum('valor_debito');
        $this->save();
    }
    
    public function get_movimiento_contable()
    {
        return ContabMovimiento::where([
                                        ['core_tipo_transaccion_id', '=', $this->core_tipo_transaccion_id],
                                        ['core_tipo_doc_app_id', '=', $this->core_tipo_doc_app_id],
                                        ['consecutivo', '=', $this->consecutivo]
                                    ])
                                ->get();
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return self::aplicar_filtros_index(self::query_listado()
            ->select(
                'contab_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS campo2'),
                'core_terceros.descripcion AS campo3',
                'contab_doc_encabezados.descripcion AS campo4',
                'contab_doc_encabezados.valor_total AS campo5',
                'contab_doc_encabezados.estado AS campo6',
                'contab_doc_encabezados.id AS campo7'
            ), $search)
            ->orderBy('contab_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $query = self::aplicar_filtros_index(self::query_listado()
            ->select(
                'contab_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS DOCUMENTO'),
                'core_terceros.descripcion AS TERCERO',
                'contab_doc_encabezados.descripcion AS DETALLE',
                'contab_doc_encabezados.valor_total AS VALOR_DOCUMENTO',
                'contab_doc_encabezados.estado AS ESTADO'
            ), $search)
            ->orderBy('contab_doc_encabezados.created_at', 'DESC');

        return self::sql_con_bindings($query);
    }

    public static function get_filtros_avanzados_index()
    {
        return [
            'filtro_tipo_documento' => [
                'label' => 'Tipo de documento',
                'type' => 'combobox',
                'options' => ['' => 'Todos'] + self::opciones_tipos_documentos()
            ],
            'filtro_consecutivo' => ['label' => 'Consecutivo', 'type' => 'text'],
            'filtro_fecha' => ['label' => 'Fecha', 'type' => 'text'],
            'filtro_tercero' => [
                'label' => 'Tercero',
                'type' => 'combobox',
                'options' => ['' => 'Todos'] + self::opciones_terceros()
            ],
            'filtro_descripcion' => ['label' => 'Descripción', 'type' => 'text'],
            'filtro_estado' => [
                'label' => 'Estado',
                'type' => 'select',
                'options' => ['' => 'Todos'] + self::opciones_filtro(
                    'contab_doc_encabezados.estado',
                    'contab_doc_encabezados.estado',
                    'estado_filtro'
                )
            ]
        ];
    }

    protected static function query_listado()
    {
        $query = self::leftJoin(
                'core_tipos_docs_apps',
                'core_tipos_docs_apps.id',
                '=',
                'contab_doc_encabezados.core_tipo_doc_app_id'
            )
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_doc_encabezados.core_tercero_id')
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'contab_doc_encabezados.core_empresa_id');

        if (Auth::check()) {
            $query->where('contab_doc_encabezados.core_empresa_id', Auth::user()->empresa_id);
        }

        return $query;
    }

    protected static function aplicar_filtros_index($query, $search)
    {
        if ($search !== '') {
            $query->where(function ($subquery) use ($search) {
                $like = '%' . $search . '%';
                $subquery->where('contab_doc_encabezados.fecha', 'LIKE', $like)
                    ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo)'), 'LIKE', $like)
                    ->orWhere('core_terceros.descripcion', 'LIKE', $like)
                    ->orWhere('contab_doc_encabezados.documento_soporte', 'LIKE', $like)
                    ->orWhere('contab_doc_encabezados.descripcion', 'LIKE', $like)
                    ->orWhere('contab_doc_encabezados.valor_total', 'LIKE', $like)
                    ->orWhere('contab_doc_encabezados.estado', 'LIKE', $like);
            });
        }

        $filtros_exactos = [
            'filtro_tipo_documento' => 'contab_doc_encabezados.core_tipo_doc_app_id',
            'filtro_empresa' => 'contab_doc_encabezados.core_empresa_id',
            'filtro_tercero' => 'contab_doc_encabezados.core_tercero_id',
            'filtro_valor_total' => 'contab_doc_encabezados.valor_total',
            'filtro_estado' => 'contab_doc_encabezados.estado'
        ];

        foreach ($filtros_exactos as $parametro => $columna) {
            $valor = trim((string)Input::get($parametro, ''));
            if ($valor !== '') {
                $query->where($columna, $valor);
            }
        }

        $filtros_like = [
            'filtro_consecutivo' => 'contab_doc_encabezados.consecutivo',
            'filtro_fecha' => 'contab_doc_encabezados.fecha',
            'filtro_documento_soporte' => 'contab_doc_encabezados.documento_soporte',
            'filtro_descripcion' => 'contab_doc_encabezados.descripcion'
        ];

        foreach ($filtros_like as $parametro => $columna) {
            $valor = trim((string)Input::get($parametro, ''));
            if ($valor !== '') {
                $query->where($columna, 'LIKE', '%' . $valor . '%');
            }
        }

        return $query;
    }

    protected static function opciones_tipos_documentos()
    {
        $registros = self::query_listado()
            ->whereNotNull('core_tipos_docs_apps.id')
            ->select(
                'core_tipos_docs_apps.id AS tipo_documento_id',
                'core_tipos_docs_apps.prefijo',
                'core_tipos_docs_apps.descripcion'
            )
            ->distinct()
            ->orderBy('core_tipos_docs_apps.descripcion')
            ->get();

        $opciones = [];
        foreach ($registros as $registro) {
            $opciones[$registro->tipo_documento_id] = trim(
                $registro->prefijo . ' - ' . $registro->descripcion
            );
        }

        return $opciones;
    }

    protected static function opciones_terceros()
    {
        $registros = self::query_listado()
            ->whereNotNull('core_terceros.id')
            ->select(
                'core_terceros.id AS tercero_id',
                'core_terceros.numero_identificacion',
                'core_terceros.descripcion'
            )
            ->distinct()
            ->orderBy('core_terceros.descripcion')
            ->get();

        $opciones = [];
        foreach ($registros as $registro) {
            $opciones[$registro->tercero_id] = $registro->descripcion
                . ' (' . $registro->numero_identificacion . ')';
        }

        return $opciones;
    }

    protected static function opciones_filtro($id, $descripcion, $alias)
    {
        return self::query_listado()
            ->whereNotNull($id)
            ->select($id . ' AS ' . $alias, $descripcion . ' AS descripcion_filtro')
            ->distinct()
            ->orderBy('descripcion_filtro')
            ->lists('descripcion_filtro', $alias)
            ->all();
    }

    protected static function sql_con_bindings($query)
    {
        $sql = $query->toSql();
        $pdo = DB::connection()->getPdo();

        foreach ($query->getBindings() as $binding) {
            $valor = is_numeric($binding) ? $binding : $pdo->quote($binding);
            $sql = preg_replace('/\?/', $valor, $sql, 1);
        }

        return $sql;
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCEMENTOS CONTABLES";
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {

        return ContabDocEncabezado::where('contab_doc_encabezados.id', $id)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_doc_encabezados.core_tercero_id')
            ->select(
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
                'contab_doc_encabezados.fecha',
                'core_terceros.descripcion AS tercero_nombre_completo',
                'contab_doc_encabezados.descripcion',
                'contab_doc_encabezados.core_tercero_id',
                'contab_doc_encabezados.documento_soporte',
                'contab_doc_encabezados.core_tipo_transaccion_id',
                'contab_doc_encabezados.core_tipo_doc_app_id',
                'contab_doc_encabezados.id',
                'contab_doc_encabezados.creado_por',
                'contab_doc_encabezados.created_at',
                'contab_doc_encabezados.consecutivo',
                'contab_doc_encabezados.core_empresa_id',
                'contab_doc_encabezados.valor_total',
                'contab_doc_encabezados.estado',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                'core_terceros.numero_identificacion'
            )
            ->get()
            ->first();
    }
}
