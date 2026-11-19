<?php
namespace App\Contabilidad\Services\Retenciones;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class FiltrosCertificados
{
    public static function normalizar(array $entrada)
    {
        $f = array_merge(['periodo'=>'anual', 'anio'=>date('Y'), 'bimestre'=>1,
            'tipo_impuesto'=>'FUENTE', 'compras'=>'retenidas', 'tercero_id'=>'', 'grupo_id'=>'',
            'codigo_municipio'=>'', 'municipio'=>'', 'confirmar_municipio'=>0,
            'ciudad_consignacion'=>'', 'firma_autorizada_id'=>'',
            'norma_ica'=>'', 'firma_fuente'=>'manual', 'mostrar_detalle_operaciones'=>'Si'], $entrada);
        foreach (['confirmar_municipio'=>0, 'tercero_id'=>'', 'grupo_id'=>'', 'mostrar_detalle_operaciones'=>'Si'] as $campo=>$defecto) {
            if ($f[$campo] === null || $f[$campo] === '') { $f[$campo]=$defecto; }
        }
        $v = Validator::make($f, [
            'periodo'=>'required|in:anual,bimestral,fechas', 'anio'=>'required|integer|between:2000,2100',
            'bimestre'=>'required|integer|between:1,6', 'tipo_impuesto'=>'required|in:FUENTE,ICA',
            'compras'=>'required|in:todas,retenidas', 'tercero_id'=>'integer|min:1', 'grupo_id'=>'integer|min:1',
            'ciudad_consignacion'=>'required|max:150', 'firma_autorizada_id'=>'required|integer|min:1',
            'firma_fuente'=>'required|in:manual,computador',
            'mostrar_detalle_operaciones'=>'required|in:Si,No',
            'codigo_municipio'=>'required_if:tipo_impuesto,ICA|regex:/^[0-9]{5}$/',
            'municipio'=>'required_if:tipo_impuesto,ICA|max:150', 'norma_ica'=>'required_if:tipo_impuesto,ICA|max:250',
            'confirmar_municipio'=>'in:0,1',
            'fecha_desde'=>'required_if:periodo,fechas|date_format:Y-m-d',
            'fecha_hasta'=>'required_if:periodo,fechas|date_format:Y-m-d'
        ]);
        if ($v->fails()) { throw new \InvalidArgumentException(implode(' ', $v->errors()->all())); }
        if ($f['tercero_id'] && $f['grupo_id']) { throw new \InvalidArgumentException('Seleccione un tercero, un grupo o todos.'); }
        if ($f['periodo'] !== 'fechas') {
            $mes = $f['periodo'] === 'anual' ? 1 : ((int)$f['bimestre'] - 1) * 2 + 1;
            $inicio = Carbon::createFromDate((int)$f['anio'], $mes, 1)->startOfDay();
            $f['fecha_desde'] = $inicio->format('Y-m-d');
            $f['fecha_hasta'] = $f['periodo'] === 'anual' ? $inicio->copy()->endOfYear()->format('Y-m-d')
                : $inicio->copy()->addMonth()->endOfMonth()->format('Y-m-d');
        }
        if ($f['fecha_desde'] > $f['fecha_hasta']) { throw new \InvalidArgumentException('La fecha inicial no puede superar la final.'); }
        return $f;
    }
}
