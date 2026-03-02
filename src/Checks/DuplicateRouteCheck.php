<?php

namespace DevHealth\LaravelHealth\Checks;

use DevHealth\LaravelHealth\Contracts\CheckInterface;
use DevHealth\LaravelHealth\ValueObjects\Result;
use Illuminate\Support\Facades\Route;

class DuplicateRouteCheck implements CheckInterface
{
    public function run(): array
    {
        $results = [];
        $routes = Route::getRoutes();
        $routeMap = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            
            foreach ($methods as $method) {
                if ($method === 'HEAD') {
                    continue;
                }

                $key = $method . ':' . $uri;
                
                if (!isset($routeMap[$key])) {
                    $routeMap[$key] = [];
                }

                $routeMap[$key][] = [
                    'action' => $route->getActionName(),
                    'name' => $route->getName(),
                ];
            }
        }

        foreach ($routeMap as $key => $routes) {
            if (count($routes) > 1) {
                [$method, $uri] = explode(':', $key, 2);
                
                $actions = array_map(fn($r) => $r['action'], $routes);
                
                $results[] = Result::fail(
                    message: "Çakışan rota tespit edildi: {$method} {$uri}",
                    file: base_path('routes/web.php'),
                    suggestion: 'Aynı URI ve HTTP metoduna sahip birden fazla rota tanımlanmış. Birini kaldırın veya URI\'yi değiştirin.',
                    metadata: [
                        'method' => $method,
                        'uri' => $uri,
                        'controllers' => $actions,
                    ]
                );
            }
        }

        if (empty($results)) {
            $results[] = Result::ok('Çakışan rota bulunamadı');
        }

        return $results;
    }

    public function getName(): string
    {
        return 'Çakışan Rota Kontrolü';
    }

    public function getDescription(): string
    {
        return 'Aynı URI ve HTTP metoduna sahip çakışan rotaları tespit eder';
    }
}
