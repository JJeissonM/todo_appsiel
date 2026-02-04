<?php

namespace App\Contabilidad\Services\Concerns;

trait AppliesContabMovementDefaults
{
    protected function apply_contab_movement_defaults(array $data): array
    {
        $defaults = [
            'inv_producto_id' => 0,
            'impuesto_id' => null,
            'cantidad' => 0,
            'tasa_impuesto' => 0,
            'base_impuesto' => 0,
            'valor_impuesto' => 0,
            'fecha_vencimiento' => '0000-00-00',
            'inv_bodega_id' => 0,
            'teso_caja_id' => 0,
            'teso_cuenta_bancaria_id' => 0,
            'codigo_referencia_tercero' => 0,
            'documento_soporte' => 0,
            'estado' => 'Activo',
        ];

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
