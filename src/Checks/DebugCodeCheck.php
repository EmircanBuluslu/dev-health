<?php

namespace DevHealth\LaravelHealth\Checks;

use DevHealth\LaravelHealth\Contracts\CheckInterface;
use DevHealth\LaravelHealth\ValueObjects\Result;

class DebugCodeCheck implements CheckInterface
{
    private array $patterns = [
        // JavaScript/Vue/React
        'console\.log' => 'console.log() kullanımı',
        'console\.debug' => 'console.debug() kullanımı',
        'console\.warn' => 'console.warn() kullanımı',
        'console\.error' => 'console.error() kullanımı',
        'console\.info' => 'console.info() kullanımı',
        'console\.table' => 'console.table() kullanımı',
        'debugger;' => 'debugger statement',
        '\balert\s*\(' => 'alert() kullanımı',
        
        // PHP Laravel
        '\bdd\s*\(' => 'dd() kullanımı',
        '\bdump\s*\(' => 'dump() kullanımı',
        '\bvar_dump\s*\(' => 'var_dump() kullanımı',
        '\bprint_r\s*\(' => 'print_r() kullanımı',
        '\bvar_export\s*\(' => 'var_export() kullanımı',
        '\bray\s*\(' => 'ray() kullanımı',
        '->dump\s*\(' => '->dump() kullanımı',
        '->dd\s*\(' => '->dd() kullanımı',
    ];

    private array $excludePaths = [
        'vendor/',
        'node_modules/',
        'storage/',
        'bootstrap/cache/',
        '.git/',
        'tests/',
        'database/factories/',
        'database/seeders/',
    ];

    public function run(): array
    {
        $results = [];
        $basePath = base_path();

        // Taranacak dosya uzantıları
        $extensions = ['php', 'js', 'vue', 'jsx', 'ts', 'tsx', 'blade.php'];
        
        foreach ($extensions as $extension) {
            $files = $this->findFiles($basePath, $extension);
            
            foreach ($files as $file) {
                if ($this->shouldExclude($file)) {
                    continue;
                }

                $this->scanFile($file, $results);
            }
        }

        if (empty($results)) {
            $results[] = Result::ok('Debug kodu bulunamadı');
        }

        return $results;
    }

    public function getName(): string
    {
        return 'Debug Kodu Kontrolü';
    }

    public function getDescription(): string
    {
        return 'Kodda unutulmuş console.log, dd(), dump() gibi debug kodlarını tespit eder';
    }

    private function findFiles(string $path, string $extension): array
    {
        $files = [];
        
        if ($extension === 'blade.php') {
            // Blade dosyaları için özel arama
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    $files[] = $file->getPathname();
                }
            }
        } else {
            // Diğer dosyalar için
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === $extension) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    private function shouldExclude(string $file): bool
    {
        foreach ($this->excludePaths as $excludePath) {
            if (str_contains($file, $excludePath)) {
                return true;
            }
        }

        return false;
    }

    private function scanFile(string $file, array &$results): void
    {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            // Yorum satırlarını atla
            if ($this->isComment($line)) {
                continue;
            }

            foreach ($this->patterns as $pattern => $description) {
                if (preg_match('/' . $pattern . '/i', $line)) {
                    $results[] = Result::warning(
                        message: "Debug kodu tespit edildi: {$description}",
                        file: str_replace(base_path() . '/', '', $file),
                        line: $lineNumber + 1,
                        suggestion: 'Production ortamına çıkmadan önce bu satırı kaldırın',
                        metadata: [
                            'code' => trim($line),
                            'type' => $description,
                        ]
                    );
                }
            }
        }
    }

    private function isComment(string $line): bool
    {
        $trimmed = trim($line);
        
        // PHP yorumları
        if (str_starts_with($trimmed, '//')) {
            return true;
        }
        
        if (str_starts_with($trimmed, '#')) {
            return true;
        }
        
        if (str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '*')) {
            return true;
        }

        // JavaScript yorumları
        if (str_starts_with($trimmed, '//')) {
            return true;
        }

        // HTML yorumları
        if (str_starts_with($trimmed, '<!--')) {
            return true;
        }

        return false;
    }
}
