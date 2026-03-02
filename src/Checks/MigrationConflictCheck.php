<?php

namespace DevHealth\LaravelHealth\Checks;

use DevHealth\LaravelHealth\Contracts\CheckInterface;
use DevHealth\LaravelHealth\ValueObjects\Result;

class MigrationConflictCheck implements CheckInterface
{
    public function run(): array
    {
        $results = [];
        $migrationPath = database_path('migrations');

        if (!is_dir($migrationPath)) {
            return [Result::warning(
                message: 'Migration klasörü bulunamadı',
                file: $migrationPath
            )];
        }

        $migrations = glob($migrationPath . '/*.php');
        $tableColumns = [];

        foreach ($migrations as $migration) {
            $content = file_get_contents($migration);
            $tableName = $this->extractTableName($content);

            if (!$tableName) {
                continue;
            }

            $columns = $this->extractColumns($content);

            foreach ($columns as $column) {
                if (!isset($tableColumns[$tableName])) {
                    $tableColumns[$tableName] = [];
                }

                if (!isset($tableColumns[$tableName][$column])) {
                    $tableColumns[$tableName][$column] = [];
                }

                $tableColumns[$tableName][$column][] = [
                    'file' => $migration,
                    'line' => $this->findColumnLine($content, $column),
                ];
            }
        }

        // Çakışmaları kontrol et
        foreach ($tableColumns as $table => $columns) {
            foreach ($columns as $column => $occurrences) {
                if (count($occurrences) > 1) {
                    $files = array_map(fn($o) => basename($o['file']), $occurrences);
                    
                    $results[] = Result::fail(
                        message: "'{$table}' tablosunda '{$column}' kolonu birden fazla migration'da tanımlanmış",
                        file: $occurrences[0]['file'],
                        line: $occurrences[0]['line'],
                        suggestion: "Çakışan kolon tanımlarını kaldırın veya migration'ları birleştirin",
                        metadata: [
                            'table' => $table,
                            'column' => $column,
                            'files' => $files,
                        ]
                    );
                }
            }
        }

        if (empty($results)) {
            $results[] = Result::ok('Migration çakışması bulunamadı');
        }

        return $results;
    }

    public function getName(): string
    {
        return 'Migration Çakışma Kontrolü';
    }

    public function getDescription(): string
    {
        return 'Migration dosyalarında çakışan kolon tanımlarını tespit eder';
    }

    private function extractTableName(string $content): ?string
    {
        // Schema::create('table_name', ...)
        if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            return $matches[1];
        }

        // Schema::table('table_name', ...)
        if (preg_match("/Schema::table\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractColumns(string $content): array
    {
        $columns = [];
        
        // $table->string('column_name')
        if (preg_match_all("/\\\$table->\w+\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            $columns = array_merge($columns, $matches[1]);
        }

        return array_unique($columns);
    }

    private function findColumnLine(string $content, string $column): ?int
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $index => $line) {
            if (preg_match("/\\\$table->\w+\s*\(\s*['\"]" . preg_quote($column, '/') . "['\"]/", $line)) {
                return $index + 1;
            }
        }

        return null;
    }
}
