<?php

namespace App\Matriculas;

use App\Tesoreria\TesoPlanPagosEstudiante;
use App\Ventas\VtasDocEncabezado;
use Illuminate\Database\Eloquent\Model;

class FacturaAuxEstudiante extends Model
{
    protected $table = 'sga_facturas_estudiantes';
    
    protected $fillable = ['vtas_doc_encabezado_id', 'matricula_id', 'cartera_estudiante_id'];

    public function encabezado_factura()
    {
        return $this->belongsTo(VtasDocEncabezado::class, 'vtas_doc_encabezado_id');
    }
    
    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
    
    public function cartera_estudiante()
    {
        return $this->belongsTo(TesoPlanPagosEstudiante::class, 'cartera_estudiante_id');
    }
}
