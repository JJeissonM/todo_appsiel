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
            if ( $empleado->id == 43 )  // falta validar a qué empleados se aplicará
            {
             
                // Se llama al subsistema de liquidación
                $liquidacion = new LiquidacionConcepto( $concepto->id, $empleado, $documento_nomina);

                $valores = $liquidacion->calcular( $modo_liquidacion_id );
                
                $cantidad_horas = 0;
                
                if( isset($valores[0]['cantidad_horas'] ) )
                {
                    $cantidad_horas = $valores[0]['cantidad_horas'];
                }

                if( ( $valores[0]['valor_devengo'] + $valores[0]['valor_deduccion']  + $cantidad_horas ) != 0 )
                {
                    $deduccion = $valores[0]['valor_deduccion'];
                    $this->almacenar_linea_registro_documento( $documento_nomina, $empleado, $concepto, $deduccion, $usuario);

                    $tabla_resumen = $valores[0]['tabla_resumen'];

                    $vista .= View::make( 'nomina.reportes.tabla_liquidacion_retefuente', compact('empleado', 'tabla_resumen') )->render();
                }
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
                                    ['valor_deduccion' => $deduccion]
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
    
}