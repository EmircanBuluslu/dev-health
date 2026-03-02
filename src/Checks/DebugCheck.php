<?php

namespace DevHealth\LaravelHealth\Checks;

use DevHealth\LaravelHealth\Contracts\CheckInterface;
use DevHealth\LaravelHealth\ValueObjects\Result;

class DebugCheck implements CheckInterface
{
    public function run(): Result
    {
        $isDebug = config('app.debug');
        $env = config('app.env');
        $envFile = base_path('.env');

        if ($env === 'production' && $isDebug) {
            return Result::fail(
                message: 'Production ortamında APP_DEBUG=true olarak ayarlanmış!',
                file: $envFile,
                line: $this->findLineInEnv('APP_DEBUG'),
                suggestion: '.env dosyasında APP_DEBUG=false olarak değiştirin'
            );
        }

        if ($env === 'production' && !$isDebug) {
            return Result::ok('Debug modu production ortamında kapalı');
        }

        if ($env !== 'production' && $isDebug) {
            return Result::ok('Debug modu development ortamında açık (normal)');
        }

        return Result::warning(
            message: 'Debug modu development ortamında kapalı',
            suggestion: 'Development ortamında APP_DEBUG=true kullanmanız önerilir'
        );
    }

    public function getName(): string
    {
        return 'Debug Modu Kontrolü';
    }

    public function getDescription(): string
    {
        return 'Production ortamında debug modunun kapalı olduğunu kontrol eder';
    }

    private function findLineInEnv(string $key): ?int
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            return null;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES);
        
        foreach ($lines as $index => $line) {
            if (str_starts_with(trim($line), $key . '=')) {
                return $index + 1;
            }
        }

        return null;
    }
}
