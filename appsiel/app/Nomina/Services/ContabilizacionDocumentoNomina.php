<?php 

namespace App\Nomina\Services;

use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;

use App\Core\Tercero;
use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;
use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;
use Illuminate\Support\Facades\Auth;

class ContabilizacionDocumentoNomina
{
	public $encabezado_doc;
	public $valor_debito_total;
	public $valor_credito_total;
	public $movimiento_contabilizar;
	public $ids_contratos = [];

	public function __construct( $nom_doc_encabezado_id )
	{
		$this->encabezado_doc = NomDocEncabezado::find( $nom_doc_encabezado_id );
	}

	public function set_movimiento_contabilizar()
	{
		$registros_liquidacion = $this->encabezado_doc->registros_liquidacion()->orderBy('core_tercero_id')->get();

		$this->valor_debito_total = 0;
		$this->valor_credito_total = 0;
		$this->movimiento_contabilizar = collect([]);
		foreach ( $registros_liquidacion as $linea_registro_nomina )
		{
			$equ_contab = $linea_registro_nomina->concepto->equivalencia_contable( $linea_registro_nomina->contrato->grupo_empleado_id );

			if ( is_null( $equ_contab) )
			{
				$equ_contab = (object)[ 'id' => 0, 'cuenta_contable' => null, 'tipo_causacion' => null, 'tercero_movimiento' => null, 'tercero' => null, 'tipo_movimiento' => null, 'concepto' => null ];
			}

			if ( is_null( $equ_contab->cuenta_contable ) )
			{
				$cuenta_contable = null;
			}else{
				$cuenta_contable = $equ_contab->cuenta_contable;
			}

			$valor_debito = $this->get_valor_debito( $equ_contab, $linea_registro_nomina );
			$valor_credito = $this->get_valor_credito( $equ_contab, $linea_registro_nomina );

			$tercero_mov = $this->get_tercero_movimiento( $equ_contab, $linea_registro_nomina->contrato );
			$registro_equivalencia_contable = (object)[
									'es_contrapartida' => 0,
									'error' => 0,
									'equivalencia_contable' => $equ_contab,
									'concepto' => $linea_registro_nomina->concepto,
									'core_tipo_transaccion_id' => $this->encabezado_doc->core_tipo_transaccion_id,
									'core_tipo_doc_app_id' => $this->encabezado_doc->core_tipo_doc_app_id,
									'consecutivo' => $this->encabezado_doc->consecutivo,
									'fecha' => $this->encabezado_doc->fecha,
									'core_empresa_id' => $this->encabezado_doc->core_empresa_id,
									'tercero' => $tercero_mov,
									'cuenta_contable' => $cuenta_contable,
									'valor_debito' => $valor_debito,
									'valor_credito' => $valor_credito,
									'tipo_transaccion' => $equ_contab->tipo_causacion,
									'estado' => 'Activo',
									'creado_por' => Auth::user()->email,
									'fecha_vencimiento' => $this->encabezado_doc->fecha
								];			

			$this->valor_debito_total += $valor_debito;
			$this->valor_credito_total += $valor_credito;

			$this->movimiento_contabilizar->push( $registro_equivalencia_contable );
		}

		$this->set_movimiento_credito();
	}

