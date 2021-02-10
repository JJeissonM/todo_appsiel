<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use View;
use Auth;

use App\Nomina\TransaccionesViaInterfaz\ArchivoPlano;

use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomConcepto;
use App\Nomina\NomContrato;
use App\Nomina\PrestacionesLiquidadas;

class ProcesosController extends Controller
{
    //

    public function procesar_archivo_plano( Request $request )
    {
    	$nom_doc_encabezado_id = $request->nom_doc_encabezado_id;
    	$encabezado_documento = NomDocEncabezado::find( $nom_doc_encabezado_id );

    	$archivo = new ArchivoPlano( $encabezado_documento, file( $request->archivo_plano ) );

    	$lineas_archivo_plano = $archivo->validar_estructura_archivo();

    	return View::make( 'nomina.procesos.transacciones_via_interface.lineas_registros_guardar_archivo_plano', compact( 'lineas_archivo_plano', 'nom_doc_encabezado_id' ) )->render();
    }

    public function almacenar_registros_via_interface( Request $request )
    {
        $nom_doc_encabezado_id = $request->documento_encabezado_id;
        $encabezado_documento = NomDocEncabezado::find( $nom_doc_encabezado_id );

        $lineas_registros = json_decode( $request->lineas_registros );
        $cantidad_registros = 0;
        foreach ($lineas_registros as $linea )
        { 
            if ( !$linea->con_errores)
            {
                $registro = new NomDocRegistro;

                $concepto = NomConcepto::find( (int)$linea->nom_concepto_id );
                $contrato = NomContrato::find( (int)$linea->nom_contrato_id );
                if ( is_null($contrato) ) {
                    dd($linea);
                }
                
                // Cuando se indica Cantidad de horas, se descarta el valor ingresado
                $valor_a_liquidar = (float)$linea->valor;
                if ( (float)$linea->cantidad_horas != 0 )
                {
                    $valor_a_liquidar = $concepto->get_valor_hora_porcentaje_sobre_basico( $contrato->salario_x_hora(), (float)$linea->cantidad_horas );
                }

                $valores = get_valores_devengo_deduccion( $concepto->naturaleza, $valor_a_liquidar );

                $registro->fill( 
                                    [ 
                                        'nom_doc_encabezado_id' => $encabezado_documento->id,
                                        'core_tercero_id' => (int)$linea->core_tercero_id,
                                        'nom_contrato_id' => (int)$linea->nom_contrato_id,
                                        'fecha' => $encabezado_documento->fecha,
                                        'core_empresa_id' => $encabezado_documento->core_empresa_id,
                                        'nom_concepto_id' => (int)$linea->nom_concepto_id,
                                        'cantidad_horas' => (float)$linea->cantidad_horas,
                                        'valor_devengo' => $valores->devengo,
                                        'valor_deduccion'  => $valores->deduccion,
                                        'estado' => 'Activo',
                                        'creado_por' => Auth::user()->email
                                    ]
                                );

                $registro->save();

                $cantidad_registros++;
            }
        }

        return redirect( 'index_procesos/nomina.procesos.transacciones_via_interface?id=17' )->with('flash_message','Se almacenaron <b>' . $cantidad_registros . ' registros </b> correctamente en el documento <b>' . $encabezado_documento->descripcion . '</b>.' );
    }

