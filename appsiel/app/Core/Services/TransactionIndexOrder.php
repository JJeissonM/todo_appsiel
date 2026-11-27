<?php

namespace App\Core\Services;

class TransactionIndexOrder
{
    public static function sql($table)
    {
        if (!in_array($table, [
            'vtas_doc_encabezados', 'vtas_pos_doc_encabezados',
            'cxc_doc_encabezados', 'cxp_doc_encabezados', 'teso_doc_encabezados',
        ], true)) {
            throw new \InvalidArgumentException('Tabla no habilitada para el ordenamiento de transacciones.');
        }

        $order = config('configuracion.orden_transacciones_index', 'consecutivo');
        if ($order === 'created_at') {
            return $table.'.created_at DESC, '.$table.'.id DESC';
        }

        if ($order === 'fecha') {
            return $table.'.fecha DESC, '.$table.'.created_at DESC, '.$table.'.id DESC';
        }

        return $table.'.consecutivo DESC, '.$table.'.id DESC';
    }
}
