<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use App\Ventas\VtasDocEncabezado;

class FacturaEstudiante extends VtasDocEncabezado
{    
    protected $table = 'vtas_doc_encabezados';
    
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'valor_total'];

    public $urls_acciones = '{"store":"facturas_estudiantes"}';
        
    public function datos_auxiliar_factura()
    {
        return $this->belongsTo(FacturaAuxEstudiante::class, 'vtas_doc_encabezado_id');
    }
    
    public function cartera_estudiante()
    {
        return $this->belongsTo('App\Tesoreria\TesoPlanPagosEstudiante', 'cartera_estudiante_id');
    }

}