	public function set_movimiento_credito()
	{
		$cta_contapartida = ContabCuenta::find( (int)config('nomina.cuenta_id_salarios_por_pagar') );

		if ( (int)config('nomina.tercero_id_salarios_por_pagar') == 0 )
		{
			// Un registro credito por cada empleado
			
			$empleados = $this->encabezado_doc->empleados;
			$registros_liquidacion = $this->encabezado_doc->registros_liquidacion;
			
			foreach ( $empleados as $empleado )
			{
				$total_devengos = $registros_liquidacion->where('nom_contrato_id',$empleado->id)->sum('valor_devengo');
				$total_deducciones = $registros_liquidacion->where('nom_contrato_id',$empleado->id)->sum('valor_deduccion');
				
				if ( $total_devengos >= $total_deducciones )
				{
					$valor_debito = 0;
					$valor_credito = $total_devengos - $total_deducciones;
				}else{
					$valor_debito = $total_deducciones - $total_devengos;
					$valor_credito = 0;
				}

				$this->movimiento_contabilizar->push( (object)[
					'es_contrapartida' => 1,
					'error' => 0,
					'equivalencia_contable' => null,
					'concepto' => null,
					'core_tipo_transaccion_id' => $this->encabezado_doc->core_tipo_transaccion_id,
					'core_tipo_doc_app_id' => $this->encabezado_doc->core_tipo_doc_app_id,
					'consecutivo' => $this->encabezado_doc->consecutivo,
					'fecha' => $this->encabezado_doc->fecha,
					'core_empresa_id' => $this->encabezado_doc->core_empresa_id,
					'tercero' => $empleado->tercero,
					'cuenta_contable' => $cta_contapartida,
					'valor_debito' => $valor_debito,
					'valor_credito' => $valor_credito ,
					'tipo_transaccion' => 'crear_cxp',
					'estado' => 'Activo',
					'creado_por' => Auth::user()->email,
					'fecha_vencimiento' => $this->encabezado_doc->fecha
				] );
			}
		}else{

			// Un solo registro credito
			$tercero_id = (int)config('nomina.tercero_id_salarios_por_pagar');
			if ( $this->valor_debito_total >= $this->valor_credito_total )
			{
				$valor_debito = 0;
				$valor_credito = $this->valor_debito_total - $this->valor_credito_total;
			}else{
				$valor_debito = $this->valor_credito_total - $this->valor_debito_total;
				$valor_credito = 0;
			}
			$this->movimiento_contabilizar->push( (object)[
															'es_contrapartida' => 1,
															'error' => 0,
															'equivalencia_contable' => null,
															'concepto' => null,
															'core_tipo_transaccion_id' => $this->encabezado_doc->core_tipo_transaccion_id,
															'core_tipo_doc_app_id' => $this->encabezado_doc->core_tipo_doc_app_id,
															'consecutivo' => $this->encabezado_doc->consecutivo,
															'fecha' => $this->encabezado_doc->fecha,
															'core_empresa_id' => $this->encabezado_doc->core_empresa_id,
															'tercero' => Tercero::find( $tercero_id ),
															'cuenta_contable' => $cta_contapartida,
															'valor_debito' => $valor_debito,
															'valor_credito' => $valor_credito,
															'tipo_transaccion' => 'crear_cxp',
															'estado' => 'Activo',
															'creado_por' => Auth::user()->email,
															'fecha_vencimiento' => $this->encabezado_doc->fecha
														] );
		}

	}

	public function get_tercero_movimiento( $equ_contab, NomContrato $contrato )
	{
		$tercero = (object)['id'=>0,'numero_identificacion'=>0,'descripcion'=>0];

		switch ( $equ_contab->tercero_movimiento )
		{
			case 'empleado':
				$tercero = $contrato->tercero;
				break;

			case 'entidad_relacionada':

				if ( !is_null($equ_contab->concepto) )
				{
					switch( $equ_contab->concepto->modo_liquidacion_id )
					{
						case '10': // Fondo Solidaridad Pensional
							$tercero = $contrato->entidad_pension->tercero;
							break;
						case '12': // Salud Obligatoria
							$tercero = $contrato->entidad_salud->tercero;
							break;
						case '13': // Pension Obligatoria
							$tercero = $contrato->entidad_pension->tercero;
							break;
						default:
							// 
							break;
					}
				}

				break;
			case 'tercero_especifico':
				
				if ( $equ_contab->tercero_especifico != null )
				{
					$tercero = $equ_contab->tercero_especifico;
				}

				break;
			
			default:
				//
				break;
		}

		if( is_null($tercero) )
		{
			$tercero = (object)[ 'id' => 0, 'numero_identificacion' => 0, 'descripcion'  => 'Tercero no esta definido. Por favor revise el Tercero asociado a la Equivalencia contable de este Concepto.'];
		}

		return $tercero;
	}

	public function get_valor_debito( $equ_contab, $linea_registro_nomina )
	{
		$valor_debito = 0;

		if( $equ_contab->tipo_movimiento == 'debito' )
		{
			$valor_debito = $linea_registro_nomina->valor_devengo + $linea_registro_nomina->valor_deduccion;
		}

		return $valor_debito;
	}

	public function get_valor_credito( $equ_contab, $linea_registro_nomina )
	{
		$valor_credito = 0;

		if( $equ_contab->tipo_movimiento == 'credito' )
		{
			$valor_credito = $linea_registro_nomina->valor_devengo + $linea_registro_nomina->valor_deduccion;
		}

		return $valor_credito;
	}

