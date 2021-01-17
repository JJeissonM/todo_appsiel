<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoPlanPagosEstudiante;
use App\Tesoreria\TesoLibretasPago;

class TesoRecaudosLibreta extends Model
{
    public $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'id_libreta', 'id_cartera', 'concepto', 'fecha_recaudo', 'teso_medio_recaudo_id', 'cantidad_cuotas', 'valor_recaudo', 'mi_token', 'creado_por', 'modificado_por'];

    public $urls_acciones = '{"show":"no"}';

    public function tipo_documento_app()
    {
        return $this->belongsTo('App\Core\TipoDocApp', 'core_tipo_doc_app_id');
    }

    public function libreta()
    {
        return $this->belongsTo(TesoLibretasPago::class, 'id_libreta');
    }

    public function elconcepto()
    {
        return $this->belongsTo('App\Inventarios\InvProducto', 'concepto');
    }

    public function registro_cartera_estudiante()
    {
        return $this->belongsTo(TesoPlanPagosEstudiante::class, 'id_cartera');
    }

    public function recaudo_tesoreria()
    {
        return TesoDocEncabezado::where('core_tipo_transaccion_id', $this->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $this->core_tipo_doc_app_id)
            ->where('consecutivo', $this->consecutivo)
            ->where('estado', 'Activo')
            ->get()
            ->first();
    }

    public function anular()
    {
        $this->actualizar_plan_pagos_estudiante($this);
        $this->actualizar_estado_libreta_pago($this->id_libreta);
        $this->delete();
    }

    public function actualizar_estado_libreta_pago($id_libreta)
    {
        $suma_matriculas = TesoPlanPagosEstudiante::get_total_valor_pagado_concepto($id_libreta, config('matriculas.inv_producto_id_default_matricula'));
        $suma_pensiones = TesoPlanPagosEstudiante::get_total_valor_pagado_concepto($id_libreta, config('matriculas.inv_producto_id_default_pension'));
        $total_pagado = $suma_matriculas + $suma_pensiones;
        $libreta = TesoLibretasPago::find($id_libreta);
        $total_libreta = $libreta->valor_matricula + $libreta->valor_pension_anual;
        if ($total_pagado == $total_libreta) {
            $libreta->estado = "Inactivo";
            $libreta->save();
        }
    }

    public function actualizar_plan_pagos_estudiante(TesoRecaudosLibreta $recaudo_libreta)
    {
        $cartera = TesoPlanPagosEstudiante::find($recaudo_libreta->id_cartera);
        $nuevo_valor_pagado = $cartera->valor_pagado - $recaudo_libreta->valor_recaudo;
        $saldo_pendiente = $cartera->saldo_pendiente + $recaudo_libreta->valor_recaudo;
        $estado = $cartera->estado;

        if ($nuevo_valor_pagado == $cartera->valor_cartera) {
            $estado = "Pagada";
        } else {
            $estado = "Pendiente";
        }

        $cartera->valor_pagado = $nuevo_valor_pagado;
        $cartera->saldo_pendiente = $saldo_pendiente;
        $cartera->estado = $estado;
        $cartera->save();
    }

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Estudiante', 'No. Identificacion', 'Detalle', 'Valor'];

    public static function consultar_registros($nro_registros, $search)
    {
        return TesoRecaudosLibreta::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_recaudos_libretas.core_tipo_doc_app_id')
            ->leftJoin('teso_cartera_estudiantes', 'teso_cartera_estudiantes.id', '=', 'teso_recaudos_libretas.id_cartera')
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'teso_cartera_estudiantes.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->select(
                'teso_recaudos_libretas.fecha_recaudo AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_recaudos_libretas.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo3'),
                'core_terceros.numero_identificacion AS campo4',
                'teso_recaudos_libretas.concepto AS campo5',
                'teso_recaudos_libretas.valor_recaudo AS campo6',
                'teso_cartera_estudiantes.id AS campo7'
            )  // OJO, no es el ID del modelo
            ->where("teso_recaudos_libretas.fecha_recaudo", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_recaudos_libretas.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("teso_recaudos_libretas.concepto", "LIKE", "%$search%")
            ->orWhere("teso_recaudos_libretas.valor_recaudo", "LIKE", "%$search%")
            ->orderBy('teso_recaudos_libretas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $transaccion_id = 57;
        $string = TesoRecaudosLibreta::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_recaudos_libretas.core_tipo_doc_app_id')
        ->leftJoin('teso_cartera_estudiantes', 'teso_cartera_estudiantes.id', '=', 'teso_recaudos_libretas.id_cartera')
        ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'teso_cartera_estudiantes.id_estudiante')
        ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
        ->select(
            'teso_recaudos_libretas.fecha_recaudo AS FECHA',
            DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_recaudos_libretas.consecutivo) AS DOCUMENTO'),
            DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS ESTUDIANTE'),
            'core_terceros.numero_identificacion AS IDENTIFICACIÓN',
            'teso_recaudos_libretas.concepto AS CONCEPTO',
            'teso_recaudos_libretas.valor_recaudo AS VALOR'
        )  // OJO, no es el ID del modelo
        ->where("teso_recaudos_libretas.fecha_recaudo", "LIKE", "%$search%")
        ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_recaudos_libretas.consecutivo)'), "LIKE", "%$search%")
        ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2)'), "LIKE", "%$search%")
        ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
        ->orWhere("teso_recaudos_libretas.concepto", "LIKE", "%$search%")
        ->orWhere("teso_recaudos_libretas.valor_recaudo", "LIKE", "%$search%")
        ->orderBy('teso_recaudos_libretas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE RECAUDOS DE LIBRETA";
    }
}
