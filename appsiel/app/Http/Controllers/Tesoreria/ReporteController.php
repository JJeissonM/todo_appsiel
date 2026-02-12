<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;

use App\Core\Acl;

use App\Http\Controllers\Core\ConfiguracionController;
use App\Matriculas\Matricula;
use App\Matriculas\Curso;

use App\Core\Colegio;
use App\Core\TipoDocApp;
use App\Core\Tercero;
use App\Sistema\Aplicacion;
use App\Tesoreria\Services\ReportsServices;
use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoRecaudosLibreta;
use App\Tesoreria\TesoPlanPagosEstudiante;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoEntidadFinanciera;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMovimiento;
use App\Inventarios\InvProducto;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Khill\Lavacharts\Laravel\LavachartsFacade;

class ReporteController extends TesoreriaController
{

    public static function grafica_movimientos_diarios($fecha_desde, $fecha_hasta)
    {
        $registros_entradas = TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                                        ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                                        ->where('teso_motivos.movimiento', '=', 'entrada')
                                        ->where('teso_motivos.teso_tipo_motivo', '<>', 'traslado-efectivo')
                                        ->select(DB::raw('sum(teso_movimientos.valor_movimiento) AS valor_movimiento'), 'teso_movimientos.fecha')
                                        ->groupBy('fecha')
                                        ->orderBy('fecha')
                                        ->get();

        $registros_salidas = TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                                        ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                                        ->where('teso_motivos.movimiento', '=', 'salida')
                                        ->where('teso_motivos.teso_tipo_motivo', '<>', 'traslado-efectivo')
                                        ->select(DB::raw('sum(teso_movimientos.valor_movimiento) AS valor_movimiento'), 'teso_movimientos.fecha')
                                        ->groupBy('fecha')
                                        ->orderBy('fecha')
                                        ->get();


        // Crear array temporal para luego llenar el array de la grÃƒÂ¡fica
        $registros = [];
        $begin = new \DateTime($fecha_desde);
        $end   = new \DateTime($fecha_hasta);
        $k = 0;
        for ($i = $begin; $i <= $end; $i->modify('+1 day')) {
            $registros[$k]['fecha'] = $i->format("Y-m-d");

            // Para los movimientos de entradas de efectivo
            $consulta_aux = $registros_entradas->where('fecha', $i->format("Y-m-d"))->first();
            $valor_movimiento = 0;
            if (!is_null($consulta_aux)) {
                $valor_movimiento = $consulta_aux->valor_movimiento;
            }
            $registros[$k]['entradas'] = $valor_movimiento;


            // Para los movimientos de salidas de efectivo
            $consulta_aux = $registros_salidas->where('fecha', $i->format("Y-m-d"))->first();
            $valor_movimiento = 0;
            if (!is_null($consulta_aux)) {
                $valor_movimiento = $consulta_aux->valor_movimiento;
            }
            $registros[$k]['salidas'] = $valor_movimiento;

            $k++;
        }


        $stocksTable1 = LavachartsFacade::DataTable();
        $stocksTable1->addDateColumn('Fecha')
            ->addNumberColumn('Recaudos')
            ->addNumberColumn('Pagos');

        //dd( $registros );
        $i = 0;
        $tabla = [];
        foreach ($registros as $linea) {
            $stocksTable1->addRow([$linea['fecha'], $linea['entradas'], $linea['salidas'] * -1]);

            $tabla[$i]['fecha'] = $linea['fecha'];
            $tabla[$i]['valor_entradas'] = $linea['entradas'];
            $tabla[$i]['valor_salidas'] = $linea['salidas'] * -1;
            $i++;
        }

        // Se almacena la grÃƒÂ¡fica en movimiento_tesoreria, luego se llama en la vista [ como mÃƒÂ¡gia :) ]
        LavachartsFacade::BarChart('movimiento_tesoreria', $stocksTable1, [
            'is3D' => True,
            'orientation' => 'horizontal',
        ]);

        return $tabla;
    }

    public function actualizar_estado_cartera()
    {
        // 1ro. PROCESO QUE ACTUALIZA LAS CARTERAS, asignando EL ESTADO Vencida
        // Actualizar las cartera con fechas inferior a hoy y con estado distinto a Pagada
        TesoPlanPagosEstudiante::where('fecha_vencimiento', '<', date('Y-m-d'))
                                ->where('estado', '<>', 'Pagada')
                                ->update(['estado' => 'Vencida']);
    }