    public function generar_archivo_consignar_cesantias( Request $request )
    {
        $nom_doc_encabezado_id = $request->nom_doc_encabezado_id;
        $formato_entidad = $request->formato_entidad;
        $fondo_cesantias_destino = $request->fondo_cesantias_destino;

        if ( $formato_entidad != 'arus' )
        {
            return 'No hay un FORMATO configurado para el <u>Formato Entidad</u> seleccionada';
        }
        
        $encabezado_documento = NomDocEncabezado::find( $nom_doc_encabezado_id );

        $lineas_registros = $encabezado_documento->registros_liquidacion;
        $cantidad_registros = 0;
        $lineas_consignacion = [];
        foreach ($lineas_registros as $linea )
        {
            if ( $linea->concepto->modo_liquidacion_id == 15 ) // 15: Consignacion de cesantías
            {
                $datos_linea = (object)[ 'tipo_documeto', 'numero_documento', 'apellido1', 'apellido2', 'nombre1', 'otros_nombres', 'codigo_fondo_cesantias_destino', 'numero_dias_trabajados', 'salario_basico', 'valor_cesantias' ];

                $tercero = $linea->contrato->tercero;
                $datos_linea->tipo_documeto = $this->get_tipo_identificacion( $tercero->id_tipo_documento_id );
                $datos_linea->numero_documento = $tercero->numero_identificacion;
                $datos_linea->apellido1 = $this->formatear_texto( $tercero->apellido1 );
                $datos_linea->apellido2 = $this->formatear_texto( $tercero->apellido2 );
                $datos_linea->nombre1 = $this->formatear_texto( $tercero->nombre1 );
                $datos_linea->otros_nombres = $this->formatear_texto( $tercero->otros_nombres );

                $datos_linea->codigo_fondo_cesantias_destino = $fondo_cesantias_destino;

                //'', '', 'fecha_final_promedios', 'prestaciones_liquidadas', 'datos_liquidacion'

                $datos_liquidacion = PrestacionesLiquidadas::where( [
                                                                        [ 'nom_doc_encabezado_id', '=', (int)$nom_doc_encabezado_id ],
                                                                        [ 'nom_contrato_id', '=', $linea->nom_contrato_id ]
                                                                    ] )
                                                            ->get()
                                                            ->first();
                
                $numero_dias_trabajados = 0;
                if ( !is_null( $datos_liquidacion ) )
                {
                    if ( isset( json_decode( $datos_liquidacion->prestaciones_liquidadas )[0] ) )
                    {
                        $numero_dias_trabajados = json_decode( $datos_liquidacion->prestaciones_liquidadas )[0]->tabla_resumen->dias_totales_laborados;
                    }
                }

                $datos_linea->numero_dias_trabajados = $numero_dias_trabajados;

                $salario_basico = $linea->contrato->sueldo;
                if ( !is_null( $linea->contrato->salario_anterior() ) )
                {
                    $salario_basico = $linea->contrato->salario_anterior();
                }                
                $datos_linea->salario_basico = $salario_basico;

                $datos_linea->valor_cesantias = $linea->valor_devengo;                
                
                $lineas_consignacion[] = $datos_linea;

                $cantidad_registros++;
            }
        }

        return View::make( 'nomina.procesos.tabla_consignacion_cesantias', compact( 'lineas_consignacion', 'cantidad_registros', 'encabezado_documento' ) )->render();
    }

    public function formatear_texto( $texto )
    {
        $cadena = str_slug( $texto );
        $primera_letra = substr( $cadena, 0, 1 );
        $primera_letra_mayuscula = strtoupper( $primera_letra );

        $texto_sin_la_primera_letra = substr($cadena, 1);

        $texto_final = $primera_letra_mayuscula . $texto_sin_la_primera_letra;
        return str_replace( '-', ' ', $texto_final );
    }


    public function get_tipo_identificacion( $id_tipo_documento_id )
    {
        switch ( $id_tipo_documento_id )
        {
            case '11': // Registro civil de nacimiento
                return 'RC';
                break;
            case '12': // Tarjeta de identidad
                return 'TI';
                break;
            case '13': // Cédula de ciudadanía
                return 'CC';
                break;
            case '22': // Cédula de extranjería
                return 'CE';
                break;
            case '41': // Pasaporte
                return 'PA';
                break;
            case '42': // Documento de identificación extranjero (Carnet Diplomático)
                return 'CD';
                break;
            
            default:
                return 'CC';
                break;
        }
    }
}
