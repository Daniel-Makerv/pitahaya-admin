<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportSchema extends Command
{
    protected $signature = 'schema:export {--output=schema.md} {--counts : Incluir número de registros}';
    protected $description = 'Exporta tablas, columnas, índices y relaciones de MySQL a Markdown';

    public function handle(): int
    {
        $db = DB::getDatabaseName();
        $md = "# Esquema de `{$db}`\n\n_Generado: " . now()->toDateTimeString() . "_\n\n";

        $tables = DB::select(
            "SELECT TABLE_NAME, TABLE_COMMENT, ENGINE
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
             ORDER BY TABLE_NAME",
            [$db]
        );

        foreach ($tables as $t) {
            $name = $t->TABLE_NAME;
            $md .= "## {$name}\n\n";
            if ($t->TABLE_COMMENT) {
                $md .= "> {$t->TABLE_COMMENT}\n\n";
            }
            if ($this->option('counts')) {
                $count = DB::table($name)->count();
                $md .= "Registros: **{$count}**\n\n";
            }

            // Columnas
            $cols = DB::select(
                "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT,
                        COLUMN_KEY, EXTRA, COLUMN_COMMENT
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION",
                [$db, $name]
            );

            $md .= "| Columna | Tipo | Null | Default | Key | Extra | Comentario |\n";
            $md .= "|---|---|---|---|---|---|---|\n";
            foreach ($cols as $c) {
                $default = $c->COLUMN_DEFAULT === null ? '' : $c->COLUMN_DEFAULT;
                $md .= "| {$c->COLUMN_NAME} | {$c->COLUMN_TYPE} | {$c->IS_NULLABLE} | {$default} | {$c->COLUMN_KEY} | {$c->EXTRA} | {$c->COLUMN_COMMENT} |\n";
            }
            $md .= "\n";

            // Índices
            $indexes = DB::select(
                "SELECT INDEX_NAME, NON_UNIQUE,
                        GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols
                 FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 GROUP BY INDEX_NAME, NON_UNIQUE",
                [$db, $name]
            );
            if ($indexes) {
                $md .= "**Índices**\n\n";
                foreach ($indexes as $i) {
                    $tipo = $i->INDEX_NAME === 'PRIMARY' ? 'PRIMARY' : ($i->NON_UNIQUE ? 'INDEX' : 'UNIQUE');
                    $md .= "- `{$i->INDEX_NAME}` ({$tipo}): {$i->cols}\n";
                }
                $md .= "\n";
            }

            // Llaves foráneas
            $fks = DB::select(
                "SELECT k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME,
                        r.UPDATE_RULE, r.DELETE_RULE
                 FROM information_schema.KEY_COLUMN_USAGE k
                 JOIN information_schema.REFERENTIAL_CONSTRAINTS r
                   ON r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                  AND r.CONSTRAINT_SCHEMA = k.TABLE_SCHEMA
                 WHERE k.TABLE_SCHEMA = ? AND k.TABLE_NAME = ?
                   AND k.REFERENCED_TABLE_NAME IS NOT NULL",
                [$db, $name]
            );
            if ($fks) {
                $md .= "**Relaciones**\n\n";
                foreach ($fks as $f) {
                    $md .= "- `{$f->COLUMN_NAME}` → `{$f->REFERENCED_TABLE_NAME}.{$f->REFERENCED_COLUMN_NAME}` (ON DELETE {$f->DELETE_RULE}, ON UPDATE {$f->UPDATE_RULE})\n";
                }
                $md .= "\n";
            }
        }

        file_put_contents(base_path($this->option('output')), $md);
        $this->info('Esquema exportado: ' . count($tables) . ' tablas → ' . $this->option('output'));

        return self::SUCCESS;
    }
}
