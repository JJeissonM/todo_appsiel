<?php

namespace App\Core\Services;

use Illuminate\Database\QueryException;

class DocumentSequenceService
{
    public static function reserve($empresaId, $documentTypeId)
    {
        if (filter_var($empresaId, FILTER_VALIDATE_INT) === false || (int)$empresaId <= 0 ||
            filter_var($documentTypeId, FILTER_VALIDATE_INT) === false || (int)$documentTypeId <= 0) {
            throw new \InvalidArgumentException('La reserva de consecutivo requiere empresa y tipo de documento válidos.');
        }

        return DocumentSequenceTransaction::run(function ($connection) use ($empresaId, $documentTypeId) {
            $query = $connection->table('core_consecutivos_documentos')
                ->where('core_empresa_id', (int)$empresaId)
                ->where('core_documento_app_id', (int)$documentTypeId);
            // Avoid acquiring a missing-key gap lock before the parent lock
            // on first creation (also supports tenants using REPEATABLE READ).
            $rows = (clone $query)->exists() ? (clone $query)->lockForUpdate()->get() : array();
            if (count($rows) > 1) {
                throw new \RuntimeException('Hay varios contadores para la misma empresa y tipo de documento. Deben conciliarse antes de reservar.');
            }

            if (count($rows) === 0) {
                // The parent row also serializes first creation on installations
                // awaiting the counter unique index. Existing counters do not
                // acquire this additional lock.
                $parent = $connection->table('core_tipos_docs_apps')
                    ->where('id', (int)$documentTypeId)->lockForUpdate()->first();
                if ($parent === null) {
                    throw new \InvalidArgumentException('El tipo de documento de la reserva no existe.');
                }
                $rows = (clone $query)->lockForUpdate()->get();
                if (count($rows) === 0) {
                    try {
                        $now = date('Y-m-d H:i:s');
                        $connection->table('core_consecutivos_documentos')->insert(array(
                            'core_empresa_id' => (int)$empresaId,
                            'core_documento_app_id' => (int)$documentTypeId,
                            'consecutivo_actual' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ));
                    } catch (QueryException $error) {
                        // A writer using the unique key may have created it.
                        // Do not swallow foreign-key, timeout or other errors.
                        $driverCode = isset($error->errorInfo[1]) ? (int)$error->errorInfo[1] : 0;
                        $sqliteUnique = $driverCode === 19 &&
                            strpos($error->getMessage(), 'UNIQUE constraint failed: core_consecutivos_documentos.') !== false;
                        if ($driverCode !== 1062 && !$sqliteUnique) {
                            throw $error;
                        }
                    }
                    $rows = (clone $query)->lockForUpdate()->get();
                }
            }

            if (count($rows) !== 1) {
                throw new \RuntimeException('No se pudo identificar un único contador para reservar el documento.');
            }
            $row = $rows[0];
            $number = (int)$row->consecutivo_actual + 1;
            if ($number <= 0 || $number > 2147483647) {
                throw new \OverflowException('El consecutivo excede el rango admitido por los documentos.');
            }
            $connection->table('core_consecutivos_documentos')->where('id', $row->id)->update(array(
                'consecutivo_actual' => $number,
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            return $number;
        });
    }
}
