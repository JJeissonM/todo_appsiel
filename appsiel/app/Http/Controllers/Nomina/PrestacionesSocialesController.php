<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;
use NumerosEnLetras;
use Carbon\Carbon;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Empresa;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\NomCuota;
use App\Nomina\NomPrestamo;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\DiaFestivo;

use App\Nomina\ModosLiquidacion\LiquidacionPrestacionSocial;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;

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

        foreach ($empleados_documento as $empleado)
        {
            //$vista .= View::make( 'nomina.incluir.tabla_datos_empleado', compact( 'empleado' ) )->render();

            foreach ($request->prestaciones as $key => $prestacion)
            {
                // Se llama al subsistema de liquidación
                $liquidacion = new LiquidacionPrestacionSocial( $prestacion, $empleado, $documento_nomina, $request->almacenar_registros, $request->fecha_final_promedios);

                $valores = $liquidacion->calcular( $prestacion );

                $cantidad_horas = 0;
                
                if( isset($valores[0]['cantidad_horas'] ) )
                {
                    $cantidad_horas = $valores[0]['cantidad_horas'];
                }

                $tabla_resumen = $valores[0]['tabla_resumen'];

                if( ( $valores[0]['valor_devengo'] + $valores[0]['valor_deduccion']  + $cantidad_horas ) != 0 )
                {
                    $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion',$prestacion)
                                                                        ->where('grupo_empleado_id',$empleado->grupo_empleado_id)
                                                                        ->get()->first();

                    $concepto = NomConcepto::find( $parametros_prestacion->nom_concepto_id );

                    if ( $request->almacenar_registros )
                    {
                        $this->almacenar_linea_registro_documento( $documento_nomina, $empleado, $concepto, $valores[0], $usuario);
                    }

                    $vista .= View::make( 'nomina.prestaciones_sociales.liquidacion_' . $prestacion, compact( 'empleado', 'tabla_resumen') )->render();
                }else{
                    $vista .= $tabla_resumen['mensaje_error'];
                }
            }
        }

        $this->registros_procesados = 0;

        $core_empresa_id = $usuario->empresa_id;

        $this->actualizar_totales_documento( (int)$request->nom_doc_encabezado_id );

        return $vista;
    }

    public function almacenar_linea_registro_documento($documento_nomina, $empleado, $concepto, $valores, $usuario)
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
                    $liquidacion = new LiquidacionPrestacionSocial( $prestacion, $registro->contrato, $documento_nomina, null);
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

        //dd($vector);

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
    
}