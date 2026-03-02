<?php

namespace DevHealth\LaravelHealth\Checks;

use DevHealth\LaravelHealth\Contracts\CheckInterface;
use DevHealth\LaravelHealth\ValueObjects\Result;
use Illuminate\Support\Facades\Route;

class RouteAuthCheck implements CheckInterface
{
    private array $publicRoutes = [
        'login',
        'register',
        'password',
        'sanctum/csrf-cookie',
        'api/health',
        'health',
        '_ignition',
    ];

    public function run(): array
    {
        $results = [];
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = implode('|', $route->methods());
            $middleware = $route->middleware();
            $action = $route->getActionName();

            // Public rotaları atla
            if ($this->isPublicRoute($uri)) {
                continue;
            }

            // Auth middleware kontrolü
            $hasAuth = $this->hasAuthMiddleware($middleware);

            if (!$hasAuth) {
                $results[] = Result::warning(
                    message: "Rota kimlik doğrulama middleware'i içermiyor: {$methods} {$uri}",
                    file: $this->getRouteFile($action),
                    suggestion: "Bu rotaya 'auth' veya 'sanctum' middleware'i ekleyin",
                    metadata: [
                        'uri' => $uri,
                        'methods' => $methods,
                        'action' => $action,
                        'middleware' => $middleware,
                    ]
                );
            }
        }

        if (empty($results)) {
            $results[] = Result::ok('Tüm rotalar uygun kimlik doğrulama middleware\'ine sahip');
        }

        return $results;
    }

    public function getName(): string
    {
        return 'Rota Kimlik Doğrulama Kontrolü';
    }

    public function getDescription(): string
    {
        return 'Rotaların uygun auth middleware\'ine sahip olduğunu kontrol eder';
    }

    private function isPublicRoute(string $uri): bool
    {
        foreach ($this->publicRoutes as $publicRoute) {
            if (str_contains($uri, $publicRoute)) {
                return true;
            }
        }

        return false;
    }

    private function hasAuthMiddleware(array $middleware): bool
    {
        $authMiddlewares = ['auth', 'auth:sanctum', 'auth:api', 'sanctum'];

        foreach ($middleware as $mw) {
            if (in_array($mw, $authMiddlewares)) {
                return true;
            }
        }

        return false;
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
}
