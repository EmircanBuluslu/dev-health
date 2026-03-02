<?php

namespace DevHealth\LaravelHealth\Checks;

use DevHealth\LaravelHealth\Contracts\CheckInterface;
use DevHealth\LaravelHealth\ValueObjects\Result;

class EnvSyncCheck implements CheckInterface
{
    public function run(): array
    {
        $results = [];
        $envFile = base_path('.env');
        $exampleFile = base_path('.env.example');

        if (!file_exists($envFile)) {
            return [Result::fail(
                message: '.env dosyası bulunamadı',
                file: $envFile,
                suggestion: '.env.example dosyasını kopyalayarak .env oluşturun'
            )];
        }

        if (!file_exists($exampleFile)) {
            return [Result::warning(
                message: '.env.example dosyası bulunamadı',
                file: $exampleFile,
                suggestion: 'Takım üyeleri için .env.example dosyası oluşturun'
            )];
        }

        $envKeys = $this->parseEnvFile($envFile);
        $exampleKeys = $this->parseEnvFile($exampleFile);

        // .env'de olup .env.example'da olmayan anahtarlar
        $missingInExample = array_diff($envKeys, $exampleKeys);
        foreach ($missingInExample as $key) {
            $results[] = Result::warning(
                message: "'{$key}' anahtarı .env.example dosyasında eksik",
                file: $exampleFile,
                suggestion: "Bu anahtarı .env.example dosyasına ekleyin",
                metadata: ['key' => $key]
            );
        }

        // .env.example'da olup .env'de olmayan anahtarlar
        $missingInEnv = array_diff($exampleKeys, $envKeys);
        foreach ($missingInEnv as $key) {
            $results[] = Result::fail(
                message: "'{$key}' anahtarı .env dosyasında eksik",
                file: $envFile,
                line: $this->findLineInEnv($key, $exampleFile),
                suggestion: "Bu anahtarı .env dosyasına ekleyin",
                metadata: ['key' => $key]
            );
        }

        if (empty($results)) {
            $results[] = Result::ok('.env ve .env.example dosyaları senkronize');
        }

        return $results;
    }

    public function getName(): string
    {
        return 'Environment Senkronizasyon Kontrolü';
    }

    public function getDescription(): string
    {
        return '.env ve .env.example dosyalarının senkronize olduğunu kontrol eder';
    }

    private function parseEnvFile(string $file): array
    {
        $keys = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key] = explode('=', $line, 2);
                $keys[] = trim($key);
            }
        }

        return $keys;
    }

    private function findLineInEnv(string $key, string $file): ?int
    {
        if (!file_exists($file)) {
            return null;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES);
        
        foreach ($lines as $index => $line) {
            if (str_starts_with(trim($line), $key . '=')) {
                return $index + 1;
            }
        }

        return null;
    }
}
