<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ResolucionFacturacion extends Model
{
    protected $table = 'vtas_resoluciones_facturacion';
	protected $fillable = ['core_empresa_id', 'sucursal_id', 'tipo_doc_app_id', 'numero_resolucion', 'numero_fact_inicial', 'numero_fact_final', 'fecha_expedicion', 'fecha_expiracion', 'modalidad', 'prefijo', 'tipo_solicitud', 'estado'];
	public $encabezado_tabla = ['Empresa', 'Tipo de documento', 'Número Resolución', 'Número desde', 'Número hasta', 'Fecha expedición', 'Fecha expiración', 'Modalidad', 'Prefijo', 'Tipo solicitud', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ResolucionFacturacion::select('vtas_resoluciones_facturacion.core_empresa_id AS campo1', 'vtas_resoluciones_facturacion.tipo_doc_app_id AS campo2', 'vtas_resoluciones_facturacion.numero_resolucion AS campo3', 'vtas_resoluciones_facturacion.numero_fact_inicial AS campo4', 'vtas_resoluciones_facturacion.numero_fact_final AS campo5', 'vtas_resoluciones_facturacion.fecha_expedicion AS campo6', 'vtas_resoluciones_facturacion.fecha_expiracion AS campo7', 'vtas_resoluciones_facturacion.modalidad AS campo8', 'vtas_resoluciones_facturacion.prefijo AS campo9', 'vtas_resoluciones_facturacion.tipo_solicitud AS campo10', 'vtas_resoluciones_facturacion.estado AS campo11', 'vtas_resoluciones_facturacion.id AS campo12')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
