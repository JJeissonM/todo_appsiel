<?php 

namespace App\Nomina\Services;

use App\Nomina\NomDocEncabezado;

use Auth;

use App\Core\Tercero;
use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabDocEncabezado;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\Nomina\ContabilizacionProceso;
use App\Nomina\PilaDatosEmpresa;

use App\Nomina\ValueObjects\LapsoNomina;

use App\Nomina\Services\Pila\SaludService;
use App\Nomina\Services\Pila\PensionService;
use App\Nomina\Services\Pila\RiesgoLaboralService;
use App\Nomina\Services\Pila\ParafiscalService;

use App\Nomina\Services\LineaDocumentoService;

class ContabilizacionPilaNomina
{
	public $fecha_final_promedios;
	public $encabezado_doc;
	public $valor_debito_total;
	public $valor_credito_total;
	public $movimiento_contabilizar;

	public $core_tipo_transaccion_id;
	public $consecutivo;

	public function __construct( $fecha_final_promedios, $core_tipo_doc_app_id )
	{
		$this->fecha_final_promedios = $fecha_final_promedios;
		$this->core_tipo_transaccion_id = 9;
		$this->core_tipo_doc_app_id = $core_tipo_doc_app_id;
	}

	// La contabilización se hará con base en la planilla generada en el mes a contabilizar
	public function set_movimiento_contabilizar()
	{
		$registros_consolidados = [];
		$pila_service = new SaludService();
        $registros_consolidados = array_merge( $registros_consolidados, $pila_service->get_total_cotizacion_por_entidad( $this->fecha_final_promedios ) );

		$pila_service = new PensionService();
        $registros_consolidados = array_merge( $registros_consolidados, $pila_service->get_total_cotizacion_por_entidad( $this->fecha_final_promedios ) );

		$pila_service = new RiesgoLaboralService();
        $registros_consolidados = array_merge( $registros_consolidados, $pila_service->get_total_cotizacion_por_entidad( $this->fecha_final_promedios ) );

		$pila_service = new ParafiscalService();
        $registros_consolidados = array_merge( $registros_consolidados, $pila_service->get_total_cotizacion_por_entidad( $this->fecha_final_promedios ) );

		$this->valor_debito_total = 0;
		$this->valor_credito_total = 0;
		$this->movimiento_contabilizar = collect([]);
		$datos_empresa = PilaDatosEmpresa::find(1);

		// Por cada registro se generan dos movimientos contables
		foreach ( $registros_consolidados as $linea_registro )
		{
			$valor_cotizacion = $linea_registro->total_cotizacion;

			switch ( $linea_registro->entidad->tipo_entidad )
			{
				case 'EPS':
					$cuenta_contable_db_id = $datos_empresa->contab_cuenta_db_eps_id;
					$cuenta_contable_cr_id = $datos_empresa->contab_cuenta_cr_eps_id;
					$valor_cotizacion = $linea_registro->total_cotizacion * $datos_empresa->porcentaje_eps_empresa / 100;
					break;
				
				case 'AFP':
					$cuenta_contable_db_id = $datos_empresa->contab_cuenta_db_afp_id;
					$cuenta_contable_cr_id = $datos_empresa->contab_cuenta_cr_afp_id;

					$linea_doc_service = new LineaDocumentoService();

					$modo_liquidacion_id = 13; // Pensión obligatoria
					$valor_pension_obligatoria = $linea_doc_service->total_deducciones_modo_liquidacion_entidad_pension( new LapsoNomina($this->fecha_final_promedios), $modo_liquidacion_id, $linea_registro->entidad->id );

					$modo_liquidacion_id = 10; // FondoSolidaridadPensional
					$valor_fondo_solidaridad = $linea_doc_service->total_deducciones_modo_liquidacion_entidad_pension( new LapsoNomina($this->fecha_final_promedios), $modo_liquidacion_id, $linea_registro->entidad->id );
					
					// A lo que ya está liquidado en la planilla se le resta lo que ya está descontado en los documentos de nómina; y que ya debería estar contabilizado.
					$valor_cotizacion = $linea_registro->total_cotizacion - $valor_pension_obligatoria - $valor_fondo_solidaridad;

					if( $linea_registro->entidad->tercero->numero_identificacion == 900336004 )
					{
						//dd( $linea_registro, $valor_fondo_solidaridad, $valor_pension_obligatoria, $valor_cotizacion );
					}
					break;
				
				case 'ARL':
					$cuenta_contable_db_id = $datos_empresa->contab_cuenta_db_arl_id;
					$cuenta_contable_cr_id = $datos_empresa->contab_cuenta_cr_arl_id;
					break;
				
				case 'CCF':
					$cuenta_contable_db_id = $datos_empresa->contab_cuenta_db_caja_compensacion_id;
					$cuenta_contable_cr_id = $datos_empresa->contab_cuenta_cr_caja_compensacion_id;
					break;
				
				case 'PARAFISCALES':
					switch ( $linea_registro->entidad->codigo_nacional )
					{
						case 'PASENA':
							$cuenta_contable_db_id = $datos_empresa->contab_cuenta_db_sena_id;
							$cuenta_contable_cr_id = $datos_empresa->contab_cuenta_cr_sena_id;
							break;
						
						case 'PAICBF':
							$cuenta_contable_db_id = $datos_empresa->contab_cuenta_db_icbf_id;
							$cuenta_contable_cr_id = $datos_empresa->contab_cuenta_cr_icbf_id;
							break;
						
						default:
							// code...
							break;
					}
					break;
				
				default:
					// code...
					break;
			}

			// Algunas entidades no registran valor cotizado para contabilizar
			if ( $valor_cotizacion == 0 )
			{
				continue;
			}

			$cuenta_contable_db = ContabCuenta::find( $cuenta_contable_db_id );
			$cuenta_contable_cr = ContabCuenta::find( $cuenta_contable_cr_id );

			// REG. DEBITO
			$valor_debito = $valor_cotizacion;
			$registro_equivalencia_contable = (object)[
									'error' => 0,
									'concepto' => 'Aporte ' . $linea_registro->entidad->tipo_entidad,
									'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
									'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
									'consecutivo' => 0,
									'fecha' => $this->fecha_final_promedios,
									'core_empresa_id' =>  Auth::user()->empresa_id,
									'tercero' => $linea_registro->entidad->tercero,
									'cuenta_contable' => $cuenta_contable_db,
									'valor_debito' => $valor_debito,
									'valor_credito' => 0,
									'tipo_transaccion' => 'causacion',
									'estado' => 'Activo',
									'creado_por' => Auth::user()->email,
									'fecha_vencimiento' => $this->fecha_final_promedios,
									'registro_consolidado' => $linea_registro
								];

			$this->valor_debito_total += $valor_debito;
			$this->movimiento_contabilizar->push( $registro_equivalencia_contable );

			// REG. CREDITO
			$valor_credito = $valor_cotizacion;
			$registro_equivalencia_contable = (object)[
									'error' => 0,
									'concepto' => 'Aporte ' . $linea_registro->entidad->tipo_entidad,
									'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
									'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
									'consecutivo' => 0,
									'fecha' => $this->fecha_final_promedios,
									'core_empresa_id' =>  Auth::user()->empresa_id,
									'tercero' => $linea_registro->entidad->tercero,
									'cuenta_contable' => $cuenta_contable_cr,
									'valor_debito' => 0,
									'valor_credito' => $valor_credito,
									'tipo_transaccion' => 'crear_cxp',
									'estado' => 'Activo',
									'creado_por' => Auth::user()->email,
									'fecha_vencimiento' => $this->fecha_final_promedios,
									'registro_consolidado' => $linea_registro
								];

			$this->movimiento_contabilizar->push( $registro_equivalencia_contable );
			$this->valor_credito_total += $valor_credito;
		}

	}


