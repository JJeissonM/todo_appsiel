<?php

namespace App\Nomina\TransaccionesViaInterfaz;

use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\NomConcepto;
use App\Core\Tercero;

class ArchivoPlano
{
	protected $documento_encabezado;
	protected $lineas_archivo_plano;

	const LONGITUD_NUM_IDENTIFICACION_EMPLEADO = 15;
	const LONGITUD_ID_CONCEPTO = 3;
	const LONGITUD_CANT_HORAS = 7;
	const LONGITUD_VALOR = 12;

	public function __construct( NomDocEncabezado $documento_encabezado, array $lineas_archivo )
	{
		$this->documento_encabezado = $documento_encabezado;
		$this->lineas_archivo_plano = $lineas_archivo;
	}

	public function validar_estructura_archivo()
	{
        $lineas_validadas = [];
        $l=0;
        foreach ($this->lineas_archivo_plano as $num_linea => $linea)
        {
        	$lineas_validadas[$l] = (object)['tercero'=>0,'contrato'=>0,'concepto'=>0,'cantidad_horas'=>0,'valor'=>0];

			$lineas_validadas[$l]->tercero = $this->validar_tercero( (int)substr($linea, 0, self::LONGITUD_NUM_IDENTIFICACION_EMPLEADO) );

			$lineas_validadas[$l]->contrato = $this->validar_contrato( $lineas_validadas[$l]->tercero );

			$lineas_validadas[$l]->concepto = $this->validar_concepto( (int)substr($linea, 15, self::LONGITUD_ID_CONCEPTO), $lineas_validadas[$l]->contrato );

			$lineas_validadas[$l]->cantidad_horas = $this->formatear_valor_numerico( substr($linea, 18, self::LONGITUD_CANT_HORAS), 5, 2 );

			$lineas_validadas[$l]->valor = $this->formatear_valor_numerico( substr($linea, 25, self::LONGITUD_VALOR), 10, 2 );

			$l++;
		}

		return $lineas_validadas;
	}

	public function validar_tercero( $numero_identificacion )
	{
		$tercero = Tercero::where('numero_identificacion',$numero_identificacion)->get()->first();

		if ( is_null( $tercero ) )
		{
			return (object)[ 'error' => 'numero_identificacion', 'id' => 0, 'numero_identificacion' => $numero_identificacion, 'descripcion' => 'Ningún Tercero está registrado con ese número de identificación.' ];
		}
		
		return $tercero;
	}

	public function validar_contrato( $tercero )
	{
		$contrato = NomContrato::where('core_tercero_id',$tercero->id )->where( 'estado', 'Activo' )->get()->first();
		if ( is_null( $contrato ) )
		{
			return (object)[ 'error' => 'contrato', 'id' => 0, 'core_tercero_id' => 0, 'cargo' => (object)['descripcion'=>'Tercero no tiene ningún contrato activo.' ] ];
		}
		
		return $contrato;
	}

	public function validar_concepto( $concepto_id, $contrato )
	{
		// No existe o está inactivo
		$concepto = NomConcepto::find( $concepto_id );
		if ( is_null( $concepto ) )
		{
			return (object)[ 'error' => 'concepto', 'id' => $concepto_id, 'descripcion' => 'Concepto no existe.', 'naturaleza' => '' ];
		}

		if ( $concepto->estado == 'Inactivo' )
		{
			return (object)[ 'error' => 'concepto', 'id' => $concepto_id, 'descripcion' => 'Concepto está Inactivo.', 'naturaleza' => '' ];
		}

		// Solo se permiten cargar vía interfaz concepto con modo liquidación Manual (modo_liquidacion_id = 2)
		if ( $concepto->modo_liquidacion_id != 2 )
		{
			return (object)[ 'error' => 'concepto', 'id' => $concepto_id, 'descripcion' => 'Solo se permiten cargar vía interfaz conceptos con modos de liquidación Manual.', 'naturaleza' => '' ];
		}
		
		// Ya está liquidado en el documento de nómina para ese empleado
		$cant = NomDocRegistro::where('nom_doc_encabezado_id', $this->documento_encabezado->id)
                                ->where('core_tercero_id', $contrato->core_tercero_id)
                                ->where('nom_concepto_id', $concepto->id)
                                ->count();

        if ( $cant != 0 )  // Ya está registrado
        {
            return (object)[ 'error' => 'concepto', 'id' => $concepto_id, 'descripcion' => $concepto->descripcion . '<br><span style="color:red;">Concepto ya está registrado en el documento actual para este empleado.</span>', 'naturaleza' => '' ];
        }

		return $concepto;
	}

	public function formatear_valor_numerico( $valor, $longitud_parte_entera, $longitud_parte_decimal )
	{
		$parte_entera = (int)substr($valor, 0, $longitud_parte_entera);
		$parte_decimal = (int)substr($valor, $longitud_parte_entera, 2);

		return (float)( $parte_entera + $parte_decimal / 100 );
	}
}