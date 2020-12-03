<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use Auth;

class NomDocRegistro extends Model
{
    //protected $table = 'nom_doc_registros';
	protected $fillable = [ 'nom_doc_encabezado_id', 'core_tercero_id', 'nom_contrato_id', 'fecha', 'core_empresa_id', 'porcentaje', 'detalle', 'nom_concepto_id', 'nom_cuota_id', 'nom_prestamo_id', 'cantidad_horas', 'valor_devengo', 'valor_deduccion', 'estado', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['Documento', 'Empleado', 'Fecha', 'Detalle', 'Concepto', 'Devengo', 'Deducción', 'Estado', 'Acción'];

	public $rutas = [
						'create' => 'web',
						'edit' => 'web/id_fila/edit' 
						];

	public function encabezado_documento()
	{
		return $this->belongsTo(NomDocEncabezado::class, 'nom_doc_encabezado_id');
	}

	public function contrato()
	{
		return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
	}

	public function tercero()
	{
		return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
	}

	public function concepto()
	{
		return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
	}

	public function cuota()
	{
		return $this->belongsTo(NomCuota::class, 'nom_cuota_id');
	}

	public function prestamo()
	{
		return $this->belongsTo(NomCuota::class, 'nom_prestamo_id');
	}
						
	public static function consultar_registros()
	{
	    $registros = NomDocRegistro::leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_doc_registros.nom_doc_encabezado_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_doc_registros.core_tercero_id')
            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_doc_registros.nom_concepto_id')
            ->select('nom_doc_encabezados.descripcion AS campo1', 'core_terceros.descripcion AS campo2', 'nom_doc_registros.fecha AS campo3', 'nom_doc_registros.detalle AS campo4', 'nom_conceptos.descripcion AS campo5', 'nom_doc_registros.valor_devengo AS campo6', 'nom_doc_registros.valor_deduccion AS campo7', 'nom_doc_registros.estado AS campo8', 'nom_doc_registros.id AS campo9')
	    ->get()
	    ->toArray();
	    return $registros;
	}


	public static function listado_acumulados( $fecha_desde, $fecha_hasta, $nom_agrupacion_id)
	{
		if ( $nom_agrupacion_id == '' )
		{
			return NomDocRegistro::where('nom_doc_registros.core_empresa_id', Auth::user()->empresa_id)
					            ->whereBetween('nom_doc_registros.fecha', [$fecha_desde, $fecha_hasta])
					            ->get();
		}

		return NomDocRegistro::leftJoin('nom_agrupacion_tiene_conceptos','nom_agrupacion_tiene_conceptos.nom_concepto_id','=','nom_doc_registros.nom_concepto_id')
							->where('nom_doc_registros.core_empresa_id', Auth::user()->empresa_id)
				            ->whereBetween('nom_doc_registros.fecha', [$fecha_desde, $fecha_hasta])
				            ->where('nom_agrupacion_tiene_conceptos.nom_agrupacion_id', $nom_agrupacion_id)
				            ->get();
							
	}
}
