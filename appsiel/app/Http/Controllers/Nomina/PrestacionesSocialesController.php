<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Sistema\Html\MigaPan;
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\PrestacionesLiquidadas;
use App\Nomina\DiaFestivo;

use App\Nomina\ModosLiquidacion\LiquidacionPrestacionSocial;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class PrestacionesSocialesController extends TransaccionController
{
    protected $total_devengos_empleado = 0;
    protected $total_deducciones_empleado = 0;
    protected $vec_totales = [];
    protected $pos = 0;
    protected $registros_procesados = 0;
    protected $vec_campos;

    /*
        Por cada empleado activo liquida los conceptos automáticos, las cuotas y préstamos
        Además actualiza el total de devengos y deducciones en el documento de nómina
    */
    public function liquidacion( Request $request )
    {
        $vista = '';
        $usuario = Auth::user();

        $documento_nomina = NomDocEncabezado::find( (int)$request->nom_doc_encabezado_id );

        // Se obtienen los Empleados del documento de nómina
        $empleados_documento = $documento_nomina->empleados;
        
        $array_prestaciones_liquidadas = (object)[];

        foreach ($empleados_documento as $empleado)
        {
            /**
             * 51 = Trabajador de Tiempo Parcial
             */
            if ( in_array($empleado->tipo_cotizante, [51] )) {
                continue;
            }

            $array_prestaciones_liquidadas->nom_doc_encabezado_id = $documento_nomina->id;
            $array_prestaciones_liquidadas->nom_contrato_id = $empleado->id;
            $array_prestaciones_liquidadas->fecha_final_promedios = $request->fecha_final_promedios;
            $array_prestaciones_liquidadas->fecha_final_liquidacion = $request->fecha_final_liquidacion;
            $array_prestaciones_liquidadas->prestaciones = [];

            $p = 0;
            foreach ($request->prestaciones as $key => $prestacion )
            {
                $array_aux_prestacion = (object)[];
                
                // Se llama al subsistema de liquidación
                $liquidacion = new LiquidacionPrestacionSocial( $prestacion, $empleado, $documento_nomina, $request->almacenar_registros, $request->fecha_final_promedios, $request->fecha_final_liquidacion);

                $valores = $liquidacion->calcular( $prestacion );

                $cantidad_horas = 0;
                
                if( isset($valores[0]['cantidad_horas'] ) )
                {
                    $cantidad_horas = $valores[0]['cantidad_horas'];
                }

                $tabla_resumen = $valores[0]['tabla_resumen'];

                if( ( $valores[0]['valor_devengo'] + $valores[0]['valor_deduccion']  + $cantidad_horas ) != 0 )
                {
                    $valores[0]['valor_devengo'] = round( $valores[0]['valor_devengo'], 0);
                    $valores[0]['valor_deduccion'] = round( $valores[0]['valor_deduccion'], 0);

                    if ( $request->almacenar_registros )
                    {
                        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion',$prestacion)
                                                                            ->where('grupo_empleado_id',$empleado->grupo_empleado_id)
                                                                            ->get()->first();

                        $concepto = NomConcepto::find( $parametros_prestacion->nom_concepto_id );
                        if ( ( $prestacion == 'cesantias') && ($request->concepto_cesantias_id != '') )
                        {
                            $concepto = NomConcepto::find( $request->concepto_cesantias_id );
                        }

                        if ( is_null($concepto) )
                        {
                            return $prestacion . '. La prestación no tiene un Concepto asociado. Favor revisar su parametrización';
                        }
                        
                        $this->almacenar_linea_registro_documento( $documento_nomina, $empleado, $concepto, $valores[0], $usuario, $prestacion);
                        $array_aux_prestacion->prestacion = $prestacion;
                        $array_aux_prestacion->tabla_resumen = $tabla_resumen;
                        $array_prestaciones_liquidadas->prestaciones[$p] = $array_aux_prestacion;
                    }

                    $vista .= View::make( 'nomina.prestaciones_sociales.liquidacion_' . $prestacion, compact( 'empleado', 'tabla_resumen') )->render();

                }else{
                    $vista .= $tabla_resumen['mensaje_error'];
                }
                $p++;
            }
            
            $this->almacenar_prestaciones_liquidadas( $array_prestaciones_liquidadas );
        }

        $this->registros_procesados = 0;

        $core_empresa_id = $usuario->empresa_id;

        $this->actualizar_totales_documento( (int)$request->nom_doc_encabezado_id );

        return $vista;
    }

    public function almacenar_prestaciones_liquidadas( $array_prestaciones_liquidadas )
    {
        if ( empty($array_prestaciones_liquidadas->prestaciones) )
        {
            return 0;
        }

        PrestacionesLiquidadas::create(
                                        ['nom_doc_encabezado_id' => $array_prestaciones_liquidadas->nom_doc_encabezado_id ] + 
                                        ['nom_contrato_id' => $array_prestaciones_liquidadas->nom_contrato_id ] + 
                                        ['fecha_final_promedios' => $array_prestaciones_liquidadas->fecha_final_promedios] +  
                                        ['prestaciones_liquidadas' => json_encode( $array_prestaciones_liquidadas->prestaciones ) ]
                                    );
    }

    public function almacenar_linea_registro_documento($documento_nomina, $empleado, $concepto, $valores, $usuario, $prestacion)
    {
        NomDocRegistro::create(
                                ['nom_doc_encabezado_id' => $documento_nomina->id ] + 
                                ['fecha' => $documento_nomina->fecha] + 
                                ['core_empresa_id' => $documento_nomina->core_empresa_id] +  
                                ['nom_concepto_id' => $concepto->id ] + 
                                ['core_tercero_id' => $empleado->core_tercero_id ] + 
                                ['nom_contrato_id' => $empleado->id ] + 
                                ['estado' => 'Activo'] + 
                                ['creado_por' => $usuario->email] + 
                                ['modificado_por' => ''] +
                                $valores
                            );
    }

    // Retiro de conceptos con modo liquidacion automatica
    public function retirar_liquidacion($doc_encabezado_id,$prestaciones)
    {
        $documento_nomina = NomDocEncabezado::find( $doc_encabezado_id );
        $registros_documento = $documento_nomina->registros_liquidacion;

        $vec_prestaciones = explode("-", $prestaciones);
        array_shift( $vec_prestaciones );

        foreach ( $registros_documento as $registro )
        {
            foreach ( $vec_prestaciones as $key => $prestacion)
            {
                if ( !is_null( $registro->concepto ) && !is_null($registro->contrato) )
                {
                    // Se llama al subsistema de liquidación
                    $liquidacion = new LiquidacionPrestacionSocial( $prestacion, $registro->contrato, $documento_nomina, 0, 0, 0);
                    $liquidacion->retirar( $prestacion, $registro );
                }
            }                   
        }

        $this->actualizar_totales_documento($doc_encabezado_id);

        return '<h3><b>Registros retirados correctamente.</b></h3>';
    }

    function actualizar_totales_documento($nom_doc_encabezado_id)
    {
        $documento = NomDocEncabezado::find($nom_doc_encabezado_id);
        $documento->total_devengos = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_devengo');
        $documento->total_deducciones = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_deduccion');
        $documento->save();
    }

    public function get_fecha_final_vacaciones( $grupo_empleado_id, $fecha_inicial, $cantidad_dias_tomados, $dias_compensados )
    {
        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion', 'vacaciones')
                                                                        ->where('grupo_empleado_id',$grupo_empleado_id)
                                                                        ->get()->first();
        
        $dias_vacaciones = (int)$cantidad_dias_tomados - (int)$dias_compensados;
        
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_aux = Carbon::createFromFormat('Y-m-d', $fecha_inicial);

        $dias_no_habiles = 0;
        $dias_contados = 0;
        $vector = [];
        $fecha_actual = $fecha_aux;
        while ( $dias_contados < $dias_vacaciones )
        {
            $aumentar_dias_contados = true;

            $el_dia = $fecha_actual;
            $descripcion_el_dia = $el_dia->format('l');
            $fecha_el_dia = $el_dia->format('Y-m-d');

            if ( $descripcion_el_dia == 'Sunday' )
            {
                $dias_no_habiles++;
                $aumentar_dias_contados = false;
            }

            if ( $this->es_dia_festivo( $fecha_el_dia ) )
            {
                $dias_no_habiles++;
                $aumentar_dias_contados = false;
            }

            if ( !$parametros_prestacion->sabado_es_dia_habil )
            {
                if ( $descripcion_el_dia == 'Saturday' )
                {
                    $dias_no_habiles++;
                    $aumentar_dias_contados = false;
                }
            }

            if ( $aumentar_dias_contados )
            {
                $dias_contados++;
            }

            $fecha_actual = $fecha_aux->addDays( 1 );

            $vector[] = (object)[
                                    'fecha_aux' => $fecha_aux,
                                    'aumentar_dias_contados'=>$aumentar_dias_contados,
                                    'el_dia' => $el_dia,
                                    'descripcion_el_dia' => $descripcion_el_dia,
                                    'fecha_el_dia' => $fecha_el_dia,
                                    'es_dia_festivo' => $this->es_dia_festivo( $fecha_el_dia ),
                                    'dias_no_habiles' => $dias_no_habiles,
                                    'sabado_es_dia_habil' => $parametros_prestacion->sabado_es_dia_habil,
                                    'dias_contados' => $dias_contados
                                ];
        }

        $fecha_fin = $fecha_ini->addDays( $dias_vacaciones + $dias_no_habiles - 1 ); // Se resta porque el mismo día se tiene encuenta
        return response()->json( [ 
                        'fecha_fin' => $fecha_fin->format('Y-m-d'),
                        'dias_no_habiles' => $dias_no_habiles
                        ] );
    }

    public function es_dia_festivo( $fecha )
    {        
        if ( is_null( DiaFestivo::where('fecha',$fecha)->get()->first() ) )
        {
            return false;
        }

        return true;
    }

    public function prestaciones_liquidadas_show( $id )
    {
        $registro = PrestacionesLiquidadas::find( $id );
        $documento_nomina = NomDocEncabezado::find( $registro->nom_doc_encabezado_id );
        $empleado = NomContrato::find($registro->nom_contrato_id);
        $prestaciones_liquidadas = json_decode( $registro->prestaciones_liquidadas );

        $vista = $this->generar_vista_prestaciones_liquidadas_show( $empleado, $prestaciones_liquidadas );
        
        $modelo = Modelo::find(Input::get('id_modelo'));
        $aplicacion = Aplicacion::find(Input::get('id'));

        $miga_pan = MigaPan::get_array($aplicacion, $modelo, $documento_nomina->descripcion);

        return view( 'nomina.prestaciones_sociales.show_prestaciones_liquidadas', compact('miga_pan','vista','documento_nomina','id') );
    }

    public function generar_vista_prestaciones_liquidadas_show( $empleado, $prestaciones_liquidadas, $tipo_vista = 'show' )
    {
        $vista = '';
        foreach ($prestaciones_liquidadas as $linea)
        {
            $prestacion = $linea->prestacion;
            $tabla_resumen = (array)$linea->tabla_resumen;
            
            $vista .= View::make( 'nomina.prestaciones_sociales.liquidacion_' . $prestacion, compact( 'empleado', 'tabla_resumen' ) )->render();

            if ( $tipo_vista = 'imprimir' )
            {
                $vista .= '<div class="page-break"></div>';
            }
        }

        return $vista;
    }

    public function pdf_prestaciones_liquidadas( $id )
    {
        $registro = PrestacionesLiquidadas::find( $id );
        $documento_nomina = NomDocEncabezado::find( $registro->nom_doc_encabezado_id );
        $empleado = NomContrato::find($registro->nom_contrato_id);
        $prestaciones_liquidadas = json_decode( $registro->prestaciones_liquidadas );

        $encabezado = View::make( 'nomina.incluir.encabezado_transaccion', ['encabezado_doc' => $documento_nomina, 'empresa' => $documento_nomina->empresa , 'descripcion_transaccion' => $documento_nomina->tipo_documento_app->descripcion ] )->render();

        $view = $encabezado . '<h3 style="width:100%; text-aling:center;">Comprobante de liquidación de prestaciones sociales</h3>' . $this->generar_vista_prestaciones_liquidadas_show( $empleado, $prestaciones_liquidadas, 'imprimir' );

        $font_size = '14';
        $vista = View::make( 'layouts.pdf3', compact('view','font_size') );

        $tam_hoja = 'letter';//array(0, 0, 612.00, 390.00);//'folio';
        $orientacion='portrait';//landscape
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($vista)->setPaper($tam_hoja,$orientacion);

        return $pdf->stream('pdf_listado_vacaciones_pendientes.pdf');
    }
    
}