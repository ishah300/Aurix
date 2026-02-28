<?php

declare(strict_types=1);

namespace Aurix\Support\Concerns;

trait InteractsWithAurixValidation
{
    protected function aurixValidationTable(string $table): string
    {
        $connection = (string) config('aurix.database.connection', '');

        return $connection !== '' ? $connection . '.' . $table : $table;
    }

    protected function uniqueRule(string $table, string $column, ?int $ignoreId = null, ?string $idColumn = null): string
    {
        $rule = 'unique:' . $this->aurixValidationTable($table) . ',' . $column;

        if ($ignoreId !== null) {
            $rule .= ',' . $ignoreId;
            if ($idColumn !== null) {
                $rule .= ',' . $idColumn;
            }
        }

        return $rule;
    }

    protected function existsRule(string $table, string $column): string
    {
        return 'exists:' . $this->aurixValidationTable($table) . ',' . $column;
    }
}
