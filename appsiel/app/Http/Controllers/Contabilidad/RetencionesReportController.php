<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Contabilidad\Services\Retenciones\ConceptosDeclaracion;
use App\Contabilidad\Services\Retenciones\RetencionesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class RetencionesReportController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public static function autorizar($permiso = 'contab_reporte_retenciones')
    {
        $usuario = Auth::user();
        abort_unless($usuario, 401);
        $roles = array_unique(array_merge(['SuperAdmin', 'Administrador'], (array)config('filtrado_registros.roles_sin_filtro', [])));
        foreach ($roles as $rol) {
            if ($rol && $usuario->hasRole($rol)) { return; }
        }
        abort_unless($usuario->can($permiso), 403);
    }

    public static function cacheKey($reporteId)
    {
        return 'pdf_retenciones_'.$reporteId.'_empresa_'.Auth::user()->empresa_id.'_usuario_'.Auth::id();
    }

    public function generar(Request $request)
    {
        static::autorizar();
        $reporte = DB::table('sys_reportes')->where('url_form_action', 'contab_reporte_retenciones')->first();
        $instancia = json_decode($request->input('reporte_instancia', '{}'));
        if (!$reporte || (isset($instancia->id) && (int)$instancia->id !== (int)$reporte->id)) {
            return '<div class="alert alert-danger">Seleccione el reporte de retenciones registrado en Contabilidad.</div>';
        }
        Cache::forget(static::cacheKey($reporte->id));
        $validacion = Validator::make($request->all(), [
            'fecha_desde' => 'required|date_format:Y-m-d', 'fecha_hasta' => 'required|date_format:Y-m-d',
            'tipo_impuesto' => 'in:FUENTE,ICA,IVA,SIN_CLASIFICAR', 'naturaleza' => 'in:practicada,sufrida',
            'tipo_persona' => 'in:natural,juridica,sin_clasificar', 'vista' => 'in:completo,resumen,terceros,detalle',
            'detalla_terceros' => 'in:Si,Sí,No',
            'tercero_id' => 'integer|min:1', 'retencion_id' => 'integer|min:1',
            'tarifa' => 'numeric|min:0|max:100', 'codigo_municipio' => 'regex:/^[0-9]{5}$/',
            'concepto' => 'in:'.implode(',', array_keys(ConceptosDeclaracion::catalogo()))
        ]);
        if ($validacion->fails() || $request->fecha_desde > $request->fecha_hasta) {
            return '<div class="alert alert-danger">Revise las fechas y los filtros del reporte. La fecha inicial no puede superar la final.</div>';
        }
        if (!Schema::hasTable('contab_retenciones_reporte_config') || !Schema::hasColumn('contab_registros_retenciones', 'core_empresa_id')) {
            return '<div class="alert alert-danger">Debe ejecutar las migraciones del reporte de retenciones.</div>';
        }
        $empresaId = Auth::user()->empresa_id;
        if ($request->tercero_id && !DB::table('core_terceros')->where('id', $request->tercero_id)->where('core_empresa_id', $empresaId)->exists()) {
            return '<div class="alert alert-danger">El tercero seleccionado no pertenece a la empresa actual.</div>';
        }
        $filtros = array_merge([
            'naturaleza' => 'practicada', 'tipo_impuesto' => '', 'vista' => 'completo',
            'tipo_persona' => '', 'tercero_id' => '', 'retencion_id' => '',
            'codigo_municipio' => '', 'tarifa' => '', 'concepto' => ''
        ], $request->only('fecha_desde', 'fecha_hasta', 'naturaleza', 'tipo_impuesto', 'vista', 'tipo_persona',
            'tercero_id', 'retencion_id', 'codigo_municipio', 'tarifa', 'concepto'));
        foreach ($filtros as $campo => $valor) {
            if ($valor === null) { $filtros[$campo] = ''; }
        }
        if (!$filtros['naturaleza']) { $filtros['naturaleza'] = 'practicada'; }
        if (!$filtros['vista']) { $filtros['vista'] = 'completo'; }
        $filtros['detalla_terceros'] = $request->input('detalla_terceros', 'Si') === 'No' ? 'No' : 'Si';
        $datos = (new RetencionesReportService)->generar($empresaId, $filtros);
        $datos['empresa'] = DB::table('core_empresas')->where('id', $empresaId)->first();
        $html = view('contabilidad.retenciones.reporte', $datos)->render();
        Cache::put(static::cacheKey($reporte->id), $html, 120);
        return $html;
    }

    public function configuracion(Request $request)
    {
        static::autorizar('contab_configurar_retenciones');
        $anio = $request->input('anio', date('Y'));
        abort_unless(ctype_digit((string)$anio) && $anio >= 2000 && $anio <= 2100, 422);
        $configuraciones = DB::table('contab_retenciones_reporte_config')->where('core_empresa_id', Auth::user()->empresa_id)
            ->where('anio', $anio)->get();
        $config = [];
        foreach ($configuraciones as $fila) { $config[$fila->contab_retencion_id] = $fila; }
        $retenciones = DB::table('contab_retenciones')->orderBy('descripcion')->get();
        $conceptos = ConceptosDeclaracion::opciones();
        $miga_pan = [['url' => 'contabilidad?id='. $request->input('id'), 'etiqueta' => 'Contabilidad'],
            ['url' => 'NO', 'etiqueta' => 'Clasificación fiscal de retenciones']];
        return view('contabilidad.retenciones.configuracion', compact('anio', 'config', 'retenciones', 'conceptos', 'miga_pan'));
    }

    public function guardarConfiguracion(Request $request)
    {
        static::autorizar('contab_configurar_retenciones');
        $this->validate($request, [
            'anio' => 'required|integer|between:2000,2100', 'contab_retencion_id' => 'required|exists:contab_retenciones,id',
            'tipo_impuesto' => 'required|in:FUENTE,ICA,IVA',
            'concepto' => 'required|in:'.implode(',', array_keys(ConceptosDeclaracion::catalogo())),
            'codigo_municipio' => 'required_if:tipo_impuesto,ICA|regex:/^[0-9]{5}$/',
            'actividad_ica' => 'required_if:tipo_impuesto,ICA|max:30'
        ]);
        $concepto = $request->concepto;
        $tipo = $request->tipo_impuesto;
        $esIva = strpos($concepto, 'iva_') === 0 || $concepto === 'devolucion_iva';
        abort_unless(($tipo === 'ICA' && $concepto === 'ica') || ($tipo === 'IVA' && $esIva)
            || ($tipo === 'FUENTE' && !$esIva && $concepto !== 'ica'), 422);
        $clave = ['core_empresa_id' => Auth::user()->empresa_id, 'contab_retencion_id' => $request->contab_retencion_id, 'anio' => $request->anio];
        DB::transaction(function () use ($clave, $request, $tipo) {
            // Serializa cambios para una empresa sin crear clasificaciones duplicadas.
            DB::table('core_empresas')->where('id', $clave['core_empresa_id'])->lockForUpdate()->first();
            $query = DB::table('contab_retenciones_reporte_config')->where($clave);
            $datos = ['tipo_impuesto' => $tipo, 'concepto' => $request->concepto,
                'codigo_municipio' => $tipo === 'ICA' ? $request->codigo_municipio : null,
                'actividad_ica' => $tipo === 'ICA' ? $request->actividad_ica : null,
                'modificado_por' => Auth::user()->email, 'updated_at' => date('Y-m-d H:i:s')];
            if ($query->exists()) { $query->update($datos); }
            else { DB::table('contab_retenciones_reporte_config')->insert(array_merge($clave, $datos, ['created_at' => date('Y-m-d H:i:s')])); }
        });
        return redirect('contab_retenciones/configuracion?id='.$request->input('id').'&anio='.$request->anio)
            ->with('flash_message', 'Clasificación fiscal guardada.');
    }
}
