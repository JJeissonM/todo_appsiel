<?php

namespace App\Core\Services;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Short database-only operations. Never retry part of an enclosing transaction.
 * Laravel 5.2 does not provide transaction retries and can retain its nesting
 * counter after InnoDB has already rolled back a deadlock victim.
 */
class DocumentSequenceTransaction
{
    public static function run(Closure $operation)
    {
        $connection = DB::connection();
        if ($connection->transactionLevel() > 0) {
            return $operation($connection);
        }

        $name = $connection->getName();
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $connection = DB::connection($name);
            $connection->beginTransaction();
            try {
                $result = $operation($connection);
                $connection->commit();
                return $result;
            } catch (\Throwable $error) {
                if ($connection->getPdo()->inTransaction()) {
                    $connection->rollBack();
                } else {
                    // This is our outermost transaction. Discard the stale
                    // Laravel nesting counter when the engine rolled it back.
                    DB::purge($name);
                }

                if ($attempt === 3 || !static::isLockConflict($error)) {
                    throw $error;
                }
                usleep($attempt * 20000);
            }
        }
    }

    private static function isLockConflict(\Throwable $error)
    {
        do {
            if ($error instanceof \PDOException && isset($error->errorInfo[1]) &&
                in_array((int)$error->errorInfo[1], array(1205, 1213), true)) {
                return true;
            }
            $error = $error->getPrevious();
        } while ($error !== null);

        return false;
    }
}
