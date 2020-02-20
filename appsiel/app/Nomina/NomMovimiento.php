<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomMovimiento extends Model
{
    //protected $table = 'nom_movimientos';
	protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'descripcion', 'total_devengos', 'total_deducciones', 'codigo_referencia_tercero', 'porcentaje','nom_concepto_id','nom_cuota_id','nom_prestamo_id','cantidad_horas','valor_devengo','valor_deduccion','estado', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['Documento', 'Empleado', 'Fecha', 'Detalle', 'Concepto', 'Devengo', 'Deducción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = NomMovimiento::leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_movimientos.nom_doc_encabezado_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_movimientos.core_tercero_id')
            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_movimientos.nom_concepto_id')
            ->select('nom_doc_encabezados.descripcion AS campo1', 'core_terceros.descripcion AS campo2', 'nom_movimientos.fecha AS campo3', 'nom_movimientos.detalle AS campo4', 'nom_conceptos.descripcion AS campo5', 'nom_movimientos.valor_devengo AS campo6', 'nom_movimientos.valor_deduccion AS campo7', 'nom_movimientos.estado AS campo8', 'nom_movimientos.id AS campo9')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
