<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use View;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Nomina\TransaccionesViaInterfaz\LibroExcel;

use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomConcepto;
use App\Nomina\NomContrato;
use App\Nomina\PrestacionesLiquidadas;

class ProcesosController extends Controller
{

    public function procesar_archivo_plano( Request $request )
    {
        // Compatibilidad con integraciones que todavía apuntan a la URL anterior.
        return $this->procesar_libro_excel($request);
    }

    public function procesar_libro_excel(Request $request)
    {
        $nom_doc_encabezado_id = (int) $request->nom_doc_encabezado_id;
        $encabezado_documento = NomDocEncabezado::find($nom_doc_encabezado_id);

        if (is_null($encabezado_documento)) {
            return $this->respuestaErrorCarga('Debe seleccionar un documento de liquidación válido.');
        }

        if (!$encabezado_documento->esta_activo_para_transacciones()) {
            return $this->respuestaErrorCarga('El documento de nómina no puede modificarse porque no está en estado Activo.', 'warning');
        }

        $archivo = $request->file('libro_excel');
        if (is_null($archivo)) {
            $archivo = $request->file('archivo_plano');
        }

        if (is_null($archivo) || !$archivo->isValid()) {
            return $this->respuestaErrorCarga('Debe seleccionar un libro de Excel válido.');
        }

        $extension = strtolower($archivo->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls'], true)) {
            return $this->respuestaErrorCarga('El archivo debe tener extensión .xlsx o .xls.');
        }

        if ($archivo->getSize() > (5 * 1024 * 1024)) {
            return $this->respuestaErrorCarga('El libro de Excel no puede superar 5 MB.');
        }

        try {
            $libro = new LibroExcel($encabezado_documento, $archivo->getRealPath());
            $lineas_libro_excel = $libro->validar();
        } catch (\InvalidArgumentException $e) {
            return $this->respuestaErrorCarga($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error al procesar libro de transacciones de nómina.', ['exception' => $e]);
            return $this->respuestaErrorCarga('Ocurrió un error al procesar el libro de Excel. Verifique su estructura e intente nuevamente.');
        }

        return View::make(
            'nomina.procesos.transacciones_via_interface.lineas_registros_guardar_libro_excel',
            compact('lineas_libro_excel', 'nom_doc_encabezado_id')
        )->render();
    }

    protected function respuestaErrorCarga($mensaje, $tipo = 'danger')
    {
        return '<div class="alert alert-' . $tipo . '">' . e($mensaje) . '</div>';
    }

    public function almacenar_registros_via_interface( Request $request )
    {
        $nom_doc_encabezado_id = (int) $request->documento_encabezado_id;
        $encabezado_documento = NomDocEncabezado::find($nom_doc_encabezado_id);

        if (is_null($encabezado_documento) || !$encabezado_documento->esta_activo_para_transacciones()) {
            return redirect( 'index_procesos/nomina.procesos.transacciones_via_interface?id=17' )->with('mensaje_error','El documento de nómina no puede modificarse porque no está en estado Activo.');
        }

        $lineas_registros = json_decode($request->lineas_registros);
        if (!is_array($lineas_registros)) {
            return redirect('index_procesos/nomina.procesos.transacciones_via_interface?id=17')
                ->with('mensaje_error', 'No se recibieron registros válidos del libro de Excel.');
        }

        $registrosValidados = [];
        $errores = [];
        $combinaciones = [];
        $lapso = $encabezado_documento->lapso();

        foreach ($lineas_registros as $indice => $linea) {
            if (!empty($linea->con_errores)) {
                continue;
            }

            $numeroRegistro = isset($linea->numero_fila_excel) ? (int) $linea->numero_fila_excel : $indice + 1;
            $contrato = NomContrato::with('tercero')->find((int) $linea->nom_contrato_id);
            $concepto = NomConcepto::find((int) $linea->nom_concepto_id);
            $cantidadHorasValida = isset($linea->cantidad_horas) && is_numeric($linea->cantidad_horas);
            $valorValido = isset($linea->valor) && is_numeric($linea->valor);
            $cantidadHoras = $cantidadHorasValida ? (float) $linea->cantidad_horas : 0;
            $valor = $valorValido ? (float) $linea->valor : 0;
            $erroresLinea = [];

            if (is_null($contrato) || is_null($contrato->tercero)) {
                $erroresLinea[] = 'el contrato no existe';
            } else {
                if ((int) $contrato->core_tercero_id !== (int) $linea->core_tercero_id) {
                    $erroresLinea[] = 'el empleado no corresponde al contrato';
                }
                if ((int) $contrato->tercero->core_empresa_id !== (int) $encabezado_documento->core_empresa_id) {
                    $erroresLinea[] = 'el contrato no pertenece a la empresa del documento';
                }
                if ($contrato->estado !== 'Activo' || $contrato->fecha_ingreso > $lapso->fecha_final ||
                    (!empty($contrato->contrato_hasta) && $contrato->contrato_hasta !== '0000-00-00' && $contrato->contrato_hasta < $lapso->fecha_inicial)) {
                    $erroresLinea[] = 'el contrato no está activo y vigente para el período';
                }
            }

            if (is_null($concepto) || $concepto->estado !== 'Activo' || (int) $concepto->modo_liquidacion_id !== 2) {
                $erroresLinea[] = 'el concepto no existe, no está activo o no es Manual';
            }

            if (!$cantidadHorasValida || !$valorValido || $cantidadHoras < 0 || $valor < 0 || ($cantidadHoras + $valor) <= 0) {
                $erroresLinea[] = 'la cantidad de horas o el valor no son válidos';
            }

            if (!is_null($contrato) && !is_null($concepto)) {
                $llave = $contrato->id . '|' . $concepto->id;
                if (isset($combinaciones[$llave])) {
                    $erroresLinea[] = 'la combinación empleado/concepto está repetida';
                }
                $combinaciones[$llave] = true;

                if (NomDocRegistro::where([
                    'nom_doc_encabezado_id' => $encabezado_documento->id,
                    'nom_contrato_id' => $contrato->id,
                    'nom_concepto_id' => $concepto->id
                ])->exists()) {
                    $erroresLinea[] = 'el concepto ya fue liquidado para el empleado';
                }
            }

            if (!empty($erroresLinea)) {
                $errores[] = 'Fila de Excel ' . $numeroRegistro . ': ' . implode(', ', $erroresLinea) . '.';
                continue;
            }

            $registrosValidados[] = compact('contrato', 'concepto', 'cantidadHoras', 'valor');
        }

        if (!empty($errores)) {
            return redirect('index_procesos/nomina.procesos.transacciones_via_interface?id=17')
                ->with('mensaje_error', 'No se almacenó ningún registro. ' . implode(' ', $errores));
        }

        if (empty($registrosValidados)) {
            return redirect('index_procesos/nomina.procesos.transacciones_via_interface?id=17')
                ->with('mensaje_error', 'No hay registros correctos para almacenar.');
        }

        DB::beginTransaction();
        try {
            foreach ($registrosValidados as $datos) {
                $valor_a_liquidar = $datos['valor'];
                if ($datos['cantidadHoras'] > 0) {
                    $valor_a_liquidar = $datos['concepto']->get_valor_hora_porcentaje_sobre_basico(
                        $datos['contrato']->salario_x_hora(),
                        $datos['cantidadHoras']
                    );
                }

                $valores = get_valores_devengo_deduccion($datos['concepto']->naturaleza, $valor_a_liquidar);
                $registro = new NomDocRegistro;
                $registro->fill([
                    'nom_doc_encabezado_id' => $encabezado_documento->id,
                    'core_tercero_id' => $datos['contrato']->core_tercero_id,
                    'nom_contrato_id' => $datos['contrato']->id,
                    'fecha' => $encabezado_documento->fecha,
                    'core_empresa_id' => $encabezado_documento->core_empresa_id,
                    'nom_concepto_id' => $datos['concepto']->id,
                    'cantidad_horas' => $datos['cantidadHoras'],
                    'valor_devengo' => $valores->devengo,
                    'valor_deduccion' => $valores->deduccion,
                    'estado' => 'Activo',
                    'creado_por' => Auth::user()->email
                ]);
                $registro->save();
            }

            $encabezado_documento->actualizar_totales();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al almacenar transacciones de nómina desde Excel.', ['exception' => $e]);
            return redirect('index_procesos/nomina.procesos.transacciones_via_interface?id=17')
                ->with('mensaje_error', 'No se almacenó ningún registro porque ocurrió un error durante el proceso.');
        }

        $cantidad_registros = count($registrosValidados);
        return redirect('index_procesos/nomina.procesos.transacciones_via_interface?id=17')
            ->with('flash_message', 'Se almacenaron <b>' . $cantidad_registros . ' registros</b> correctamente en el documento <b>' . $encabezado_documento->descripcion . '</b>.');
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
