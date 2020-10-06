<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class FacturaEstudiante extends Model
{
    //protected $table = 'sga_facturas_estudiantes';
    //protected $fillable = ['vtas_doc_encabezado_id', 'matricula_id', 'cartera_estudiante_id'];
    
    protected $table = 'vtas_doc_encabezados';
    
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'valor_total'];

    public $urls_acciones = '{"store":"facturas_estudiantes"}';

    public function encabezado_factura()
    {
        return $this->belongsTo('App\Ventas\VtasDocEncabezado', 'vtas_doc_encabezado_id');
    }
    
    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
    
    public function cartera_estudiante()
    {
        return $this->belongsTo('App\Tesoreria\TesoCarteraEstudiante', 'cartera_estudiante_id');
    }

}
