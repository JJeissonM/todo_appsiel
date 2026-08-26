<?php 

namespace App\Nomina\Services;

use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;
use App\Nomina\PilaDatosEmpresa;
use App\Nomina\EquivalenciaContable;

use App\Core\Tercero;
use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;
use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContabilizacionDocumentoNomina
{
	public $encabezado_doc;
	public $valor_debito_total;
	public $valor_credito_total;
	public $movimiento_contabilizar;
	public $ids_contratos = [];
	protected $tercero_operador_pila;
	protected $operador_pila_configurado = false;

	public function __construct( $nom_doc_encabezado_id )
	{
		$this->encabezado_doc = NomDocEncabezado::find( $nom_doc_encabezado_id );
		if (!is_null($this->encabezado_doc)) {
			$this->set_operador_pila();
		}
	}

	public function set_movimiento_contabilizar()
	{
		if (is_null($this->encabezado_doc)) {
			throw new \InvalidArgumentException('El documento de nómina no existe.');
		}

		$registros_liquidacion = $this->encabezado_doc->registros_liquidacion()
			->with([
				'concepto',
				'tercero',
				'contrato.tercero',
				'contrato.entidad_salud.tercero',
				'contrato.entidad_pension.tercero'
			])
			->orderBy('core_tercero_id')
			->orderBy('id')
			->get();

		$equivalencias = EquivalenciaContable::with(['cuenta_contable', 'tercero_especifico', 'concepto'])
			->whereIn('nom_concepto_id', $registros_liquidacion->pluck('nom_concepto_id')->unique()->values()->all())
			->where('core_empresa_id', $this->encabezado_doc->core_empresa_id)
			->where('estado', 'Activo')
			->orderBy('id')
			->get()
			->groupBy('nom_concepto_id');

		$this->valor_debito_total = 0;
		$this->valor_credito_total = 0;
		$this->movimiento_contabilizar = collect([]);
		foreach ( $registros_liquidacion as $linea_registro_nomina )
		{
			$errores_integridad = [];
			$concepto = $linea_registro_nomina->concepto;
			$contrato = $linea_registro_nomina->contrato;

			if (is_null($concepto)) {
				$errores_integridad[] = 'El registro de nómina #' . $linea_registro_nomina->id . ' no tiene un concepto válido asociado.';
			}

			if (is_null($contrato)) {
				$empleado = is_null($linea_registro_nomina->tercero)
					? 'tercero #' . $linea_registro_nomina->core_tercero_id
					: $linea_registro_nomina->tercero->descripcion;
				$errores_integridad[] = 'El registro de nómina #' . $linea_registro_nomina->id . ' (' . $empleado . ') no tiene un contrato válido asociado. Contrato #' . $linea_registro_nomina->nom_contrato_id . '.';
			}

			$grupo_empleado_id = is_null($contrato) ? null : $contrato->grupo_empleado_id;
			$equ_contab = is_null($concepto)
				? null
				: $this->resolver_equivalencia_contable($equivalencias->get($concepto->id, collect([])), $grupo_empleado_id);

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

			$tercero_mov = is_null($contrato)
				? $this->tercero_inconsistente($linea_registro_nomina)
				: $this->get_tercero_movimiento( $equ_contab, $contrato );
			$tercero_mov = $this->get_tercero_movimiento_cxp_aportes($equ_contab, $linea_registro_nomina, $tercero_mov);
			if (is_null($tercero_mov) || (int)$tercero_mov->id === 0) {
				$detalle_tercero = is_null($tercero_mov) || empty($tercero_mov->descripcion)
					? 'No se pudo determinar el tercero del movimiento contable.'
					: $tercero_mov->descripcion;
				$errores_integridad[] = $detalle_tercero;
			}
			$detalle_operador_pila = $this->get_detalle_operador_pila($equ_contab, $linea_registro_nomina);
			$registro_equivalencia_contable = (object)[
									'es_contrapartida' => 0,
									'error' => 0,
									'equivalencia_contable' => $equ_contab,
									'concepto' => $concepto,
									'error_integridad' => implode('<br>', $errores_integridad),
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
									'detalle_operador_pila' => $detalle_operador_pila,
									'estado' => 'Activo',
									'creado_por' => $this->usuario_actual(),
									'fecha_vencimiento' => $this->encabezado_doc->fecha
								];			

			$this->valor_debito_total += $valor_debito;
			$this->valor_credito_total += $valor_credito;

			$this->movimiento_contabilizar->push( $registro_equivalencia_contable );
		}

		$this->set_movimiento_credito();
		$this->validar_movimiento_balanceado();
	}

	protected function usuario_actual()
	{
		if (Auth::check()) {
			return Auth::user()->email;
		}

		return $this->encabezado_doc->creado_por;
	}

	protected function resolver_equivalencia_contable($equivalencias, $grupo_empleado_id)
	{
		$equivalencia_grupo = null;
		foreach ($equivalencias as $equivalencia) {
			if ((string)$equivalencia->nom_grupo_empleado_id === (string)$grupo_empleado_id) {
				$equivalencia_grupo = $equivalencia;
				break;
			}
		}

		return is_null($equivalencia_grupo) ? $equivalencias->first() : $equivalencia_grupo;
	}

	protected function tercero_inconsistente($linea_registro_nomina)
	{
		if (!is_null($linea_registro_nomina->tercero)) {
			return $linea_registro_nomina->tercero;
		}

		return (object)[
			'id' => 0,
			'numero_identificacion' => $linea_registro_nomina->core_tercero_id,
			'descripcion' => 'Tercero no encontrado'
		];
	}

	public function set_movimiento_credito()
	{
		$cta_contapartida = ContabCuenta::find( (int)config('nomina.cuenta_id_salarios_por_pagar') );

		if ( (int)config('nomina.tercero_id_salarios_por_pagar') == 0 )
		{
			// Un registro credito por cada empleado
			
			$empleados = $this->encabezado_doc->empleados()->with('tercero')->get();
			$registros_liquidacion = $this->encabezado_doc->registros_liquidacion;
			
			foreach ( $empleados as $empleado )
			{
				$total_devengos = $registros_liquidacion->where('nom_contrato_id',$empleado->id)->sum('valor_devengo');
				$total_deducciones = $registros_liquidacion->where('nom_contrato_id',$empleado->id)->sum('valor_deduccion');

				if ((float)$total_devengos === 0.0 && (float)$total_deducciones === 0.0) {
					continue;
				}
				
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
					'error_integridad' => '',
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
					'detalle_movimiento' => '',
					'detalle_operador_pila' => '',
					'estado' => 'Activo',
					'creado_por' => $this->usuario_actual(),
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
				'error_integridad' => '',
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
				'detalle_movimiento' => '',
				'detalle_operador_pila' => '',
				'estado' => 'Activo',
				'creado_por' => $this->usuario_actual(),
				'fecha_vencimiento' => $this->encabezado_doc->fecha
			] );
			}
		}

	protected function validar_movimiento_balanceado()
	{
		$total_debitos = (float)$this->movimiento_contabilizar->sum('valor_debito');
		$total_creditos = (float)$this->movimiento_contabilizar->sum('valor_credito');
		$diferencia = round($total_debitos - $total_creditos, 2);

		if (abs($diferencia) < 0.01) {
			return;
		}

		$linea = $this->movimiento_contabilizar->filter(function ($movimiento) {
			return (float)$movimiento->valor_debito !== 0.0 || (float)$movimiento->valor_credito !== 0.0;
		})->last();
		if (is_null($linea)) {
			return;
		}

		$detalle = 'El movimiento contable está descuadrado. Débitos: $' . number_format($total_debitos, 2, ',', '.') .
			'; créditos: $' . number_format($total_creditos, 2, ',', '.') .
			'; diferencia: $' . number_format(abs($diferencia), 2, ',', '.') . '.';
		$linea->error_integridad = trim($linea->error_integridad . '<br>' . $detalle, '<br>');
	}

	protected function set_operador_pila()
	{
		$datos_empresa = PilaDatosEmpresa::where('core_empresa_id', $this->encabezado_doc->core_empresa_id)
			->where('estado', 'Activo')
			->orderBy('id')
			->first();

		if (is_null($datos_empresa) || (int)$datos_empresa->operador_pila_core_tercero_id <= 0) {
			return;
		}

		$this->operador_pila_configurado = true;
		$this->tercero_operador_pila = Tercero::find((int)$datos_empresa->operador_pila_core_tercero_id);
	}

	protected function get_tercero_movimiento_cxp_aportes($equ_contab, $linea_registro_nomina, $tercero_movimiento)
	{
		if (!$this->operador_pila_configurado) {
			return $tercero_movimiento;
		}

		if ($equ_contab->tipo_causacion != 'crear_cxp' || $equ_contab->tercero_movimiento != 'entidad_relacionada') {
			return $tercero_movimiento;
		}

		if (!$this->es_concepto_aporte_pila($linea_registro_nomina->concepto)) {
			return $tercero_movimiento;
		}

		if (is_null($this->tercero_operador_pila)) {
			return (object)[
				'id' => 0,
				'numero_identificacion' => 0,
				'descripcion'  => 'Operador PILA no está definido. Revise el campo Operador PILA CxP en Datos de la empresa.'
			];
		}

		return $this->tercero_operador_pila;
	}

	protected function es_concepto_aporte_pila($concepto)
	{
		if (is_null($concepto)) {
			return false;
		}

		return !is_null($this->get_tipo_entidad_relacionada($concepto));
	}

	protected function get_detalle_operador_pila($equ_contab, $linea_registro_nomina)
	{
		if (!$this->operador_pila_configurado) {
			return '';
		}

		if ($equ_contab->tipo_causacion != 'crear_cxp' || $equ_contab->tercero_movimiento != 'entidad_relacionada') {
			return '';
		}

		if (!$this->es_concepto_aporte_pila($linea_registro_nomina->concepto)) {
			return '';
		}

		return '';
		//return '<i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="La CxP de este aporte se genera al operador PILA configurado."></i>';
	}

	public function get_tercero_movimiento( $equ_contab, NomContrato $contrato )
	{
		$tercero = $this->tercero_no_definido(
			'No se pudo determinar el tercero asociado a la equivalencia contable.'
		);

		switch ( $equ_contab->tercero_movimiento )
		{
			case 'empleado':
				$tercero = is_null($contrato->tercero)
					? $this->tercero_no_definido('El contrato #' . $contrato->id . ' no tiene un empleado válido asociado.')
					: $contrato->tercero;
				break;

			case 'entidad_relacionada':

				if ( !is_null($equ_contab->concepto) )
				{
					$tipo_entidad = $this->get_tipo_entidad_relacionada($equ_contab->concepto);
					if (!is_null($tipo_entidad)) {
						$tercero = $this->get_tercero_entidad_contrato($contrato, $tipo_entidad);
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

		return $tercero;
	}

	protected function get_tercero_entidad_contrato(NomContrato $contrato, $tipo_entidad)
	{
		$es_salud = $tipo_entidad === 'salud';
		$campo_id = $es_salud ? 'entidad_salud_id' : 'entidad_pension_id';
		$relacion = $es_salud ? 'entidad_salud' : 'entidad_pension';
		$etiqueta = $es_salud ? 'salud' : 'pensión';
		$entidad_id = (int)$contrato->{$campo_id};
		$entidad = $contrato->{$relacion};

		if (is_null($entidad)) {
			$referencia = $entidad_id > 0 ? ' #' . $entidad_id : '';
			return $this->tercero_no_definido(
				'El contrato #' . $contrato->id . ' tiene una entidad de ' . $etiqueta . $referencia . ' inexistente o sin asignar.'
			);
		}

		if (is_null($entidad->tercero)) {
			return $this->tercero_no_definido(
				'La entidad de ' . $etiqueta . ' #' . $entidad->id . ' (' . $entidad->descripcion . ') no tiene un tercero válido asociado.'
			);
		}

		return $entidad->tercero;
	}

	protected function tercero_no_definido($descripcion)
	{
		return (object)[
			'id' => 0,
			'numero_identificacion' => 0,
			'descripcion' => $descripcion
		];
	}

	protected function get_tipo_entidad_relacionada($concepto)
	{
		$modo_liquidacion_id = (int)$concepto->modo_liquidacion_id;
		if ($modo_liquidacion_id === 12) {
			return 'salud';
		}

		if (in_array($modo_liquidacion_id, [10, 13])) {
			return 'pension';
		}

		$codigo_dian = is_null($concepto->cpto_dian) ? '' : $concepto->cpto_dian->codigo;
		if ($codigo_dian === 'SALUD') {
			return 'salud';
		}

		if (in_array($codigo_dian, ['FONDO_PENSION', 'FONDO_SOLIDARIDAD_PENSIONAL'])) {
			return 'pension';
		}

		return null;
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
			if (($movimiento->valor_debito + $movimiento->valor_credito) == 0
					&& empty($movimiento->error_integridad))
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

			$tercero_movimiento = '';
			if (!is_null($movimiento->tercero)) {
				$tercero_movimiento = $movimiento->tercero->numero_identificacion . ' ' . $movimiento->tercero->descripcion;
			}

			$lineas_tabla[] = (object)[
											'error' => $observacion->error,
											'tipo_causacion' => $this->get_tipo_causacion( $movimiento ),
											'cuenta_contable' => $cuenta_contable,
											'tercero_movimiento' => $tercero_movimiento,
										'concepto' => $concepto,
										'valor_debito' => $movimiento->valor_debito,
										'valor_credito' => $movimiento->valor_credito,
										'observacion' => $this->get_detalle_movimiento( $movimiento ) . $observacion->descripcion . $movimiento->detalle_operador_pila,
									];
		}

		return $lineas_tabla;
	}

	protected function get_detalle_movimiento( $movimiento )
	{
		if ( !isset( $movimiento->detalle_movimiento ) || $movimiento->detalle_movimiento == '' )
		{
			return '';
		}

		return $movimiento->detalle_movimiento . '<br>';
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
				$tipo_causacion = 'Anticipo/Saldo a favor CxP';
				break;
			case 'anticipo_cxc':
				$tipo_causacion = 'Anticipo/Saldo a favor CxC';
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

		if (!empty($linea_movimiento_contab->error_integridad)) {
			$error = 1;
			$descripcion .= $linea_movimiento_contab->error_integridad;
		}

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
			if ( $linea_movimiento_contab->es_contrapartida )
			{
				$descripcion .= '<br>La contrapartida del neto a pagar no registra una cuenta contable relacionada.';
			}else{
				$descripcion .= '<br>Concepto no registra una cuenta contable relacionada.';
			}
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
		if (is_null($this->movimiento_contabilizar)) {
			$this->set_movimiento_contabilizar();
		}

		if ($this->movimiento_contabilizar->isEmpty()) {
			throw new \RuntimeException('El documento no tiene movimientos con valores para contabilizar.');
		}

		DB::transaction(function () {
			$documento = NomDocEncabezado::where('id', $this->encabezado_doc->id)->lockForUpdate()->first();
			if (is_null($documento) || $documento->estado != NomDocEncabezado::ESTADO_ACTIVO) {
				throw new \RuntimeException('El documento de nómina ya no está disponible para contabilizar.');
			}

			if ($this->existen_movimientos_contables()) {
				throw new \RuntimeException('El documento de nómina ya tiene movimientos contables registrados.');
			}

			foreach ($this->movimiento_contabilizar as $movimiento )
			{
				$observacion = $this->get_observacion($movimiento);
				if ($observacion->error)
				{
					throw new \RuntimeException('El documento contiene inconsistencias y no puede contabilizarse.');
				}

				$datos = [];
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

			$documento->marcar_contabilizado();
			$this->encabezado_doc = $documento;
		});
	}

	protected function existen_movimientos_contables()
	{
		return ContabMovimiento::where('core_empresa_id', $this->encabezado_doc->core_empresa_id)
			->where('core_tipo_transaccion_id', $this->encabezado_doc->core_tipo_transaccion_id)
			->where('core_tipo_doc_app_id', $this->encabezado_doc->core_tipo_doc_app_id)
			->where('consecutivo', $this->encabezado_doc->consecutivo)
			->exists();
	}

	public function get_estado()
	{
		if (is_null($this->encabezado_doc)) {
			return 'inexistente';
		}

		$cantidad_registros_contab = ContabMovimiento::where('core_empresa_id', $this->encabezado_doc->core_empresa_id)
											->where( 'core_tipo_transaccion_id', $this->encabezado_doc->core_tipo_transaccion_id)
											->where( 'core_tipo_doc_app_id', $this->encabezado_doc->core_tipo_doc_app_id)
											->where( 'consecutivo', $this->encabezado_doc->consecutivo)
											->count();

		if ( $this->encabezado_doc->estado == NomDocEncabezado::ESTADO_CONTABILIZADO || $cantidad_registros_contab > 0 )
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

		// Se retira movimiento de cartera y anticipos
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

        // Se retira movimiento de cartera y anticipos
        CxpMovimiento::where( $array_wheres2 )->delete();

        // RETIRO DEL MOVIMIENTO CONTABLE
		ContabMovimiento::where( $array_wheres2 )->delete();

        $this->encabezado_doc->marcar_activo();

		return 'ok';
	}
}
