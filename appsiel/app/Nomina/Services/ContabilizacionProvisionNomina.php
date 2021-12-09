<?php 

namespace App\Nomina\Services;

use App\Nomina\NomDocEncabezado;

use Illuminate\Support\Facades\Auth;

use App\Core\Tercero;
use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabDocEncabezado;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\Nomina\ContabilizacionProceso;
use App\Nomina\ConsolidadoPrestacionesSociales;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;

class ContabilizacionProvisionNomina
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

	// La contabilización se hará con base en los Consolidados de prestaciones Sociales del mes a contabilizar
	public function set_movimiento_contabilizar()
	{

		$registros_consolidados = ConsolidadoPrestacionesSociales::where( 'fecha_fin_mes', $this->fecha_final_promedios )->get();

		$this->valor_debito_total = 0;
		$this->valor_credito_total = 0;
		$this->movimiento_contabilizar = collect([]);

		// Por cada registro se generan dos movimientos contables
		foreach ( $registros_consolidados as $linea_registro )
		{
			$parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where( [
					[ 'concepto_prestacion', '=', $linea_registro->tipo_prestacion ],
					[ 'grupo_empleado_id', '=', $linea_registro->contrato->grupo_empleado_id]
				] )->get()->first();
			
			if ( is_null( $parametros_prestacion) )
			{
				$parametros_prestacion = (object)[ 'id' => 0, 'cuenta_contable' => null, 'tipo_causacion' => null, 'tercero_movimiento' => null, 'tercero' => null, 'tipo_movimiento' => null, 'concepto' => null ];
			}

			$cuenta_contable_db = null;
			$cuenta_contable_cr = null;
			if ( !is_null( $parametros_prestacion->cuenta_debito ) )
			{
				$cuenta_contable_db = $parametros_prestacion->cuenta_debito;
			}

			if ( !is_null( $parametros_prestacion->cuenta_credito ) )
			{
				$cuenta_contable_cr = $parametros_prestacion->cuenta_credito;
			}

			// REG. DEBITO
			$valor_debito = $linea_registro->valor_consolidado_mes;
			$registro_equivalencia_contable = (object)[
									'es_contrapartida' => 0,
									'error' => 0,
									'equivalencia_contable' => $parametros_prestacion,
									'concepto' => $linea_registro->get_descripcion_prestacion(),
									'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
									'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
									'consecutivo' => 0,
									'fecha' => $this->fecha_final_promedios,
									'core_empresa_id' =>  Auth::user()->empresa_id,
									'tercero' => $linea_registro->contrato->tercero,
									'cuenta_contable' => $cuenta_contable_db,
									'valor_debito' => $valor_debito,
									'valor_credito' => 0,
									'tipo_transaccion' => 'causacion',
									'estado' => 'Activo',
									'creado_por' => Auth::user()->email,
									'fecha_vencimiento' => $this->fecha_final_promedios,
									'registro_consolidado_prestacion' => $linea_registro
								];

			$this->valor_debito_total += $valor_debito;
			$this->movimiento_contabilizar->push( $registro_equivalencia_contable );

			// REG. CREDITO
			$valor_credito = $linea_registro->valor_consolidado_mes;
			$registro_equivalencia_contable = (object)[
									'es_contrapartida' => 0,
									'error' => 0,
									'equivalencia_contable' => $parametros_prestacion,
									'concepto' => $linea_registro->get_descripcion_prestacion(),
									'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
									'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
									'consecutivo' => 0,
									'fecha' => $this->fecha_final_promedios,
									'core_empresa_id' =>  Auth::user()->empresa_id,
									'tercero' => $linea_registro->contrato->tercero,
									'cuenta_contable' => $cuenta_contable_cr,
									'valor_debito' => 0,
									'valor_credito' => $valor_credito,
									'tipo_transaccion' => 'crear_cxp',
									'estado' => 'Activo',
									'creado_por' => Auth::user()->email,
									'fecha_vencimiento' => $this->fecha_final_promedios,
									'registro_consolidado_prestacion' => $linea_registro
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

		if ( !$linea_movimiento_contab->es_contrapartida )
		{
			if( $linea_movimiento_contab->equivalencia_contable->id== 0 )
			{
				$error = 1;
				$descripcion .= 'Concepto no tiene equivalencia contable asignada.'; 
			}
		}

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

		$datos_encabezado_doc->descripcion = 'Provisión de prestaciones sociales.';
		$datos_encabezado_doc->consecutivo = $consecutivo;

		$datos_encabezado_doc->proceso_contabilizado = 'provision_prestaciones_sociales';

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
        	$datos['detalle_operacion'] = 'Causación provisión de nómina del mes.';
        	$datos['tipo_transaccion'] = $movimiento->tipo_transaccion;

        	$datos['valor_debito'] = $movimiento->valor_debito;
        	$datos['valor_credito'] = $movimiento->valor_credito * -1;
        	$datos['valor_saldo'] = $movimiento->valor_debito - $movimiento->valor_credito;

            $datos['creado_por'] = $movimiento->creado_por;
            $datos['estado'] = 'Activo';

			ContabMovimiento::create( $datos );

			$movimiento->registro_consolidado_prestacion->estado = 'Contabilizado';
			$movimiento->registro_consolidado_prestacion->save();

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
												[ 'proceso_contabilizado', '=', 'provision_prestaciones_sociales' ]
											])
										->get()
										->first();
	}

	public function get_document_header( $core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo  )
	{
		$array_wheres = [ 'core_empresa_id'=> Auth::user()->empresa_id, 
            'core_tipo_transaccion_id' => $core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $core_tipo_doc_app_id,
            'consecutivo' => $consecutivo];
		return ContabDocEncabezado::where( $array_wheres )
										->get()
										->first();
	}

	public function retirar_contabilizacion( $fecha )
	{
		$encabezado_doc = ContabilizacionProceso::where( [
															[ 'fecha', '=', $fecha ],
															[ 'proceso_contabilizado', '=', 'provision_prestaciones_sociales' ]
														])
													->get()
													->first();

		if( is_null( $encabezado_doc ) )
        {
            return 'NO hay registros de contabilización de provisiones.';
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