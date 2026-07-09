<?php

namespace App\Matriculas;

use App\Tesoreria\TesoPlanPagosEstudiante;
use App\Ventas\VtasDocEncabezado;
use Illuminate\Database\Eloquent\Model;

class FacturaAuxEstudiante extends Model
{
    protected $table = 'sga_facturas_estudiantes';
    
    protected $fillable = ['vtas_doc_encabezado_id', 'matricula_id', 'cartera_estudiante_id'];

    public static function get_factura_por_cartera($cartera_estudiante_id)
    {
        return self::where('cartera_estudiante_id', (int)$cartera_estudiante_id)->first();
    }

    public static function cartera_ya_tiene_factura($cartera_estudiante_id)
    {
        return !is_null(self::get_factura_por_cartera($cartera_estudiante_id));
    }

    public static function mensaje_factura_duplicada($cartera_estudiante_id)
    {
        $factura_estudiante = self::get_factura_por_cartera($cartera_estudiante_id);
        if (is_null($factura_estudiante) || is_null($factura_estudiante->encabezado_factura)) {
            return 'La línea de cartera ID ' . (int)$cartera_estudiante_id . ' ya tiene una factura asociada.';
        }

        $factura = $factura_estudiante->encabezado_factura;
        $prefijo = is_null($factura->tipo_documento_app) ? '' : $factura->tipo_documento_app->prefijo;

        return 'La línea de cartera ID ' . (int)$cartera_estudiante_id . ' ya tiene asociada la factura ' . $prefijo . ' ' . $factura->consecutivo . '.';
    }

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
