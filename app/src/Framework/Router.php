<?php
declare(strict_types=1);

namespace App\Framework;

use App\Controllers\ErrorController;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

final class Router
{
    /** @var array<int, array{0:string,1:string,2:array|callable,3:array<int, string|callable>}> */
    private array $routeDefinitions = [];

    public function __construct(private Container $container)
    {
    }

    public function add(string $httpMethod, string $path, array|callable $handler, array $middleware = []): self
    {
        $this->routeDefinitions[] = [strtoupper($httpMethod), $path, $handler, $middleware];
        return $this;
    }

    public function get(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    private function buildDispatcher(): Dispatcher
    {
        $defs = $this->routeDefinitions;

        return simpleDispatcher(function (RouteCollector $r) use ($defs) {
            foreach ($defs as [$method, $path, $handler, $middleware]) {
                $r->addRoute($method, $path, [
                    'handler' => $handler,
                    'mw'      => $middleware,
                ]);
            }
        });
    }

    private function executeMiddleware(array $middleware, array $vars): bool
    {
        foreach ($middleware as $mw) {
            if (is_string($mw)) {
                switch ($mw) {
                    case 'auth':
                        if (!Auth::check()) {
                            header('Location: /login');
                            exit;
                        }
                        break;
                    case 'teacher':
                        Auth::requireRole('teacher');
                        break;
                    case 'student':
                        Auth::requireRole('student');
                        break;
                    case 'guest':
                        if (Auth::check()) {
                            $role = Auth::role();
                            if ($role === 'teacher') {
                                header('Location: /teacher/dashboard');
                                exit;
                            } elseif ($role === 'student') {
                                header('Location: /student/dashboard');
                                exit;
                            }
                            // Stale or invalid session data
                            Auth::logout();
                        }
                        break;
                    default:
                        $this->renderError(500, "Unknown middleware: {$mw}");
                        return false;
                }
            } elseif (is_callable($mw)) {
                if ($mw($vars) === false) return false;
            }
        }
        return true;
    }

    public function dispatch(?string $httpMethod = null, ?string $uri = null): void
    {
        $httpMethod ??= strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri ??= (string)($_SERVER['REQUEST_URI'] ?? '/');

        $path = strtok($uri, '?');
        if ($path === false || $path === '') $path = '/';

        $dispatcher = $this->buildDispatcher();
        $routeInfo = $dispatcher->dispatch($httpMethod, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->renderError(404, 'Not Found');
                return;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->renderError(405, 'Method Not Allowed');
                return;

            case Dispatcher::FOUND:
                $payload = $routeInfo[1];
                $vars = $routeInfo[2];

                if (!$this->executeMiddleware($payload['mw'], $vars)) exit;

                $handler = $payload['handler'];

                if (is_callable($handler)) {
                    $handler(...array_values($vars));
                    return;
                }

                [$class, $method] = $handler;
                $controller = $this->container->get($class);

                if (!method_exists($controller, $method)) {
                    $this->renderError(500, 'Route handler not found');
                    return;
                }

                $controller->{$method}(...array_values($vars));
                return;
        }
    }

    private function renderError(int $statusCode, string $message): void
    {
        $errorController = $this->container->get(ErrorController::class);
        if (method_exists($errorController, 'notFound') && $statusCode === 404) {
            $errorController->notFound();
        } elseif (method_exists($errorController, 'forbidden') && $statusCode === 403) {
            $errorController->forbidden();
        } else {
            http_response_code($statusCode);
            echo $message;
        }
    }
}
