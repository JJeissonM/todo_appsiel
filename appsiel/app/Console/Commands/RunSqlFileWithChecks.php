<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RunSqlFileWithChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appsiel:run-sql-file
                            {--file= : Ruta del archivo SQL (por defecto database/scripts/cambios_bd__appsiel_10.sql)}
                            {--dry-run : Solo muestra lo que se haria, sin ejecutar}
                            {--stop-on-error : Detiene la ejecucion al primer error}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta un archivo SQL con validaciones basicas (tablas, columnas, llaves unicas)';

    public function handle()
    {
        $path = $this->option('file') ?: 'database/scripts/cambios_bd__appsiel_10.sql';
        $fullPath = $this->resolvePath($path);

        if (!is_file($fullPath)) {
            $this->error("No se encontro el archivo: {$fullPath}");
            return 1;
        }

        $sql = file_get_contents($fullPath);
        $statements = $this->parseSqlStatements($sql);

        $dryRun = (bool) $this->option('dry-run');
        $stopOnError = (bool) $this->option('stop-on-error');

        $this->info("Archivo: {$fullPath}");
        $this->info('Total de sentencias: ' . count($statements));

        $executed = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($statements as $index => $stmt) {
            $sqlStmt = trim($stmt['sql']);
            if ($sqlStmt === '') {
                continue;
            }

            $label = 'Stmt #' . ($index + 1) . ' (linea ' . $stmt['line'] . ')';
            $type = $this->detectStatementType($sqlStmt);

            try {
                $result = $this->processStatement($sqlStmt, $type, $dryRun, $label);
                if ($result === 'skipped') {
                    $skipped++;
                } else {
                    $executed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->error("{$label} ERROR: " . $e->getMessage());
                if ($stopOnError) {
                    return 1;
                }
            }
        }

        $this->info("Ejecutadas: {$executed}");
        $this->info("Saltadas: {$skipped}");
        $this->info("Errores: {$failed}");

        return $failed > 0 ? 1 : 0;
    }

    protected function processStatement($sqlStmt, $type, $dryRun, $label)
    {
        if ($type === 'insert') {
            return $this->handleInsert($sqlStmt, $dryRun, $label);
        }

        if (in_array($type, ['update', 'delete', 'alter_table', 'drop_table', 'create_table'])) {
            $table = $this->extractTableName($sqlStmt, $type);
            if ($table && !$this->tableExists($table)) {
                $this->warn("{$label} TABLA NO EXISTE: {$table} (se omite)");
                return 'skipped';
            }

            if ($type === 'create_table' && $table && $this->tableExists($table)) {
                $this->warn("{$label} TABLA YA EXISTE: {$table} (se omite)");
                return 'skipped';
            }

            if ($type === 'drop_table' && $table && !$this->tableExists($table)) {
                $this->warn("{$label} TABLA NO EXISTE: {$table} (se omite)");
                return 'skipped';
            }

            if ($type === 'alter_table' && $table) {
                if ($this->shouldSkipAlter($sqlStmt, $table)) {
                    $this->warn("{$label} ALTER NO APLICA (se omite)");
                    return 'skipped';
                }
            }
        }

        if ($dryRun) {
            $this->line("{$label} DRY-RUN: {$type}");
            return 'executed';
        }

        DB::statement($sqlStmt);
        $this->line("{$label} OK: {$type}");

        return 'executed';
    }

    protected function handleInsert($sqlStmt, $dryRun, $label)
    {
        $parsed = $this->parseInsertStatement($sqlStmt);
        if (!$parsed) {
            if ($dryRun) {
                $this->line("{$label} DRY-RUN: insert (sin parsear)");
                return 'executed';
            }
            DB::statement($sqlStmt);
            $this->line("{$label} OK: insert (sin parsear)");
            return 'executed';
        }

        $table = $parsed['table'];
        if (!$this->tableExists($table)) {
            $this->warn("{$label} TABLA NO EXISTE: {$table} (se omite)");
            return 'skipped';
        }

        $columns = $parsed['columns'];
        $rows = $parsed['rows'];

        $pkColumns = $this->getPrimaryKeyColumns($table);
        $uniqueIndexes = $this->getUniqueIndexes($table);

        $toInsert = [];

        foreach ($rows as $rowValues) {
            $row = $this->buildAssocRow($columns, $rowValues);
            if ($this->shouldSkipPermissionInsertByName($table, $row)) {
                $this->warn("{$label} PERMISSION name ya existe (se omite)");
                continue;
            }
            if ($this->rowExistsByConstraints($table, $row, $pkColumns, $uniqueIndexes)) {
                $this->warn("{$label} REGISTRO EXISTE en {$table} (se omite)");
                continue;
            }
            $toInsert[] = $row;
        }

        if (empty($toInsert)) {
            return 'skipped';
        }

        if ($dryRun) {
            $this->line("{$label} DRY-RUN: insert {$table} (" . count($toInsert) . " filas)");
            return 'executed';
        }

        foreach ($toInsert as $row) {
            DB::table($table)->insert($row);
        }

        $this->line("{$label} OK: insert {$table} (" . count($toInsert) . " filas)");
        return 'executed';
    }

    protected function shouldSkipPermissionInsertByName($table, array $row)
    {
        if ($table !== 'permissions') {
            return false;
        }
        if (!array_key_exists('name', $row)) {
            return false;
        }
        if ($row['name'] === null || $row['name'] === '') {
            return false;
        }
        return DB::table($table)->where('name', $row['name'])->exists();
    }

    protected function parseSqlStatements($sql)
    {
        $statements = [];
        $current = '';
        $line = 1;
        $stmtLine = 1;
        $len = strlen($sql);
        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            $next = $i + 1 < $len ? $sql[$i + 1] : '';

            if ($ch === "\n") {
                $line++;
            }

            if (!$inSingle && !$inDouble && !$inBacktick) {
                if ($ch === '-' && $next === '-' && ($i === 0 || ctype_space($sql[$i - 1]))) {
                    while ($i < $len && $sql[$i] !== "\n") {
                        $i++;
                    }
                    $line++;
                    continue;
                }
                if ($ch === '/' && $next === '*') {
                    $i += 2;
                    while ($i < $len - 1 && !($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                        if ($sql[$i] === "\n") {
                            $line++;
                        }
                        $i++;
                    }
                    $i++;
                    continue;
                }
            }

            if ($ch === "'" && !$inDouble && !$inBacktick) {
                if ($inSingle) {
                    if ($next === "'") {
                        $current .= $ch . $next;
                        $i++;
                        continue;
                    }
                    if ($i > 0 && $sql[$i - 1] === '\\') {
                        $current .= $ch;
                        continue;
                    }
                    $inSingle = false;
                } else {
                    $inSingle = true;
                }
            } elseif ($ch === '"' && !$inSingle && !$inBacktick) {
                if ($inDouble) {
                    if ($i > 0 && $sql[$i - 1] === '\\') {
                        $current .= $ch;
                        continue;
                    }
                    $inDouble = false;
                } else {
                    $inDouble = true;
                }
            } elseif ($ch === '`' && !$inSingle && !$inDouble) {
                $inBacktick = !$inBacktick;
            }

            if ($ch === ';' && !$inSingle && !$inDouble && !$inBacktick) {
                $statements[] = [
                    'sql' => $current,
                    'line' => $stmtLine,
                ];
                $current = '';
                $stmtLine = $line;
                continue;
            }

            if ($current === '' && !ctype_space($ch)) {
                $stmtLine = $line;
            }

            $current .= $ch;
        }

        if (trim($current) !== '') {
            $statements[] = [
                'sql' => $current,
                'line' => $stmtLine,
            ];
        }

        return $statements;
    }

    protected function detectStatementType($sql)
    {
        $sql = ltrim($sql);
        $head = strtoupper(substr($sql, 0, 20));
        if (strpos($head, 'INSERT') === 0) {
            return 'insert';
        }
        if (strpos($head, 'UPDATE') === 0) {
            return 'update';
        }
        if (strpos($head, 'DELETE') === 0) {
            return 'delete';
        }
        if (strpos($head, 'CREATE TABLE') === 0) {
            return 'create_table';
        }
        if (strpos($head, 'ALTER TABLE') === 0) {
            return 'alter_table';
        }
        if (strpos($head, 'DROP TABLE') === 0) {
            return 'drop_table';
        }
        return 'other';
    }

    protected function extractTableName($sql, $type)
    {
        $patterns = [
            'update' => '/^UPDATE\\s+`?([^`\\s]+)`?/i',
            'delete' => '/^DELETE\\s+FROM\\s+`?([^`\\s]+)`?/i',
            'create_table' => '/^CREATE\\s+TABLE\\s+`?([^`\\s]+)`?/i',
            'alter_table' => '/^ALTER\\s+TABLE\\s+`?([^`\\s]+)`?/i',
            'drop_table' => '/^DROP\\s+TABLE\\s+`?([^`\\s]+)`?/i',
        ];
        if (!isset($patterns[$type])) {
            return null;
        }
        if (preg_match($patterns[$type], $sql, $m)) {
            return $m[1];
        }
        return null;
    }

    protected function parseInsertStatement($sql)
    {
        $sql = trim($sql);
        $regex = '/^INSERT\\s+INTO\\s+`?([^`\\s]+)`?\\s*\\((.*?)\\)\\s*VALUES\\s*(.*)$/is';
        if (!preg_match($regex, $sql, $m)) {
            return null;
        }

        $table = $m[1];
        $columns = $this->splitColumns($m[2]);
        $rows = $this->parseValuesGroups($m[3]);
        if (empty($columns) || empty($rows)) {
            return null;
        }

        return [
            'table' => $table,
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    protected function splitColumns($columnsStr)
    {
        $parts = [];
        $current = '';
        $inBacktick = false;
        $len = strlen($columnsStr);
        for ($i = 0; $i < $len; $i++) {
            $ch = $columnsStr[$i];
            if ($ch === '`') {
                $inBacktick = !$inBacktick;
            }
            if ($ch === ',' && !$inBacktick) {
                $parts[] = $this->cleanColumnName($current);
                $current = '';
                continue;
            }
            $current .= $ch;
        }
        if (trim($current) !== '') {
            $parts[] = $this->cleanColumnName($current);
        }
        return array_values(array_filter($parts));
    }

    protected function cleanColumnName($col)
    {
        $col = trim($col);
        $col = trim($col, "` \t\n\r\0\x0B");
        return $col;
    }

    protected function parseValuesGroups($valuesStr)
    {
        $rows = [];
        $current = '';
        $depth = 0;
        $inSingle = false;
        $inDouble = false;
        $len = strlen($valuesStr);

        for ($i = 0; $i < $len; $i++) {
            $ch = $valuesStr[$i];
            $next = $i + 1 < $len ? $valuesStr[$i + 1] : '';

            if ($ch === "'" && !$inDouble) {
                if ($inSingle) {
                    if ($next === "'") {
                        $current .= $ch . $next;
                        $i++;
                        continue;
                    }
                    if ($i > 0 && $valuesStr[$i - 1] === '\\') {
                        $current .= $ch;
                        continue;
                    }
                    $inSingle = false;
                } else {
                    $inSingle = true;
                }
            } elseif ($ch === '"' && !$inSingle) {
                if ($inDouble) {
                    if ($i > 0 && $valuesStr[$i - 1] === '\\') {
                        $current .= $ch;
                        continue;
                    }
                    $inDouble = false;
                } else {
                    $inDouble = true;
                }
            }

            if (!$inSingle && !$inDouble) {
                if ($ch === '(') {
                    if ($depth === 0) {
                        $current = '';
                    }
                    $depth++;
                    continue;
                }
                if ($ch === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $rows[] = $this->parseValueList($current);
                        $current = '';
                        continue;
                    }
                }
            }

            if ($depth > 0) {
                $current .= $ch;
            }
        }

        return $rows;
    }

    protected function parseValueList($valueList)
    {
        $values = [];
        $current = '';
        $inSingle = false;
        $inDouble = false;
        $len = strlen($valueList);

        for ($i = 0; $i < $len; $i++) {
            $ch = $valueList[$i];
            $next = $i + 1 < $len ? $valueList[$i + 1] : '';

            if ($ch === "'" && !$inDouble) {
                if ($inSingle) {
                    if ($next === "'") {
                        $current .= $ch . $next;
                        $i++;
                        continue;
                    }
                    if ($i > 0 && $valueList[$i - 1] === '\\') {
                        $current .= $ch;
                        continue;
                    }
                    $inSingle = false;
                } else {
                    $inSingle = true;
                }
            } elseif ($ch === '"' && !$inSingle) {
                if ($inDouble) {
                    if ($i > 0 && $valueList[$i - 1] === '\\') {
                        $current .= $ch;
                        continue;
                    }
                    $inDouble = false;
                } else {
                    $inDouble = true;
                }
            }

            if ($ch === ',' && !$inSingle && !$inDouble) {
                $values[] = $this->parseLiteralValue($current);
                $current = '';
                continue;
            }
            $current .= $ch;
        }

        if (trim($current) !== '') {
            $values[] = $this->parseLiteralValue($current);
        }

        return $values;
    }

    protected function parseLiteralValue($value)
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $upper = strtoupper($value);
        if ($upper === 'NULL') {
            return null;
        }
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }
        if ($value[0] === "'" && substr($value, -1) === "'") {
            $inner = substr($value, 1, -1);
            return stripcslashes($inner);
        }
        if (preg_match('/^(NOW\\(\\)|CURRENT_TIMESTAMP)$/i', $value)) {
            return DB::raw($value);
        }
        return $value;
    }

    protected function buildAssocRow(array $columns, array $values)
    {
        $row = [];
        foreach ($columns as $idx => $col) {
            $row[$col] = array_key_exists($idx, $values) ? $values[$idx] : null;
        }
        return $row;
    }

    protected function rowExistsByConstraints($table, array $row, array $pkColumns, array $uniqueIndexes)
    {
        if (!empty($pkColumns) && $this->rowHasColumns($row, $pkColumns)) {
            $query = DB::table($table);
            foreach ($pkColumns as $col) {
                $query->where($col, $row[$col]);
            }
            return $query->exists();
        }

        foreach ($uniqueIndexes as $indexColumns) {
            if ($this->rowHasColumns($row, $indexColumns)) {
                $query = DB::table($table);
                foreach ($indexColumns as $col) {
                    $query->where($col, $row[$col]);
                }
                if ($query->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function rowHasColumns(array $row, array $columns)
    {
        foreach ($columns as $col) {
            if (!array_key_exists($col, $row)) {
                return false;
            }
        }
        return true;
    }

    protected function shouldSkipAlter($sqlStmt, $table)
    {
        if (preg_match('/DROP\\s+FOREIGN\\s+KEY\\s+`?([^`\\s]+)`?/i', $sqlStmt, $m)) {
            $fk = $m[1];
            if (!$this->foreignKeyExists($table, $fk)) {
                return true;
            }
        }

        if (preg_match('/ADD\\s+(?:COLUMN\\s+)?(?!UNIQUE|INDEX|KEY|CONSTRAINT|FOREIGN)\\s*`?([a-zA-Z0-9_]+)`?/i', $sqlStmt, $m)) {
            $col = $m[1];
            if ($this->columnExists($table, $col)) {
                return true;
            }
        }

        if (preg_match('/CHANGE\\s+`?([a-zA-Z0-9_]+)`?/i', $sqlStmt, $m)) {
            $col = $m[1];
            if (!$this->columnExists($table, $col)) {
                return true;
            }
        }

        if (preg_match('/MODIFY\\s+`?([a-zA-Z0-9_]+)`?/i', $sqlStmt, $m)) {
            $col = $m[1];
            if (!$this->columnExists($table, $col)) {
                return true;
            }
        }

        if (preg_match('/ADD\\s+UNIQUE\\s+KEY\\s+`?([^`\\s]+)`?/i', $sqlStmt, $m)) {
            $index = $m[1];
            if ($this->indexExists($table, $index)) {
                return true;
            }
        }

        if (preg_match('/ADD\\s+UNIQUE\\s+\\(`?([^`\\s]+)`?\\)/i', $sqlStmt, $m)) {
            $col = $m[1];
            $uniqueIndexes = $this->getUniqueIndexes($table);
            foreach ($uniqueIndexes as $cols) {
                if (count($cols) === 1 && $cols[0] === $col) {
                    return true;
                }
            }
        }

        if (preg_match('/ADD\\s+INDEX\\s+`?([^`\\s]+)`?/i', $sqlStmt, $m)) {
            $index = $m[1];
            if ($this->indexExists($table, $index)) {
                return true;
            }
        }

        return false;
    }

    protected function tableExists($table)
    {
        return Schema::hasTable($table);
    }

    protected function columnExists($table, $column)
    {
        return Schema::hasColumn($table, $column);
    }

    protected function foreignKeyExists($table, $fkName)
    {
        $db = DB::getDatabaseName();
        $row = DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $db)
            ->where('table_name', $table)
            ->where('constraint_name', $fkName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->first();
        return (bool) $row;
    }

    protected function indexExists($table, $indexName)
    {
        $db = DB::getDatabaseName();
        $row = DB::table('information_schema.statistics')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->first();
        return (bool) $row;
    }

    protected function getPrimaryKeyColumns($table)
    {
        $db = DB::getDatabaseName();
        $rows = DB::table('information_schema.key_column_usage')
            ->select('column_name')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('constraint_name', 'PRIMARY')
            ->orderBy('ordinal_position')
            ->get();
        $cols = [];
        foreach ($rows as $row) {
            $cols[] = $row->column_name;
        }
        return $cols;
    }

    protected function getUniqueIndexes($table)
    {
        $db = DB::getDatabaseName();
        $rows = DB::table('information_schema.statistics')
            ->select('index_name', 'column_name', 'seq_in_index')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('non_unique', 0)
            ->where('index_name', '!=', 'PRIMARY')
            ->orderBy('index_name')
            ->orderBy('seq_in_index')
            ->get();

        $indexes = [];
        foreach ($rows as $row) {
            $indexes[$row->index_name][] = $row->column_name;
        }
        return array_values($indexes);
    }

    protected function resolvePath($path)
    {
        if (preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }
        return base_path($path);
    }
}