    public function cartera_vencida_estudiantes()
    {
        $this->actualizar_estado_cartera();

        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get();
        $curso_id = '';
        $curso_lbl = 'Todos';
        $cursos = Curso::where('id_colegio', $colegio[0]->id)->where('estado', 'Activo')->get();
        if (Input::get('curso_id') !== null) {
            $curso_id = Input::get('curso_id');
            if (Input::get('curso_id') != '') {
                $curso_lbl = Curso::find(Input::get('curso_id'))->descripcion;
            }
        }
        $vec2[''] = 'Todos';
        foreach ($cursos as $opcion) {
            $vec2[$opcion->id] = $opcion->descripcion;
        }
        $cursos = $vec2;


        if (Input::get('curso_id') == '') {
            $curso_id = '%%';
        } else {
            $curso_id = Input::get('curso_id');
        }


        // CreaciÃƒÂ³n de grÃƒÂ¡fico de Torta MATRICULAS
        $stocksTable1 = LavachartsFacade::DataTable();

        $stocksTable1->addStringColumn('Meses')
            ->addNumberColumn('Valor');

        // ObtenciÃƒÂ³n de datos
        $concepto = config('matriculas.inv_producto_id_default_matricula');
        $num_mes = "01";
        $cartera_matriculas = array();
        for ($i = 0; $i < 12; $i++) {
            if (strlen($num_mes) == 1) {
                $num_mes = "0" . $num_mes;
            }
            $cadena = "%-" . $num_mes . "-%";
            $cartera_matriculas[$num_mes] = TesoPlanPagosEstudiante::leftJoin('teso_libretas_pagos','teso_libretas_pagos.id_estudiante','=','teso_cartera_estudiantes.id_estudiante')
                ->leftJoin('sga_matriculas','sga_matriculas.id','=','teso_libretas_pagos.matricula_id')
                ->where('curso_id', 'LIKE', $curso_id)
                ->where('teso_cartera_estudiantes.fecha_vencimiento', 'LIKE', $cadena)
                ->where('teso_cartera_estudiantes.inv_producto_id', '=', $concepto)
                ->where('teso_cartera_estudiantes.estado', '=', 'Vencida')
                ->sum('teso_cartera_estudiantes.saldo_pendiente');

            // Agregar campo a la torta
            $stocksTable1->addRow([ConfiguracionController::nombre_mes($num_mes), (float) $cartera_matriculas[$num_mes]]);

            $num_mes++;
            if ($num_mes >= 13) {
                $num_mes = '01';
            }
        }

        $chart1 = LavachartsFacade::PieChart('torta_matriculas', $stocksTable1, [
            'is3D'                  => True,
            'pieSliceText'          => 'value'
        ]);


        // CreaciÃƒÂ³n de grÃƒÂ¡fico de Torta PENSIONES
        $stocksTable = LavachartsFacade::DataTable();

        $stocksTable->addStringColumn('Meses')
            ->addNumberColumn('Valor');

        // ObtenciÃƒÂ³n de datos
        $concepto = config('matriculas.inv_producto_id_default_pension');
        $num_mes = "01";
        $cartera_pensiones = array();
        for ($i = 0; $i < 12; $i++) {
            if (strlen($num_mes) == 1) {
                $num_mes = "0" . $num_mes;
            }
            $cadena = "%-" . $num_mes . "-%";
            $cartera_pensiones[$num_mes] = TesoPlanPagosEstudiante::leftJoin('teso_libretas_pagos','teso_libretas_pagos.id_estudiante','=','teso_cartera_estudiantes.id_estudiante')
                ->leftJoin('sga_matriculas','sga_matriculas.id','=','teso_libretas_pagos.matricula_id')
                ->where('curso_id', 'LIKE', $curso_id)
                ->where('teso_cartera_estudiantes.fecha_vencimiento', 'LIKE', $cadena)
                ->where('teso_cartera_estudiantes.inv_producto_id', '=', $concepto)
                ->where('teso_cartera_estudiantes.estado', '=', 'Vencida')
                ->sum('teso_cartera_estudiantes.saldo_pendiente');

            // Agregar campo a la torta
            $stocksTable->addRow([ConfiguracionController::nombre_mes($num_mes), (float) $cartera_pensiones[$num_mes]]);

            $num_mes++;
            if ($num_mes >= 13) {
                $num_mes = '01';
            }
        }

        $chart = LavachartsFacade::PieChart('torta_pensiones', $stocksTable, [
            'is3D'                  => True,
            'pieSliceText'          => 'value'
        ]);



        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Tesoreria']
        ];

