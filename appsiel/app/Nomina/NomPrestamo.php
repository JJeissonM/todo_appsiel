<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomPrestamo extends Model
{
    //protected $table = 'nom_prestamos';
	protected $fillable = ['core_tercero_id', 'nom_concepto_id', 'fecha_inicio','periodicidad_mensual', 'valor_prestamo', 'valor_cuota', 'numero_cuotas', 'valor_acumulado', 'detalle', 'estado'];
	public $encabezado_tabla = ['Empleado', 'Concepto', 'Fecha inicio', 'Valor prestamo', 'Valor cuota', 'Núm. cuotas', 'Valor acumulado', 'Detalle', 'Estado', 'Acción'];

	// El archivo js debe estar en la carpeta public
	public $archivo_js = 'assets/js/nomina/prestamos.js';

	public static function consultar_registros()
	{
	    $registros = NomPrestamo::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_prestamos.core_tercero_id')
            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_prestamos.nom_concepto_id')
            ->select('core_terceros.descripcion AS campo1', 'nom_conceptos.descripcion AS campo2', 'nom_prestamos.fecha_inicio AS campo3', 'nom_prestamos.valor_prestamo AS campo4', 'nom_prestamos.valor_cuota AS campo5', 'nom_prestamos.numero_cuotas AS campo6', 'nom_prestamos.valor_acumulado AS campo7', 'nom_prestamos.detalle AS campo8', 'nom_prestamos.estado AS campo9', 'nom_prestamos.id AS campo10')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
