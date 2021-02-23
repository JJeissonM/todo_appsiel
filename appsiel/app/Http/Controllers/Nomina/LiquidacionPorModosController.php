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
use App\Nomina\ProgramacionVacacion;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\ModosLiquidacion\ModoLiquidacion; // Facade

class LiquidacionPorModosController extends TransaccionController
{
    protected $total_devengos_empleado = 0;
    protected $total_deducciones_empleado = 0;
    protected $vec_totales = [];
    protected $pos = 0;
    protected $registros_procesados = 0;
    protected $vec_campos;

    /* 
        7: Tiempo NO Laborado
        1: tiempo laborado
        6: Aux. transporte
        3: cuotas
        4: prestamos
        10: Fondo de solidaridad pensional
        12: Salud Obligatoria
        13: Pensión Obligatoria
    */
        
    // Nota: el orden de líquidación para 7,1 8, 10 7 11 es muy importante
    protected $array_ids_modos_liquidacion_automaticos; // = [ 7, 1, 6, 3, 4, 10, 12, 13];
    //protected $array_ids_modos_liquidacion_automaticos = [ 10 ];


    public function liquidar_prima_antiguedad( $nom_doc_encabezado_id )
    {
        $this->array_ids_modos_liquidacion_automaticos = [ 19 ];

        $this->liquidacion( $nom_doc_encabezado_id );

        return redirect( 'nomina/' . $nom_doc_encabezado_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion') )->with( 'flash_message', 'Primas de antigüedad liquidadas correctamente. Se procesaron ' . $this->registros_procesados . ' registros.' );
    }

    public function retirar_prima_antiguedad( $nom_doc_encabezado_id )
    {
        $this->array_ids_modos_liquidacion_automaticos = [ 19 ];

        $this->retirar_liquidacion( $nom_doc_encabezado_id );

        return redirect( 'nomina/'. $nom_doc_encabezado_id .'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( 'mensaje_error','Registros de Primas de antigüedad retirados correctamente.' );
    }

    /*
        Por cada empleado activo liquida los conceptos asignados al array de modos de liquidación.
    */
    public function liquidacion( $id )
    {
        $this->registros_procesados = 0;

        $usuario = Auth::user();

        $core_empresa_id = $usuario->empresa_id;

        $documento = NomDocEncabezado::find($id);

        // Se obtienen los Empleados del documento
        $empleados_documento = $documento->empleados;

        // Guardar los valores para cada empleado 
        foreach ( $empleados_documento as $empleado ) 
        {
            $cant = count( $this->array_ids_modos_liquidacion_automaticos );

            for ( $i=0; $i < $cant; $i++ ) 
            {                
                $this->liquidar_automaticos_empleado( $this->array_ids_modos_liquidacion_automaticos[$i], $empleado, $documento, $usuario);
            }
        }

        $this->actualizar_totales_documento($id);
    }

    /*
        Recibe doc. de nómina, al empleado y el modo de liquidación para calcular el valor de devengo o deducción de cada concepto
    */
    public function liquidar_automaticos_empleado( $modo_liquidacion_id, $empleado, $documento_nomina, $usuario )
    {
        $conceptos_automaticos = NomConcepto::where('estado','Activo')->where('modo_liquidacion_id', $modo_liquidacion_id)->get();

        foreach ( $conceptos_automaticos as $concepto )
        {
            $cant = 0;
            if ( $modo_liquidacion_id != 7 ) // Si no es TNL. Pueden haber varios registros de estos conceptos en el mismo Doc.
            {
                // Se valida si ya hay una liquidación previa del concepto en ese documento
                $cant = NomDocRegistro::where( 'nom_doc_encabezado_id', $documento_nomina->id)
                                        ->where('nom_contrato_id', $empleado->id)
                                        ->where('nom_concepto_id', $concepto->id)
                                        ->count();
            }
                

            if ( $cant != 0 ) 
            {
                continue;
            }

            // Se llama al subsistema de liquidación
            $liquidacion = new LiquidacionConcepto( $concepto->id, $empleado, $documento_nomina);

            $valores = $liquidacion->calcular( $concepto->modo_liquidacion_id );

            foreach( $valores as $registro )
            {
                $cantidad_horas = 0;
                if( isset($registro['cantidad_horas'] ) )
                {
                    $cantidad_horas = $registro['cantidad_horas'];
                }

                if( ( $registro['valor_devengo'] + $registro['valor_deduccion']  + $cantidad_horas ) != 0 )
                {
                    $registro['valor_devengo'] = round( $registro['valor_devengo'], 0);
                    $registro['valor_deduccion'] = round( $registro['valor_deduccion'], 0);
                    $this->almacenar_linea_registro_documento( $documento_nomina, $empleado, $concepto, $registro, $usuario);

                    $this->registros_procesados++;
                }
            }            
        } // Fin Por cada concepto
    }

    public function almacenar_linea_registro_documento($documento_nomina, $empleado, $concepto, $registro, $usuario)
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
                                    $registro
                                );
    }

    // Retiro de conceptos con modo liquidacion automatica
    public function retirar_liquidacion($id)
    {
        $documento_nomina = NomDocEncabezado::find( $id );
        $registros_documento = $documento_nomina->registros_liquidacion;

        foreach ( $registros_documento as $registro )
        {
            if ( !is_null( $registro->concepto ) && !is_null($registro->contrato) )
            {
                if ( in_array( $registro->concepto->modo_liquidacion_id, $this->array_ids_modos_liquidacion_automaticos) )
                {
                    // Se llama al subsistema de liquidación
                    $liquidacion = new LiquidacionConcepto( $registro->concepto->id, $registro->contrato, $documento_nomina);
                    $liquidacion->retirar( $registro->concepto->modo_liquidacion_id, $registro );
                }
            }   
        }

        $this->actualizar_totales_documento($id);
    }

    function actualizar_totales_documento($nom_doc_encabezado_id)
    {
        $documento = NomDocEncabezado::find($nom_doc_encabezado_id);
        $documento->total_devengos = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_devengo');
        $documento->total_deducciones = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_deduccion');
        $documento->save();
    }
    
}