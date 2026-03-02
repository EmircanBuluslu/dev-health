<?php

namespace DevHealth\LaravelHealth\Checks;

use DevHealth\LaravelHealth\Contracts\CheckInterface;
use DevHealth\LaravelHealth\ValueObjects\Result;
use Illuminate\Support\Facades\Route;

class UnusedRouteCheck implements CheckInterface
{
    private array $suspiciousPatterns = [
        'test' => 'Test rotası',
        'debug' => 'Debug rotası',
        'temp' => 'Geçici rota',
        'old' => 'Eski rota',
        'backup' => 'Backup rotası',
        'sample' => 'Örnek rota',
        'demo' => 'Demo rotası',
        'example' => 'Örnek rota',
    ];

    private array $deprecatedMethods = [
        'create' => 'Kullanılmayan create metodu',
        'edit' => 'Kullanılmayan edit metodu',
        'update' => 'Kullanılmayan update metodu',
        'destroy' => 'Kullanılmayan destroy metodu',
    ];

    public function run(): array
    {
        $results = [];
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $uri = $route->uri();
            $name = $route->getName();
            $action = $route->getActionName();

            // Test/Debug rotalarını kontrol et
            foreach ($this->suspiciousPatterns as $pattern => $description) {
                if (str_contains(strtolower($uri), $pattern) || 
                    ($name && str_contains(strtolower($name), $pattern))) {
                    
                    $results[] = Result::warning(
                        message: "Şüpheli rota tespit edildi: {$description}",
                        file: $this->getRouteFile($action),
                        suggestion: 'Bu rota gerçekten gerekli mi? Production\'da olmamalı.',
                        metadata: [
                            'uri' => $uri,
                            'name' => $name,
                            'action' => $action,
                            'pattern' => $pattern,
                        ]
                    );
                }
            }

            // Closure rotalarını kontrol et (named route değilse)
            if ($action === 'Closure' && !$name) {
                $results[] = Result::info(
                    message: "İsimsiz Closure rotası: {$uri}",
                    file: $this->getRouteFile($action),
                    suggestion: 'Closure rotalarına isim verin veya controller\'a taşıyın',
                    metadata: [
                        'uri' => $uri,
                        'methods' => implode('|', $route->methods()),
                    ]
                );
            }

            // Duplicate isimli rotaları kontrol et
            if ($name && $this->hasDuplicateName($routes, $name)) {
                $results[] = Result::warning(
                    message: "Duplicate rota ismi: {$name}",
                    file: $this->getRouteFile($action),
                    suggestion: 'Rota isimleri unique olmalı',
                    metadata: [
                        'name' => $name,
                        'uri' => $uri,
                    ]
                );
            }
        }

        // Controller metodlarını kontrol et
        $this->checkUnusedControllerMethods($results);

        if (empty($results)) {
            $results[] = Result::ok('Şüpheli rota bulunamadı');
        }

        return $results;
    }

    public function getName(): string
    {
        return 'Kullanılmayan Rota Kontrolü';
    }

    public function getDescription(): string
    {
        return 'Test, debug gibi şüpheli rotaları ve kullanılmayan controller metodlarını tespit eder';
    }

    private function getRouteFile(string $action): ?string
    {
        if ($action === 'Closure') {
            return base_path('routes/web.php');
        }

        if (str_contains($action, '@')) {
            [$controller] = explode('@', $action);
            $path = str_replace('\\', '/', $controller);
            $path = str_replace('App/', 'app/', $path);
            return base_path($path . '.php');
        }

        return null;
    }

    private function hasDuplicateName($routes, string $name): bool
    {
        $count = 0;
        foreach ($routes as $route) {
            if ($route->getName() === $name) {
                $count++;
                if ($count > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    private function checkUnusedControllerMethods(array &$results): void
    {
        $routes = Route::getRoutes();
        $usedMethods = [];

        // Kullanılan metodları topla
        foreach ($routes as $route) {
            $action = $route->getActionName();
            if ($action !== 'Closure' && str_contains($action, '@')) {
                [$controller, $method] = explode('@', $action);
                $usedMethods[$controller][] = $method;
            }
        }

        // Controller dosyalarını tara
        $controllers = $this->findControllers();

        foreach ($controllers as $controllerFile) {
            $this->scanControllerForUnusedMethods($controllerFile, $usedMethods, $results);
        }
    }

    private function findControllers(): array
    {
        $controllers = [];
        $paths = [
            app_path('Http/Controllers'),
            base_path('Modules/*/app/Http/Controllers'),
        ];

        foreach ($paths as $path) {
            $files = glob($path . '/**/*Controller.php', GLOB_BRACE);
            if ($files) {
                $controllers = array_merge($controllers, $files);
            }
        }

        return $controllers;
    }

    private function scanControllerForUnusedMethods(string $file, array $usedMethods, array &$results): void
    {
        if (!file_exists($file)) {
            return;
        }

        $content = file_get_contents($file);
        $className = $this->extractClassName($file, $content);

        if (!$className) {
            return;
        }

        // Public metodları bul
        preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $matches);
        $publicMethods = $matches[1] ?? [];

        // Magic metodları ve constructor'ı çıkar
        $ignoreMethods = ['__construct', '__invoke', '__call', '__get', '__set', 'middleware', 'authorize'];
        $publicMethods = array_diff($publicMethods, $ignoreMethods);

        // Kullanılmayan metodları bul
        $usedInThisController = $usedMethods[$className] ?? [];

        foreach ($publicMethods as $method) {
            if (!in_array($method, $usedInThisController)) {
                // Sadece CRUD metodlarını raporla (çok fazla false positive olmasın)
                if (in_array($method, ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])) {
                    $lineNumber = $this->findMethodLine($content, $method);
                    
                    $results[] = Result::info(
                        message: "Kullanılmayan controller metodu: {$method}()",
                        file: str_replace(base_path() . '/', '', $file),
                        line: $lineNumber,
                        suggestion: 'Bu metod hiçbir rotada kullanılmıyor. Silebilir veya rota ekleyebilirsiniz.',
                        metadata: [
                            'controller' => $className,
                            'method' => $method,
                        ]
                    );
                }
            }
        }
    }

    private function extractClassName(string $file, string $content): ?string
    {
        // Namespace'i bul
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];
            
            // Class adını bul
            if (preg_match('/class\s+(\w+)/', $content, $matches)) {
                $className = $matches[1];
                return $namespace . '\\' . $className;
            }
        }

        return null;
    }

    private function findMethodLine(string $content, string $method): ?int
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $index => $line) {
            if (preg_match('/public\s+function\s+' . preg_quote($method, '/') . '\s*\(/', $line)) {
                return $index + 1;
            }
        }

        return null;
    }
}
