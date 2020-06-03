<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Planillac extends Model
{
    // Este es el documento FUEC como tal (el PDF)
    protected $table = 'cte_planillacs';
    protected $fillable = ['id', 'contrato_id', 'razon_social', 'nit', 'convenio', 'plantilla_id', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Planillac::leftJoin('cte_contratos', 'cte_contratos.id', '=', 'cte_planillacs.contrato_id')
            ->leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_planillacs.plantilla_id')
            ->select('cte_planillacs.id', 'cte_contratos.codigo AS contrato_codigo', 'cte_plantillas.titulo AS plantilla_titulo')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->contrato_codigo . ' > ' . $opcion->plantilla_titulo;
        }

        return $vec;
    }

    public function planillaconductors()
    {
        return $this->hasMany(Planillaconductor::class);
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }
}
