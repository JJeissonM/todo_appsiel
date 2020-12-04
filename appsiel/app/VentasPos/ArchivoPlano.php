<?php

namespace App\VentasPos;

use App\Inventarios\InvProducto;

class ArchivoPlano
{
	protected $documento_encabezado;
	protected $lineas_archivo_plano;

	const LONGITUD_CODIGO_ARTICULO = 5;
	const LONGITUD_CANTIDAD = 6;
	const LONGITUD_PRECIO_UNITARIO = 5;
	const LONGITUD_PRECIO_TOTAL_LINEA = 6; // IMPORTE

	public function __construct( array $lineas_archivo )
	{
		$this->lineas_archivo_plano = $lineas_archivo;
	}

	public function validar_estructura_archivo()
	{
        $lineas_validadas = [];
        $l=0;
        foreach ($this->lineas_archivo_plano as $num_linea => $linea)
        {
        	$lineas_validadas[$l] = (object)['articulo'=>0,'cantidad'=>0,'precio_unitario'=>0];

			$lineas_validadas[$l]->articulo = $this->validar_codigo_articulo( (int)substr($linea, 0, self::LONGITUD_CODIGO_ARTICULO) );

			$lineas_validadas[$l]->cantidad = $this->formatear_valor_numerico( substr($linea, 5, self::LONGITUD_CANTIDAD), 3, 3);

			$lineas_validadas[$l]->precio_unitario = $this->formatear_valor_numerico( substr($linea, 11, self::LONGITUD_PRECIO_UNITARIO), 5, 0 );

			$lineas_validadas[$l]->precio_total_linea = $this->formatear_valor_numerico( substr($linea, 16, self::LONGITUD_PRECIO_TOTAL_LINEA), 6, 0 );

			$l++;
		}

		return $lineas_validadas;
	}

	public function validar_codigo_articulo( $articulo_id )
	{
		$articulo = InvProducto::find( $articulo_id );

		if ( is_null( $articulo ) )
		{
			return (object)[ 'error' => 'codigo_articulo', 'id' => 0, 'descripcion' => 'Ningún artículo está registrado con este código: ' . $articulo_id, 'unidad_medida1' => '', 'impuesto' => (object)['tasa_impuesto'=>0] ];
		}
		
		return $articulo;
	}

	public function formatear_valor_numerico( $valor, $longitud_parte_entera, $longitud_parte_decimal )
	{
		$parte_entera = (int)substr($valor, 0, $longitud_parte_entera);

		$parte_decimal = (int)substr($valor, $longitud_parte_entera, $longitud_parte_decimal);

		return (float)( $parte_entera + $parte_decimal / pow(10, $longitud_parte_decimal) );
	}
}