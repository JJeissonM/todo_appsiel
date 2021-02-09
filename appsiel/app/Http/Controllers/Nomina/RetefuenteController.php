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

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\ModosLiquidacion\ModoLiquidacion; // Facade
use App\Nomina\ModosLiquidacion\Estrategias\Retefuente;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;

use App\Nomina\ParametrosRetefuenteEmpleado;

class RetefuenteController extends TransaccionController
{

    public function liquidacion( Request $request )
    {
        $modo_liquidacion_id = 11; // ReteFuente
        $usuario = Auth::user();
        
        $concepto = NomConcepto::where('modo_liquidacion_id',$modo_liquidacion_id)->get()->first();

        if ( is_null($concepto) )
        {
            return 'No existe un concepto creado con modo de liquidación de Retefuente';
        }
        
        $documento_nomina = NomDocEncabezado::find( (int)$request->nom_doc_encabezado_id );

        // Se obtienen los Empleados del documento de nómina
        $empleados_documento = $documento_nomina->empleados;

        $vista = '';
        foreach ($empleados_documento as $empleado) 
        {
            $parametros_retencion = ParametrosRetefuenteEmpleado::where('nom_contrato_id',$empleado->id)->orderBy('fecha_final_promedios')->get()->last();

            if ( is_null( $parametros_retencion ) )  // falta validar a qué empleados se aplicará
            {
                continue;
            }
             
            // Se llama al subsistema de liquidación
            $liquidacion = new LiquidacionConcepto( $concepto->id, $empleado, $documento_nomina, $request->fecha_final_promedios );

            $valores = $liquidacion->calcular( $modo_liquidacion_id );
            
            $cantidad_horas = 0;
            
            if( isset($valores[0]['cantidad_horas'] ) )
            {
                $cantidad_horas = $valores[0]['cantidad_horas'];
            }

            if( ( $valores[0]['valor_devengo'] + $valores[0]['valor_deduccion']  + $cantidad_horas ) != 0 )
            {
                $deduccion = $valores[0]['valor_deduccion'];

                if ( $request->almacenar_registros )
                {
                    $this->almacenar_linea_registro_documento( $documento_nomina, $empleado, $concepto, $deduccion, $usuario);
                }

                $tabla_resumen = $valores[0]['tabla_resumen'];

                $vista .= View::make( 'nomina.reportes.tabla_liquidacion_retefuente', compact('empleado', 'tabla_resumen') )->render();
            }
        }

        $this->actualizar_totales_documento( (int)$request->nom_doc_encabezado_id );

        return $vista;
    }



