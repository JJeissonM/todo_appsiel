<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

class FacturaPos extends Model
{
    protected $table = 'vtas_pos_doc_encabezados';
	protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'remision_doc_encabezado_id ', 'ventas_doc_relacionado_id', 'cliente_id ', 'vendedor_id ', 'pdv_id', 'cajero_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'descripcion', 'valor_total', 'estado', 'creado_por', 'modificado_por'];
	public $encabezado_tabla = ['Fecha', 'Documento', 'Cliente', 'Detalle', 'Valor total', 'PDV', 'Acción'];
	public static function consultar_registros()
	{
	    return FacturaPos::select('vtas_pos_doc_encabezados.consecutivo AS campo1', 'vtas_pos_doc_encabezados.fecha AS campo2', 'vtas_pos_doc_encabezados.core_empresa_id AS campo3', 'vtas_pos_doc_encabezados.core_tercero_id AS campo4', 'vtas_pos_doc_encabezados.remision_doc_encabezado_id  AS campo5', 'vtas_pos_doc_encabezados.ventas_doc_relacionado_id AS campo6', 'vtas_pos_doc_encabezados.id AS campo7')
	    ->get()
	    ->toArray();
	}
	public static function opciones_campo_select()
    {
        $opciones = FacturaPos::where('vtas_pos_doc_encabezados.estado','Activo')
                    ->select('vtas_pos_doc_encabezados.id','vtas_pos_doc_encabezados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
