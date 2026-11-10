<?php

namespace App\Core\Services;

class TransactionIndexOrder
{
    public static function sql($table)
    {
        if (!in_array($table, ['vtas_doc_encabezados', 'vtas_pos_doc_encabezados'], true)) {
            throw new \InvalidArgumentException('Tabla no habilitada para el ordenamiento de transacciones.');
        }

        if (config('configuracion.orden_transacciones_index', 'consecutivo') === 'fecha') {
            return $table.'.fecha DESC, '.$table.'.created_at DESC, '.$table.'.id DESC';
        }

        return $table.'.consecutivo DESC, '.$table.'.id DESC';
    }
}