	public function get_lineas_html_movimiento_contable()
	{
		$this->set_movimiento_contabilizar();

		$lineas_tabla = [];
		foreach ( $this->movimiento_contabilizar as $movimiento )
		{
			$observacion = $this->get_observacion( $movimiento );

			$movimiento->error = $observacion->error;

			$concepto = $movimiento->concepto;

			$cuenta_contable = '';
			if ( !is_null( $movimiento->cuenta_contable ) )
			{
				$cuenta_contable = $movimiento->cuenta_contable->codigo . ' ' . $movimiento->cuenta_contable->descripcion;
			}

			$lineas_tabla[] = (object)[
										'error' => $observacion->error,
										'tipo_causacion' => $movimiento->tipo_transaccion,
										'cuenta_contable' => $cuenta_contable,
										'tercero_movimiento' => $movimiento->tercero->numero_identificacion . ' ' . $movimiento->tercero->descripcion,
										'concepto' => $concepto,
										'valor_debito' => $movimiento->valor_debito,
										'valor_credito' => $movimiento->valor_credito,
										'observacion' => $observacion->descripcion,
									];
		}

		return $lineas_tabla;
	}


	public function get_observacion( $linea_movimiento_contab )
	{
		$error = 0;
		$descripcion = '';

		if ( is_null( $linea_movimiento_contab->cuenta_contable ) )
		{
			$error = 1;
			$descripcion .= '<br>Concepto no registra una cuenta contable relacionada.'; 
		}

		if ( is_null($linea_movimiento_contab->tercero) )
		{
			$error = 1;
			$descripcion .= '<br>El registro no tiene un tercero relacionado.';
		}elseif ( $linea_movimiento_contab->tercero->id == 0 )
		{
			$error = 1;
			$descripcion .= '<br>El registro no tiene un tercero relacionado.'; 
		}

			

		if ( $linea_movimiento_contab->valor_debito + $linea_movimiento_contab->valor_credito == 0 )
		{
			$error = 1;
			$descripcion .= '<br>No hay registros de valores Débito o Crédito.'; 
		}

		return (object)[ 'error' => $error, 'descripcion' => $descripcion ];
	}