    public function almacenar_linea_registro_documento($documento_nomina, $empleado, $concepto, $deduccion, $usuario)
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
                                    ['modificado_por' => '']+ 
                                    ['valor_deduccion' => round( $deduccion, 0) ]
                                );
    }

    function actualizar_totales_documento($nom_doc_encabezado_id)
    {
        $documento = NomDocEncabezado::find($nom_doc_encabezado_id);
        $documento->total_devengos = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_devengo');
        $documento->total_deducciones = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_deduccion');
        $documento->save();
    }


    /**
     * Muestra un documento de liquidación con sus registros
     */
    public function show($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        $reg_anterior = NomDocEncabezado::where('id', '<', $id)->max('id');
        $reg_siguiente = NomDocEncabezado::where('id', '>', $id)->min('id');

        $view_pdf = $this->vista_preliminar($id,'show');
        
        $encabezado_doc = $this->encabezado_doc;

        $miga_pan = [
                  ['url'=>'nomina?id='.Input::get('id'),'etiqueta'=>'Nómina'],
                  ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                  ['url'=>'NO','etiqueta' => 'Consulta' ]
              ];

        // Para el modelo relacionado: Empleados
        $modelo_crud = new ModeloController;
        $respuesta = $modelo_crud->get_tabla_relacionada($modelo, $encabezado_doc);

        $tabla = $respuesta['tabla'];
        $opciones = $respuesta['opciones'];
        $registro_modelo_padre_id = $respuesta['registro_modelo_padre_id'];
        $titulo_tab = $respuesta['titulo_tab'];

        return view( 'nomina.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id','encabezado_doc','tabla','opciones','registro_modelo_padre_id','titulo_tab') ); 

    }


    // Retiro de conceptos con modo liquidacion automatica
    public function retirar_liquidacion($doc_encabezado_id)
    {
        $documento_nomina = NomDocEncabezado::find( $doc_encabezado_id );
        $registros_documento = $documento_nomina->registros_liquidacion;

        foreach ( $registros_documento as $registro )
        {
            if ( !is_null( $registro->concepto ) && !is_null($registro->contrato) )
            {
                if ( in_array( $registro->concepto->modo_liquidacion_id, [11] ) )
                {
                    // Se llama al subsistema de liquidación
                    $liquidacion = new LiquidacionConcepto( $registro->concepto->id, $registro->contrato, $documento_nomina);
                    $liquidacion->retirar( $registro->concepto->modo_liquidacion_id, $registro );
                }
            }   
        }

        $this->actualizar_totales_documento($doc_encabezado_id);

        return '<h3><b>Registros de Retefuente retirados correctamente.</b></h3>';
    }


    public function calcular_porcentaje_fijo_retefuente( Request $request )
    {
        
        $empleados_activos = NomContrato::where( 'estado', 'Activo' )->get();

        $empleados_con_retefuente = [];
        foreach ( $empleados_activos as $empleado ) 
        {
            $obj_retefuente = new ReteFuente();
            
            $obj_auxiliar = new ParametroLiquidacionPrestacionesSociales();
            $obj_auxiliar->cantidad_meses_a_promediar = $request->meses_a_promediar;
            $fecha_inicial = $obj_auxiliar->get_fecha_inicial_promedios( $request->fecha_final_promedios, $empleado );

            $valor_base_depurada = $obj_retefuente->get_valor_base_depurada( $fecha_inicial, $request->fecha_final_promedios, $empleado );

            // X    Renta de trabajo exenta del 25% numeral 10 del artículo 206 ET (W X 25%)
            $renta_trabajo_exenta = $valor_base_depurada * 25 / 100;

            // Y    SubTotal2
            $sub_total = $valor_base_depurada - $renta_trabajo_exenta;

            $numero_meses = round( $this->calcular_dias_laborados_calendario_30_dias( $fecha_inicial, $request->fecha_final_promedios ) / 30 , 0);

            $base_retencion_pesos = 0;
            $base_retencion_uvts = 0;
            if ( $numero_meses != 0 )
            {
                // AA Base en pesos para determinar porcentaje fijo de retención
                $base_retencion_pesos = $sub_total / $numero_meses;

                // AB Base en UVT (AA dividido valor_uvt_actual) (aplicar tabla artículo 383 ET)
                $base_retencion_uvts = $base_retencion_pesos / $request->valor_uvt_actual;
            }

            // AC  Rango de la tabla del artículo 383 ET
            $rango_tabla = $obj_retefuente->get_rango_tabla_uvts( $base_retencion_uvts );
            
            // AD  Últimas UVT del rango anterior
            $uvts_finales_rango_anterior = $rango_tabla->uvts_finales_rango_anterior;
            
            // AE  Tarifa marginal (%)
            $tarifa_marginal = $rango_tabla->tarifa_marginal;

            // AF  UVT marginales (los determina la tabla)
            $uvts_marginales = $rango_tabla->uvts_marginales;

            // AG  Retención en la fuente en UVT (((AB – AD) X AE) + AF)
            $valor_retencion_uvts = ( $base_retencion_uvts - $uvts_finales_rango_anterior ) * $tarifa_marginal + $uvts_marginales;
            
            // AH  Porcentaje fijo de retención ((AG dividido AB) X 100)
            $porcentaje_fijo = 0;
            if ( $base_retencion_uvts > 0 )
            {
                $porcentaje_fijo = $valor_retencion_uvts / $base_retencion_uvts * 100;
            }

            if ( $porcentaje_fijo > 0 )
            {
                $lbl_rango_tabla = 'Desde ' . $rango_tabla->uvts_iniciales . ' hasta ' . $rango_tabla->uvts_finales . ' UVT. Tarifa: ' . $rango_tabla->tarifa_marginal * 100 . '%. UVT marginales: ' . $rango_tabla->uvts_marginales;

                $datos = (object)[
                                    'empleado' => $empleado,
                                    'fecha_final_promedios' => $request->fecha_final_promedios,
                                    'valor_base_depurada' => $valor_base_depurada,
                                    'renta_trabajo_exenta' => $renta_trabajo_exenta,
                                    'sub_total' => $sub_total,
                                    'base_retencion_pesos' => $base_retencion_pesos,
                                    'base_retencion_uvts' => $base_retencion_uvts,
                                    'rango_tabla' => $lbl_rango_tabla,
                                    'valor_retencion_uvts' => $valor_retencion_uvts,
                                    'porcentaje_fijo' => $porcentaje_fijo
                                ];

                $empleados_con_retefuente[] = $datos;
                if( $request->almacenar_registros )
                {
                    $this->almacenar_parametros_retencion_empleado( $datos );
                }
            }
            

            
        }

        $vista = View::make( 'nomina.reportes.tabla_calculo_porcentaje_fijo_retefuente', compact( 'empleados_con_retefuente' ) )->render();

        return $vista;
    }

    public function almacenar_parametros_retencion_empleado( $datos )
    {
        ParametrosRetefuenteEmpleado::create(
                                                [ 'nom_contrato_id' => $datos->empleado->id ] + 
                                                [ 'fecha_final_promedios' => $datos->fecha_final_promedios ] + 
                                                [ 'valor_base_depurada' => $datos->valor_base_depurada ] +
                                                [ 'renta_trabajo_exenta' => $datos->renta_trabajo_exenta ] +
                                                [ 'sub_total' => $datos->sub_total ] +
                                                [ 'base_retencion_pesos' => $datos->base_retencion_pesos ] +
                                                [ 'base_retencion_uvts' => $datos->base_retencion_uvts ] +
                                                [ 'rango_tabla' => $datos->rango_tabla ] +
                                                [ 'valor_retencion_uvts' => $datos->valor_retencion_uvts ] +
                                                [ 'procedimiento' => 2 ] +  
                                                [ 'porcentaje_fijo' => round( $datos->porcentaje_fijo, 2) ] +
                                                [ 'deduccion_por_dependientes' => 1 ] +
                                                [ 'estado' => 'Activo']
                                            );
    }



    public function calcular_dias_laborados_calendario_30_dias( $fecha_inicial, $fecha_final )
    {
        $vec_fecha_inicial = explode("-", $fecha_inicial);
        $vec_fecha_final = explode("-", $fecha_final);

        // Días iniciales = (Año ingreso x 360) + ((Mes ingreso-1) x 30) + días ingreso
        $dias_iniciales = ( (int)$vec_fecha_inicial[0] * 360 ) + ( ( (int)$vec_fecha_inicial[1] - 1 ) * 30) + (int)$vec_fecha_inicial[2];

        // Días finales = (Año ingreso x 360) + ((Mes ingreso-1) x 30) + días ingreso
        $dias_finales = ( (int)$vec_fecha_final[0] * 360 ) + ( ( (int)$vec_fecha_final[1] - 1 ) * 30) + (int)$vec_fecha_final[2];

        $dias_totales_laborados = ($dias_finales - $dias_iniciales) + 1;

        return $dias_totales_laborados;
    }
    
}