<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class FacturaAuxEstudiante extends Model
{
    protected $table = 'sga_facturas_estudiantes';
    protected $fillable = ['vtas_doc_encabezado_id', 'matricula_id', 'cartera_estudiante_id'];

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