	public function crear_encabezado_documento_contable( $consecutivo )
	{
		$movimiento_contabilizar = $this->movimiento_contabilizar;

		$datos_encabezado_doc = $movimiento_contabilizar->first();

		$datos_encabezado_doc->core_tercero_id = (int)config('contabilidad.tercero_default_cierre_ejercicio');

		$datos_encabezado_doc->descripcion = 'Contabilización planilla integrada.';
		$datos_encabezado_doc->consecutivo = $consecutivo;

		$datos_encabezado_doc->proceso_contabilizado = 'planilla_integrada';

		$datos_encabezado_doc = json_decode( json_encode( $datos_encabezado_doc ) , true);

		// Se registra el proceso de contabilización
		ContabilizacionProceso::create( $datos_encabezado_doc );

		return ContabDocEncabezado::create( $datos_encabezado_doc );
	}

	public function almacenar_movimiento_contable( $consecutivo )
	{
		$movimiento_contabilizar = $this->movimiento_contabilizar;
		
		foreach ($movimiento_contabilizar as $movimiento )
		{
			if ( $movimiento->error )
			{
				continue;
			}

        	$datos['core_tipo_transaccion_id'] = $movimiento->core_tipo_transaccion_id;
        	$datos['core_tipo_doc_app_id'] = $movimiento->core_tipo_doc_app_id;
        	$datos['consecutivo'] = $consecutivo;
        	$datos['core_empresa_id'] = $movimiento->core_empresa_id;
        	$datos['core_tercero_id'] = $movimiento->tercero->id;
        	$datos['fecha'] = $movimiento->fecha;
        	$datos['fecha_vencimiento'] = $movimiento->fecha;

        	$datos['contab_cuenta_id'] = $movimiento->cuenta_contable->id;
        	$datos['detalle_operacion'] = 'Causación planilla integrada del mes.';
        	$datos['tipo_transaccion'] = $movimiento->tipo_transaccion;

        	$datos['valor_debito'] = $movimiento->valor_debito;
        	$datos['valor_credito'] = $movimiento->valor_credito * -1;
        	$datos['valor_saldo'] = $movimiento->valor_debito - $movimiento->valor_credito;

            $datos['creado_por'] = $movimiento->creado_por;
            $datos['estado'] = 'Activo';

			ContabMovimiento::create( $datos );

			// Generar CxP
            if ( $movimiento->tipo_transaccion == 'crear_cxp' )
            {
            	$datos['valor_documento'] = $movimiento->valor_credito;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $movimiento->valor_credito;
                $datos['estado'] = 'Pendiente';
                CxpMovimiento::create( $datos );
            }
		}
	}

	public function get_estado( $fecha )
	{
		return ContabilizacionProceso::where( [
												[ 'fecha', '=', $fecha ],
												[ 'proceso_contabilizado', '=', 'planilla_integrada' ]
											])
										->get()
										->first();
	}

	public function retirar_contabilizacion( $fecha )
	{
		$encabezado_doc = ContabilizacionProceso::where( [
															[ 'fecha', '=', $fecha ],
															[ 'proceso_contabilizado', '=', 'planilla_integrada' ]
														])
													->get()
													->first();

		if( is_null( $encabezado_doc ) )
        {
            return 'NO hay registros de contabilización de la planilla en el mes seleccionado.';
        }
        
        $array_wheres2 = [ 'core_empresa_id'=> Auth::user()->empresa_id, 
            'core_tipo_transaccion_id' => $encabezado_doc->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $encabezado_doc->core_tipo_doc_app_id,
            'consecutivo' => $encabezado_doc->consecutivo];

        // VALIDACION DE ABONOS DE CXP
        $array_where = ['core_empresa_id'=> Auth::user()->empresa_id, 
            'doc_cxp_transacc_id' => $encabezado_doc->core_tipo_transaccion_id,
            'doc_cxp_tipo_doc_id' => $encabezado_doc->core_tipo_doc_app_id,
            'doc_cxp_consecutivo' => $encabezado_doc->consecutivo];
        $cantidad = CxpAbono::where( $array_where )
                                ->count();

        if( $cantidad != 0 )
        {
            return 'Documento NO puede ser retirado. Algunos registros tienen abonos de CxP.';
        }

        CxpMovimiento::where( $array_wheres2 )->delete();

        // RETIRO DEL MOVIMIENTO CONTABLE

        $encabezado_doc->estado = 'Anulado';
        $encabezado_doc->save();
        
		ContabMovimiento::where( $array_wheres2 )->delete();

		$encabezado_doc->delete();

		return 'ok';
	}
}