        return view('tesoreria.cartera_vencida_estudiantes', compact('cartera_pensiones', 'cartera_matriculas', 'miga_pan', 'cursos', 'curso_id'));
    }

    /*
        flujo_de_efectivo
    */
    public function flujo_de_efectivo()
    {
        //$cajas = $this->get_cajas( Auth::user()->empresa_id );
        //$cuentas_bancarias = $this->get_cuentas_bancarias( Auth::user()->empresa_id );

        $registros2 = Tercero::where('core_empresa_id', '=', Auth::user()->empresa_id)->orderBy('descripcion')->get();
        $terceros[''] = '';
        foreach ($registros2 as $fila) {
            $terceros[$fila->numero_identificacion] = $fila->numero_identificacion . " " . $fila->descripcion;
        }

        $miga_pan = [
            ['url' => 'tesoreria?id=' . Input::get('id'), 'etiqueta' => 'TesorerÃƒÂ­a'],
            ['url' => 'NO', 'etiqueta' => 'Informes y listados'],
            ['url' => 'NO', 'etiqueta' => 'Flujo de efectivo']
        ];

        return view('tesoreria.flujo_de_efectivo', compact('miga_pan', 'terceros'));
    }

    /**
     * 
     */
    public function ajax_flujo_de_efectivo(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $flujo_efectivo_tipo_reporte = $request->flujo_efectivo_tipo_reporte;

        if ( $flujo_efectivo_tipo_reporte == 'columnario')
        {
            $data_array = (new ReportsServices())->build_array( $request->all() );

            $rows = count( $data_array );

            $columns = count( $data_array[0] );

            $vista = View::make('tesoreria.reportes.flujo_efectivo_columnario', compact( 'data_array', 'rows', 'columns') )->render();

            Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

            return $vista;            
        }

        //dd('stop');

        $saldo_inicial = 0;

        if( $request->incluir_saldo_anterior )
        {
            $saldo_inicial = TesoMovimiento::get_suma_movimientos_menor_a_la_fecha($fecha_desde);
        }

        $movimiento_entradas = TesoMovimiento::movimiento_por_tipo_motivo('entrada', $fecha_desde, $fecha_hasta)->toArray();
        
        $movimiento_salidas = TesoMovimiento::movimiento_por_tipo_motivo('salida', $fecha_desde, $fecha_hasta)->toArray();

        // 
        $valor_movimiento = 0;
        $this->j = 0;
        $i = 0;

        /*
            Se debe cambiar la $tabla2 por una vista que ya estÃƒÂ¡ creada, solo falta terminarla
            View::make('tesoreria.incluir.flujo_efectivo_tabla', compact( variables requeridas por la vista))
        */
        $tabla2 = '<h3> Flujo de efectivo </h3><hr><table class="table table-striped tabla_registros" style="margin-top: -4px;">
                        <thead>
                            <tr>
                                <th>
                                   &nbsp;
                                </th>
                                <th>
                                   Motivo
                                </th>
                                <th>
                                   Valor Movimiento
                                </th>
                                <th>
                                   Saldo
                                </th>
                            </tr>
                        </thead>
                        <tbody>';

        $tabla2 .= '<tr  class="fila-' . $this->j . '" >
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                               ' . number_format($saldo_inicial, 0, ',', '.') . '
                            </td>
                        </tr>';

        $this->saldo = $saldo_inicial;

        $tabla2 .= $this->seccion_tabla_movimiento('ENTRADAS', $movimiento_entradas, $saldo_inicial);

        $gran_total = $this->total_valor_movimiento;

        $tabla2 .= $this->seccion_tabla_movimiento('SALIDAS', $movimiento_salidas, $this->saldo);

        $gran_total += $this->total_valor_movimiento;

        $tabla2 .= '<tr  class="fila-' . $this->j . '" >
                            <td colspan="2">
                               <b>FLUJO NETO</b>
                            </td>
                            <td>
                               ' . number_format($gran_total, 0, ',', '.') . '
                            </td>
                            <td>
                               ' . number_format($this->saldo, 0, ',', '.') . '
                            </td>
                        </tr>';
        $tabla2 .= '</tbody></table>';

        return $tabla2;
    }

    function seccion_tabla_movimiento($etiqueta, $movimiento, $saldo_inicial)
    {
        $seccion = '';
        $this->j++;
        $this->total_valor_movimiento = 0;
        //$saldo = 0;
        for ($i = 0; $i < count($movimiento); $i++) {

            $valor_movimiento = $movimiento[$i]['valor_movimiento'];

            $this->saldo = $saldo_inicial + $valor_movimiento;

            $seccion .= '<tr class="fila-' . $this->j . '" >';

            if ($i == 0) {
                $seccion .= '<td rowspan="' . count($movimiento) . '">
                                ' . $etiqueta . '
                            </td>';
            }

            $seccion .= '<td>
                           ' . $movimiento[$i]['motivo'] . '
                        </td>
                        <td>
                           ' . number_format($valor_movimiento, 0, ',', '.') . '
                        </td>
                        <td>
                           ' . number_format($this->saldo, 0, ',', '.') . '
                        </td>
                    </tr>';

            $saldo_inicial = $this->saldo;
            $this->j++;
            if ($this->j == 3) {
                $this->j = 1;
            }
            $this->total_valor_movimiento += $valor_movimiento;
        }

        $seccion .= '<tr  class="fila-' . $this->j . '" >
                            <td colspan="2">
                               &nbsp;
                            </td>
                            <td>
                               <b>' . number_format($this->total_valor_movimiento, 0, ',', '.') . '</b>
                            </td>
                            <td>
                               ' . number_format($this->saldo, 0, ',', '.') . '
                            </td>
                        </tr>';

        return $seccion;
    }

    //   GET CAJAS
    public function get_cajas($empresa_id)
    {
        $registros = TesoCaja::where('core_empresa_id', $empresa_id)->get();
        foreach ($registros as $fila) {
            $vec_m[$fila->id] = $fila->descripcion;
        }
        return $vec_m;
    }

    //   GET CUENTAS BANCARIAS
    public function get_cuentas_bancarias($empresa_id)
    {
        $registros = TesoCuentaBancaria::where('core_empresa_id', $empresa_id)->get();
        foreach ($registros as $fila) {
            $vec_m[$fila->id] = $fila->descripcion;
        }
        return $vec_m;
    }

    //   GET MOTIVOS DE TESORERIA
    public function ajax_get_motivos($tipo_motivo)
    {
        $registros = TesoMotivo::where('teso_tipo_motivo', $tipo_motivo)
            ->where('estado', 'Activo')
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->get();
        $opciones = '';
        foreach ($registros as $campo) {
            $opciones .= '<option value="' . $campo->id . '">' . $campo->descripcion . '</option>';
        }
        return $opciones;
    }

    // AUMENTAR EL CONSECUTIVO Y OBTENERLO AUMENTADO
    public function get_consecutivo($core_empresa_id, $core_tipo_doc_app_id)
    {
        // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
        $consecutivo = TipoDocApp::get_consecutivo_actual($core_empresa_id, $core_tipo_doc_app_id) + 1;

        // Se incementa el consecutivo para ese tipo de documento y la empresa
        TipoDocApp::aumentar_consecutivo($core_empresa_id, $core_tipo_doc_app_id);

        return $consecutivo;
    }

    /*
        FORM: reporte_cartera_por_curso
    */
    public function reporte_cartera_por_curso()
    {
        $app = Aplicacion::find(Input::get('id'));

        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];

        $todos_los_cursos = Curso::where('estado', 'Activo')->where('id_colegio', $colegio->id)->OrderBy('id')->get();

        $cursos[''] = '';
        foreach ($todos_los_cursos as $fila) {
            $cursos[$fila->id] = $fila->descripcion;
        }

        $tipos_reportes = ['Resumen de recaudos', 'Cartera Vencida'];

        $miga_pan = [
            ['url' => $app->app . '?id=' . Input::get('id'), 'etiqueta' => $app->descripcion],
            ['url' => 'NO', 'etiqueta' => 'Informes y listados'],
            ['url' => 'NO', 'etiqueta' => 'Cartera por curso']
        ];

        return view('tesoreria.reporte_cartera_por_curso', compact('miga_pan', 'cursos', 'colegio', 'tipos_reportes'));
    }

    /**
     * ajax_reporte_cartera_por_curso
     *
     */
    public function ajax_reporte_cartera_por_curso(Request $request)
    {
        $periodo_lectivo_id = null;
        if ((int)$request->periodo_lectivo_id != 0) {
            $periodo_lectivo_id = (int)$request->periodo_lectivo_id;
        }

        
        $vista = $this->generar_reporte_cartera_por_curso($request->colegio_id, $request->curso_id, $request->tipo_reporte, $periodo_lectivo_id) . '
        <div style="font-size: 11px; text-align: right; width: 100%;">
            Generado:  ' . date('Y-m-d, h:m:s') . '
        </div>';

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);


        return $vista;
    }

    /**
     * ajax_reporte_cartera_por_curso
     *
     */
    public function teso_pdf_reporte_cartera_por_curso()
    {
        $tabla = $this->generar_reporte_cartera_por_curso(Input::get('colegio_id'), Input::get('curso_id'), Input::get('tipo_reporte'), Input::get('periodo_lectivo_id'));

        $vista = '<html>
                    <head>
                        <title>Reporte cartera por Curso</title>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                        <style>
                            @page { margin: 1cm; }
                            table {
                                width:100%;
                                border: 1px solid;
                                border-collapse: collapse;
                                font-size: 12px;
                            }
                            table td {
                                border: 1px solid;
                                border-collapse: collapse;
                            }
                        </style>    
                    </head>
                    <body>
                    <br/>
                    ' . $tabla . '
                    <div style="font-size: 11px; text-align: right; width: 100%;">
                        Generado:  ' . date('Y-m-d, h:m:s') . '
                    </div>
                    </body>
                </html>';

        $tam_hoja = 'folio';
        $orientacion = 'landscape';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($vista)->setPaper($tam_hoja, $orientacion);

        return $pdf->download('reporte_cartera_por_curso.pdf');

        //echo $vista;

    }

    /**
     * generar_reporte_cartera_por_curso
     *
     */
    public function generar_reporte_cartera_por_curso($colegio_id, $curso_id, $tipo_reporte, $periodo_lectivo_id)
    {
        $this->actualizar_estado_cartera();

        $concepto_matricula_id = config('matriculas.inv_producto_id_default_matricula');
        $concepto_pension_id = config('matriculas.inv_producto_id_default_pension');

        $errores_config = [];

        if ( empty($concepto_matricula_id) )
        {
            $errores_config[] = 'inv_producto_id_default_matricula';
        } else {
            if ( is_null( InvProducto::find($concepto_matricula_id) ) )
            {
                $errores_config[] = 'inv_producto_id_default_matricula (no existe producto ' . $concepto_matricula_id . ')';
            }
        }

        if ( empty($concepto_pension_id) )
        {
            $errores_config[] = 'inv_producto_id_default_pension';
        } else {
            if ( is_null( InvProducto::find($concepto_pension_id) ) )
            {
                $errores_config[] = 'inv_producto_id_default_pension (no existe producto ' . $concepto_pension_id . ')';
            }
        }

        if ( count($errores_config) > 0 )
        {
            return '<div class="alert alert-danger">' .
                    'Configuraci&oacute;n incompleta: <b>' . implode(', ', $errores_config) . '</b>.' .
                    ' Por favor config&uacute;relos en <b>Configuraciones &gt; Matr&iacute;culas</b>.' .
                '</div>';
        }

        $todas_las_matriculas_del_curso = Matricula::estudiantes_matriculados($curso_id, $periodo_lectivo_id, null);

        $format_money = function ($valor) {
            return '$' . number_format($valor, 0, ',', '.');
        };

        switch ($tipo_reporte) {
            case '0':
                $titulo = 'Resumen de recaudos de matr&iacute;culas y pensiones';
                $lbl_total = 'Pagado/Cartera';
                break;
            case '1':
                $titulo = 'Cartera Vencida Mes a Mes';
                $lbl_total = 'Total';
                break;
            default:
                # code...
                break;
        }

        $curso = Curso::find($curso_id);

        $filas = [];
        $fila = 1;
        $total_columna = [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $total_columna_valor_cartera = [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $total_columna_valor_pagado = [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $total_valor_cartera = 0;
        $total_valor_pagado = 0;

        $config_campo_visualizar_en_reporte_cartera = config('tesoreria.campo_visualizar_en_reporte_cartera');

        foreach ( $todas_las_matriculas_del_curso as $una_matricula ) 
        {
            $total_linea = 0;
            $total_cartera_linea = 0;
            $num_columna = 0;
            $fila_data = [
                'num' => $fila,
                'estudiante' => '',
                'matricula' => '',
                'meses' => [],
                'total_linea' => ''
            ];

            $campo_visualizar_en_reporte_cartera = ' ('.$una_matricula->codigo .')';
            if ($config_campo_visualizar_en_reporte_cartera == 'anio_lectivo') {
                $campo_visualizar_en_reporte_cartera = ' ('.$una_matricula->periodo_lectivo->descripcion .')';
            }

            // Se obtiene la libreta Activa de ese estudiante para el anio de la matricula activa
            $libreta_pagos = TesoLibretasPago::where('estado','Activo')
                                                ->where('matricula_id', $una_matricula->matricula_id )
                                                ->get()
                                                ->first();

            // Se obtiene la cartera de ese estudiante para el aÃƒÂ±o de la matrÃƒÂ­ula activa
            if ( !is_null($libreta_pagos) )
            {
                // PRIMERAS DOS COLUMNAS DE LA TABLA
                $fila_data['estudiante'] = $una_matricula->nombre_completo . $campo_visualizar_en_reporte_cartera;

                //Matri­cula

                $cartera_matricula = TesoPlanPagosEstudiante::where('id_libreta', $libreta_pagos->id)
                                            ->where('inv_producto_id', $concepto_matricula_id)
                                            ->get();

                if ( count($cartera_matricula) > 0 ) 
                {
                    $linea_cartera = $cartera_matricula[0];
                    $valor_pagado_linea = $linea_cartera->valor_cartera - $linea_cartera->saldo_pendiente;
                    if ( $valor_pagado_linea < 0 ) { $valor_pagado_linea = 0; }
                    $total_valor_cartera += $linea_cartera->valor_cartera;
                    $total_valor_pagado += $valor_pagado_linea;
                    $total_columna_valor_cartera[$num_columna] += $linea_cartera->valor_cartera;
                    $total_columna_valor_pagado[$num_columna] += $valor_pagado_linea;
                    $total_cartera_linea += $linea_cartera->valor_cartera;

                    switch ($tipo_reporte) {
                        case '0': // Resumen de recaudos
                        	$saldo_pendiente = $linea_cartera->saldo_pendiente;
                            $subtabla = $this->get_tabla_anidada($linea_cartera->id, $saldo_pendiente, $linea_cartera->valor_cartera, $linea_cartera->concepto);
                            $total_linea += $valor_pagado_linea;

                            break;

                        case '1': // Cartera Vencida
                            if ( $linea_cartera->estado == 'Vencida')
                            {
                                $saldo_pendiente = $linea_cartera->saldo_pendiente;
                                $total_linea+=$saldo_pendiente;
                            }else{
                                $saldo_pendiente = 0;
                            }

                            $subtabla = $format_money($saldo_pendiente);
                            break;

                        default:
                            # code...
                            break;
                    }

                    if ( $subtabla == '$0') {
                                   $subtabla = '';
                               }

                    $fila_data['matricula'] = $subtabla;
                    $total_columna[$num_columna] += $saldo_pendiente;
                    $num_columna++;
                }

                //Pension
                $cartera_pension = TesoPlanPagosEstudiante::where('id_libreta',$libreta_pagos->id)
                                                            ->where('inv_producto_id', $concepto_pension_id)
                                                            ->orderBy('fecha_vencimiento','ASC')
                                                            ->get();

                for ($i=2; $i < 12; $i++)
                {
                    $aplico_mes = false; // aplicó mes, es decir, si hay valor de pension en ese mes
                    $mes_columna = str_repeat(0, 2-strlen($i) ).$i;

                    foreach ( $cartera_pension as $linea_cartera )
                    {
                        $mes_libreta =explode("-", $linea_cartera->fecha_vencimiento)[1];

                        if ( $mes_columna == $mes_libreta ) 
                        {
                            $valor_pagado_linea = $linea_cartera->valor_cartera - $linea_cartera->saldo_pendiente;
                            if ( $valor_pagado_linea < 0 ) { $valor_pagado_linea = 0; }
                            $total_valor_cartera += $linea_cartera->valor_cartera;
                            $total_valor_pagado += $valor_pagado_linea;
                            $total_columna_valor_cartera[$num_columna] += $linea_cartera->valor_cartera;
                            $total_columna_valor_pagado[$num_columna] += $valor_pagado_linea;
                            $total_cartera_linea += $linea_cartera->valor_cartera;

                            switch ($tipo_reporte) {
                                case '0':
                                	$saldo_pendiente = $linea_cartera->saldo_pendiente;
                                    $subtabla = $this->get_tabla_anidada($linea_cartera->id, $saldo_pendiente, $linea_cartera->valor_cartera, $linea_cartera->concepto);
                                    $total_linea += $valor_pagado_linea;

                                    break;

                                case '1':
                                    if ( $linea_cartera->estado == 'Vencida')
                                    {
                                        $saldo_pendiente = $linea_cartera->saldo_pendiente;
                                        $total_linea+=$saldo_pendiente;
                                    }else{
                                        $saldo_pendiente = 0;
                                    }
                                    $subtabla = $format_money($saldo_pendiente);
                                    break;

                                default:
                                    # code...
                                    break;
                            }

                            if ( $subtabla == '$0') {
                                   $subtabla = '';
                               }

                            $fila_data['meses'][] = $subtabla;
                            $aplico_mes = true;

                            if ( !isset( $total_columna[$num_columna] ) )
                            {
                                dd( [ 'No existe datos para la columna: ' . $num_columna, $linea_cartera, $linea_cartera->estudiante->tercero->descripcion ] );
                            }
                            $total_columna[$num_columna] += $saldo_pendiente;
                            $num_columna++;
                        }
                    }

                    if ( !$aplico_mes)
                    {
                        $fila_data['meses'][] = '';

                        $total_columna[$num_columna] += 0;
                        $num_columna++;
                    }
                }


                if ( $total_linea == 0)
                {
                    $total_linea = '';
                }else{
                    if ($tipo_reporte == '0') {
                        $total_linea = $format_money($total_linea) . ' / ' . $format_money($total_cartera_linea);
                    } else {
                        $total_linea = $format_money($total_linea);
                    }
                }

                $fila_data['total_linea'] = $total_linea;
                $filas[] = $fila_data;
                $fila++;

            }else{
                // Para Descomentar abajo, se debe validar que la matricula no sea de un aÃƒÂ±o lectivo anterior
                //$tabla.='<tr><td colspan="14">El estudiante no tiene libreta de pagos Activa.</td></tr>';
            }

        } // Fin foreach $todas_las_matriculas_del_curso

        $gran_total = 0;
        for ($i=0; $i < 11; $i++)
        {
            $gran_total += $total_columna[$i];
        }

        return View::make(
            'tesoreria.partials.reporte_cartera_por_curso_tabla',
            [
                'titulo' => $titulo,
                'lbl_total' => $lbl_total,
                'curso' => $curso,
                'filas' => $filas,
                'total_columna' => $total_columna,
                'total_columna_valor_cartera' => $total_columna_valor_cartera,
                'total_columna_valor_pagado' => $total_columna_valor_pagado,
                'gran_total' => $gran_total,
                'total_valor_cartera' => $total_valor_cartera,
                'total_valor_pagado' => $total_valor_pagado
            ]
        )->render();
    }
    public function get_tabla_anidada($id_cartera, $saldo_pendiente, $valor_cartera, $concepto)
    {
        // CREACION TABLA anidada
        // El primer concepto de la cartera es matrÃƒÂ­cula

        // --------------------
        // |  $valor_cartera  |
        // --------------------
        // |   B    |    C    |
        // --------------------
        // |   fecha_recaudo  |
        // --------------------
        $key_cartera = 0;

        $recaudos_libreta = TesoRecaudosLibreta::where('id_cartera',$id_cartera)
                ->where('concepto',$concepto->id)
                ->groupBy('id_cartera')
                ->select(DB::raw('sum(valor_recaudo) AS valor_recaudo'),'teso_medio_recaudo_id','fecha_recaudo')
                ->get();

        $valor_pagado = 0;

        // Si no pagÃ³
        $fecha_recaudo = ' - ';
        $color_b = '#FFFFFF';
        $color_c = '#FFFFFF';
        $color_f = '#FFFFFF';

        // se hizo recaudo para el concepto de la cartera del estudiante
        if ( count($recaudos_libreta) > 0 ) 
        {
            $medio_recaudo = TesoMedioRecaudo::find($recaudos_libreta[0]->teso_medio_recaudo_id);

            $fecha_recaudo = $recaudos_libreta[0]->fecha_recaudo;
            $meses_abrev = [1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'];
            $timestamp_recaudo = strtotime($fecha_recaudo);
            if ( $timestamp_recaudo !== false ) {
                $dia = date('j', $timestamp_recaudo);
                $mes_num = (int) date('n', $timestamp_recaudo);
                $mes_abrev = isset($meses_abrev[$mes_num]) ? $meses_abrev[$mes_num] : '';
                $fecha_recaudo = $dia . ' de ' . $mes_abrev . '.';
            }
            $valor_pagado = $recaudos_libreta[0]->valor_recaudo;

            // Si pago completo o no
            $color_f = '#F7CE13';
            if ( $saldo_pendiente == 0) 
            {
                $color_f = '#33FFC1';
            }

            if($medio_recaudo== null){
                //$medio_recaudo = TesoMedioRecaudo::find(1);
            }

            // Si pago en banco o en efectivo
            if ( $medio_recaudo->comportamiento == 'Tarjeta bancaria') 
            {
                $color_b = '#33FFC1';
                $color_c = '#FFFFFF';
            }else{
                $color_b = '#FFFFFF';
                $color_c = '#33FFC1';
            }

        }

        $subtabla = '<table style="border: 1px solid; border-collapse: collapse;">
                        <tr>
                            <td colspan="2" align="center" title="Pagado / Cartera" >$' . number_format($valor_pagado, 0, ',', '.') . ' / $' . number_format($valor_cartera, 0, ',', '.') . '</td>
                        </tr>
                        <tr>
                            <td style="background-color:' . $color_b . ';" align="center" title="Banco">B</td>
                            <td style="background-color:' . $color_c . ';" align="center" title="Caja">C</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background-color:' . $color_f . ';" align="center" title="Fecha último abono">' . $fecha_recaudo . '</td>
                        </tr>
                    </table>';

        return $subtabla;
    }

    public function get_saldo_pendiente($id_cartera, $saldo_pendiente, $valor_cartera, $concepto)
    {
        return number_format( TesoRecaudosLibreta::where('id_cartera',$id_cartera)->where('concepto',$concepto)->sum('saldo_pendiente'), 0, ',', '.');
    }


    public function get_tabla_movimiento()
    {
        $movimiento = TesoMovimiento::movimiento_por_tipo_motivo( Input::get('movimiento'), Input::get('fecha_desde'), Input::get('fecha_hasta'), Input::get('teso_caja_id') )->toArray();

        $total_valor_movimiento = 0;
        foreach ($movimiento as $linea)
        {
            $total_valor_movimiento += $linea['valor_movimiento'];
        }
        return [View::make('tesoreria.incluir.tabla_movimiento_por_motivo', compact('movimiento'))->render(), $total_valor_movimiento,$movimiento];
    }

    /*
    Reporte saldo de cuentas de bancos
    */
    public static function reporte_cuentas( $fecha_corte )
    {
        $cuentas = self::get_cuentas_permitidas();

        $response = [
            'total' => 0,
            'data' => null
        ];
        $total = 0;
        if (count($cuentas) > 0) {
            foreach ($cuentas as $c) {
                $saldo = 0;
                $movs = TesoMovimiento::where('teso_cuenta_bancaria_id', $c->id)->where('fecha', '<=', $fecha_corte)->get();
                if (count($movs) > 0) {
                    foreach ($movs as $m) {
                        $saldo = $saldo + $m->valor_movimiento;
                    }
                }

                if (round($saldo,0) == 0 && $c->estado == 'Inactivo') {
                    continue;
                }

                $response['data'][] = [
                    'cuenta' => TesoEntidadFinanciera::find($c->entidad_financiera_id)->descripcion . " - " . $c->tipo_cuenta . " - Nro. " . $c->descripcion,
                    'saldo' => $saldo
                ];
                $total = $total + $saldo;
            }
            $response['total'] = $total;
        }
        return $response;
    }


    public static function get_cuentas_permitidas()
    {
        $cuentas = [];
        $user = Auth::user();
        if( $user->hasRole('Agencia') )
        {
            $acl = Acl::where([
                            ['modelo_recurso_id','=',33],
                            ['user_id','=',Auth::user()->id] ,
                            ['permiso_concedido','=',1] 
                        ] )
                    ->get()->first();

            if (!is_null($acl))
            {
                $cuentas = TesoCuentaBancaria::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'teso_cuentas_bancarias.contab_cuenta_id')
                                    ->where('teso_cuentas_bancarias.id',$acl->recurso_id)
                                    ->select(
                                        'teso_cuentas_bancarias.id',
                                        'teso_cuentas_bancarias.descripcion',
                                        'teso_cuentas_bancarias.tipo_cuenta',
                                        'teso_cuentas_bancarias.entidad_financiera_id',
                                        'teso_cuentas_bancarias.estado'
                                        )
                                    ->orderBy('teso_cuentas_bancarias.tipo_cuenta')
                                    ->get();
            }
            
        }else{
            $cuentas = TesoCuentaBancaria::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'teso_cuentas_bancarias.contab_cuenta_id')
                                    ->select(
                                        'teso_cuentas_bancarias.id',
                                        'teso_cuentas_bancarias.descripcion',
                                        'teso_cuentas_bancarias.tipo_cuenta',
                                        'teso_cuentas_bancarias.entidad_financiera_id',
                                        'teso_cuentas_bancarias.estado'
                                        )
                                    ->orderBy('teso_cuentas_bancarias.tipo_cuenta')
                                    ->get();
        }

        return $cuentas;
    }

    /*
    Reporte saldos de cajas
    */
    public static function reporte_cajas( $fecha_corte )
    {
        $cajas = self::get_cajas_permitidas();
        
        $response = [
            'total' => 0,
            'data' => null
        ];
        $total = 0;
        if (count($cajas) > 0) {
            foreach ($cajas as $c) {
                $saldo = 0;
                $movs = TesoMovimiento::where('teso_caja_id', $c->id)->where('fecha', '<=', $fecha_corte)->get();
                if (count($movs) > 0) {
                    foreach ($movs as $m) {
                        $saldo = $saldo + $m->valor_movimiento;
                    }
                }
                
                if (round($saldo,0) == 0 && $c->estado == 'Inactivo') {
                    continue;
                }

                $response['data'][] = [
                    'caja' => $c->descripcion,
                    'saldo' => $saldo
                ];
                $total = $total + $saldo;
            }
            $response['total'] = $total;
        }
        return $response;
    }

    public static function get_cajas_permitidas()
    {
        $cajas = [];
        $user = Auth::user();
        if( $user->hasRole('Agencia') )
        {
            $acl = Acl::where([
                            ['modelo_recurso_id','=',45],
                            ['user_id','=',Auth::user()->id] ,
                            ['permiso_concedido','=',1] 
                        ] )
                    ->get()->first();

            if (!is_null($acl))
            {
                $cajas = TesoCaja::where('id',$acl->recurso_id)->get();
            }
            
        }else{
            $cajas = TesoCaja::all();
        }

        return $cajas;
    }


    public function teso_movimiento_caja_bancos(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $teso_caja_id = (int)$request->teso_caja_id;
        $teso_cuenta_bancaria_id = (int)$request->teso_cuenta_bancaria_id;
        $teso_motivo_id = (int)$request->teso_motivo_id;
        $core_tercero_id = (int)$request->core_tercero_id;
        
        $array_wheres = $this->preparar_wheres( $teso_caja_id, $teso_cuenta_bancaria_id,$teso_motivo_id, $core_tercero_id, null );
        
        $caja = TesoCaja::find( $teso_caja_id );
        $cuenta_bancaria = TesoCuentaBancaria::find( $teso_cuenta_bancaria_id );

        $saldo_inicial = TesoMovimiento::get_saldo_inicial( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde );

        $movimiento = TesoMovimiento::get_movimiento2( $fecha_desde, $fecha_hasta, $array_wheres );
        
        $vista = View::make( 'tesoreria.reportes.movimiento_caja_bancos', compact( 'fecha_desde', 'fecha_hasta', 'saldo_inicial', 'movimiento','caja', 'cuenta_bancaria') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function preparar_wheres( $teso_caja_id, $teso_cuenta_bancaria_id,$teso_motivo_id, $core_tercero_id, $tipo_movimiento )
    {
        $array_wheres = [ ['teso_movimientos.id' ,'>', 0 ] ];
        
        if ( $tipo_movimiento != null ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_motivos.movimiento' => $tipo_movimiento ]);
        }
        
        if ( $teso_caja_id != 0 ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_caja_id' => (int) $teso_caja_id ]);
        }
        
        if ( $teso_cuenta_bancaria_id != 0 ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_cuenta_bancaria_id' => (int) $teso_cuenta_bancaria_id ]);
        }
        
        if ( $teso_motivo_id != 0 ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_motivo_id' => (int) $teso_motivo_id ]);
        }
        
        if ( $core_tercero_id != 0 ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.core_tercero_id' => (int) $core_tercero_id ]);
        }

        return $array_wheres;
    }

    public function teso_resumen_movimiento_caja_bancos(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $teso_caja_id = $request->teso_caja_id;
        $teso_cuenta_bancaria_id = $request->teso_cuenta_bancaria_id;

        if ( $request->teso_caja_id == '')
        {
            $teso_caja_id = 0;
        }

        if ( $request->teso_cuenta_bancaria_id == '')
        {
            $teso_cuenta_bancaria_id = 0;
        }

        $caja = TesoCaja::find( $teso_caja_id );
        $cuenta_bancaria = TesoCuentaBancaria::find( $teso_cuenta_bancaria_id );

        $saldo_inicial = TesoMovimiento::get_saldo_inicial( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde );

        $movimiento = TesoMovimiento::get_movimiento( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $fecha_hasta );

        $ids_cajas = array_keys( $movimiento->groupBy('teso_caja_id')->toArray() );
        $ids_cuentas_bancarias = array_keys( $movimiento->groupBy('teso_cuenta_bancaria_id')->toArray() );

        $movimiento_entradas = TesoMovimiento::get_movimiento( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $fecha_hasta, 'entrada' );

        $movimiento_salidas = TesoMovimiento::get_movimiento( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $fecha_hasta, 'salida' );
        
        $vista = View::make( 'tesoreria.reportes.resumen_movimientos_cajas_bancos', compact( 'fecha_desde', 'fecha_hasta', 'saldo_inicial', 'movimiento_entradas', 'movimiento_salidas', 'caja', 'cuenta_bancaria','ids_cajas','ids_cuentas_bancarias') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function teso_movimiento_caja_pdv( $fecha_desde, $fecha_hasta, $teso_caja_id )
    {
        $teso_cuenta_bancaria_id = 0;

        $caja = TesoCaja::find( $teso_caja_id );
        $mensaje = $caja->descripcion;

        $saldo_inicial = TesoMovimiento::get_saldo_inicial( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde );

        $movimiento = TesoMovimiento::get_movimiento( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $fecha_hasta );

        $vista = View::make('tesoreria.reportes.movimiento_caja_bancos', compact( 'fecha_desde', 'saldo_inicial', 'movimiento', 'mensaje'))->render();

        return $vista;
    }

    public function movimiento_con_fecha_distinta_a_su_creacion()
    {
        $fecha_desde = '2022-04-01' . ' 00:00:00';
        $fecha_hasta = '2022-04-25' . ' 23:59:00';

        $movimientos = TesoMovimiento::whereBetween( 'created_at', [$fecha_desde, $fecha_hasta] )->get();

        $arr_movin = [];
        foreach ($movimientos as $movimiento) {
            $created_at = explode(" ",$movimiento->created_at)[0];
            
            if($created_at != $movimiento->fecha)
            {
                $arr_movin[] = $movimiento;
            }
        }
        
        $vista = View::make( 'tesoreria.reportes.auditoria_movin_fecha_distinta_creacion', compact( 'fecha_desde', 'fecha_hasta', 'arr_movin') )->render();

        //Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }
}