	public function get_lineas_html_movimiento_contable()
	{
		$this->set_movimiento_contabilizar();

		$lineas_tabla = [];
		foreach ( $this->movimiento_contabilizar as $movimiento )
		{
            if( $movimiento->concepto == null && ( $movimiento->valor_debito + $movimiento->valor_credito ) == 0 )
            {
            	continue;
            }
            
			$observacion = $this->get_observacion( $movimiento );

			$movimiento->error = $observacion->error;

			$concepto = '';
			if ( !is_null( $movimiento->concepto ) )
			{
				$concepto = $movimiento->concepto->id . ' ' . $movimiento->concepto->descripcion;
			}

			$cuenta_contable = '';
			if ( !is_null( $movimiento->cuenta_contable ) )
			{
				$cuenta_contable = $movimiento->cuenta_contable->codigo . ' ' . $movimiento->cuenta_contable->descripcion;
			}

			$lineas_tabla[] = (object)[
										'error' => $observacion->error,
										'tipo_causacion' => $this->get_tipo_causacion( $movimiento ),
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

	public function get_tipo_causacion( $linea_movimiento_contab )
	{
		if ( $linea_movimiento_contab->es_contrapartida )
		{
			return 'Crear CxP';
		}

		$tipo_causacion = 'Normal';

		if ( is_null($linea_movimiento_contab->equivalencia_contable) )
		{
			return $tipo_causacion;
		}
		
		switch ( $linea_movimiento_contab->equivalencia_contable->tipo_causacion )
		{
			case 'causacion':
				$tipo_causacion = 'Normal';
				break;
			case 'crear_cxp':
				$tipo_causacion = 'Crear CxP';
				break;
			case 'crear_cxc':
				$tipo_causacion = 'Crear CxC';
				break;
			case 'anticipo_cxp':
				$tipo_causacion = 'Anticipo CxP';
				break;
			case 'anticipo_cxc':
				$tipo_causacion = 'Anticipo CxC';
				break;
			
			default:
				//
				break;
		}

		return $tipo_causacion;
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

	public function almacenar_movimiento_contable()
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
        	$datos['consecutivo'] = $movimiento->consecutivo;
        	$datos['core_empresa_id'] = $movimiento->core_empresa_id;
        	$datos['core_tercero_id'] = $movimiento->tercero->id;
        	$datos['fecha'] = $movimiento->fecha;
        	$datos['fecha_vencimiento'] = $movimiento->fecha;

        	$datos['contab_cuenta_id'] = $movimiento->cuenta_contable->id;
        	$datos['detalle_operacion'] = 'Causación documento de nómina.';
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
            	//$datos['doc_proveedor_prefijo'] = ;
            	//$datos['doc_proveedor_consecutivo'] = ;
            	$datos['valor_documento'] = $movimiento->valor_credito;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $movimiento->valor_credito;
                $datos['estado'] = 'Pendiente';
                CxpMovimiento::create( $datos );
            }

            // Anticipos de CxP
	        if ( $movimiento->tipo_transaccion == 'anticipo_cxp' )
	        {
	            $datos['core_tipo_transaccion_id'] = $movimiento->core_tipo_transaccion_id;
            	$datos['core_tipo_doc_app_id'] = $movimiento->core_tipo_doc_app_id;
            	$datos['consecutivo'] = $movimiento->consecutivo;
            	$datos['core_empresa_id'] = $movimiento->core_empresa_id;
            	$datos['core_tercero_id'] = $movimiento->tercero->id;
            	$datos['fecha'] = $movimiento->fecha;
            	$datos['fecha_vencimiento'] = $movimiento->fecha;
            	$datos['valor_documento'] = $movimiento->valor_debito * -1;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $movimiento->valor_debito * -1;
            	$datos['creado_por'] = $movimiento->creado_por;
                $datos['estado'] = 'Pendiente';
	            CxpMovimiento::create( $datos );
	        }

            // Anticipos de CxC
	        if ( $movimiento->tipo_transaccion == 'anticipo_cxc' )
	        {
	            $datos['core_tipo_transaccion_id'] = $movimiento->core_tipo_transaccion_id;
            	$datos['core_tipo_doc_app_id'] = $movimiento->core_tipo_doc_app_id;
            	$datos['consecutivo'] = $movimiento->consecutivo;
            	$datos['core_empresa_id'] = $movimiento->core_empresa_id;
            	$datos['core_tercero_id'] = $movimiento->tercero->id;
            	$datos['fecha'] = $movimiento->fecha;
            	$datos['fecha_vencimiento'] = $movimiento->fecha;
            	$datos['valor_documento'] = $movimiento->valor_credito * -1;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $movimiento->valor_credito * -1;
            	$datos['creado_por'] = $movimiento->creado_por;
                $datos['estado'] = 'Pendiente';
	            CxcMovimiento::create( $datos );
	        }

            // Generar CxC
            if ( $movimiento->tipo_transaccion == 'crear_cxc' )
            {
	            $datos['core_tipo_transaccion_id'] = $movimiento->core_tipo_transaccion_id;
            	$datos['core_tipo_doc_app_id'] = $movimiento->core_tipo_doc_app_id;
            	$datos['consecutivo'] = $movimiento->consecutivo;
            	$datos['core_empresa_id'] = $movimiento->core_empresa_id;
            	$datos['core_tercero_id'] = $movimiento->tercero->id;
            	$datos['fecha'] = $movimiento->fecha;
            	$datos['fecha_vencimiento'] = $movimiento->fecha;
            	$datos['valor_documento'] = $movimiento->valor_debito;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $movimiento->valor_debito;
            	$datos['creado_por'] = $movimiento->creado_por;
                $datos['estado'] = 'Pendiente';
	            CxcMovimiento::create( $datos );
            }
		}
	}

	public function get_estado()
	{
		$cantidad_registros_contab = ContabMovimiento::where( 'core_tipo_transaccion_id', $this->encabezado_doc->core_tipo_transaccion_id)
											->where( 'core_tipo_doc_app_id', $this->encabezado_doc->core_tipo_doc_app_id)
											->where( 'consecutivo', $this->encabezado_doc->consecutivo)
											->count();

		if ( $cantidad_registros_contab > 0 )
		{
			return 'contabilizado';
		}else{
			return 'pendiente';
		}
	}

	public function retirar_contabilizacion()
	{
        $array_wheres2 = [ 'core_empresa_id'=>$this->encabezado_doc->core_empresa_id, 
            'core_tipo_transaccion_id' => $this->encabezado_doc->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $this->encabezado_doc->core_tipo_doc_app_id,
            'consecutivo' => $this->encabezado_doc->consecutivo];

        // VALIDACIONES DE ABONOS DE CXC
        $array_where = ['core_empresa_id'=>$this->encabezado_doc->core_empresa_id, 
            'doc_cxc_transacc_id' => $this->encabezado_doc->core_tipo_transaccion_id,
            'doc_cxc_tipo_doc_id' => $this->encabezado_doc->core_tipo_doc_app_id,
            'doc_cxc_consecutivo' => $this->encabezado_doc->consecutivo];
        $cantidad = CxcAbono::where( $array_where )->count();

        if( $cantidad != 0 )
        {
            return 'Documento NO puede ser retirado. Algunos registros tienen abonos de CxC.';
        }

		// Se retira moiiento de cartera y anticipos
        CxcMovimiento::where( $array_wheres2 )->delete();

        // VALIDACION DE ABONOS DE CXP
        $array_where = ['core_empresa_id'=>$this->encabezado_doc->core_empresa_id, 
            'doc_cxp_transacc_id' => $this->encabezado_doc->core_tipo_transaccion_id,
            'doc_cxp_tipo_doc_id' => $this->encabezado_doc->core_tipo_doc_app_id,
            'doc_cxp_consecutivo' => $this->encabezado_doc->consecutivo];
        $cantidad = CxpAbono::where( $array_where )
                                ->count();

        if( $cantidad != 0 )
        {
            return 'Documento NO puede ser retirado. Algunos registros tienen abonos de CxP.';
        }

        // Se retira moiiento de cartera y anticipos
        CxpMovimiento::where( $array_wheres2 )->delete();

        // RETIRO DEL MOVIMIENTO CONTABLE
		ContabMovimiento::where( $array_wheres2 )->delete();

        //$encabezado_doc->estado = 'Activo';
        //$encabezado_doc->save();

		return 'ok';
	}
}