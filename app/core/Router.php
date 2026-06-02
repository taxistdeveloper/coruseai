<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $uri, array $action, array $middleware = []): void
    {
        $this->add('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, array $action, array $middleware = []): void
    {
        $this->add('POST', $uri, $action, $middleware);
    }

    private function add(string $method, string $uri, array $action, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'uri'        => $this->normalizeUri($uri),
            'controller' => $action[0],
            'methodName' => $action[1],
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = $this->normalizeUri(parse_url($uri, PHP_URL_PATH) ?: '/');
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            $params = [];
            if ($route['method'] !== $method) {
                continue;
            }
            if (!$this->match($route['uri'], $uri, $params)) {
                continue;
            }

            foreach ($route['middleware'] as $mw) {
                if (is_string($mw) && str_contains($mw, '::')) {
                    [$class, $method] = explode('::', $mw, 2);
                    $class::$method();
                } else {
                    $mw::handle();
                }
            }

            $controller = new $route['controller']();
            call_user_func_array([$controller, $route['methodName']], $params);
            return;
        }

        http_response_code(404);
        view('errors.404');
    }

    private function normalizeUri(string $uri): string
    {
        $uri = '/' . trim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    private function match(string $pattern, string $uri, array &$params): bool
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (!preg_match($regex, $uri, $matches)) {
            return false;
        }
        array_shift($matches);
        $params = array_map('urldecode', $matches);
        return true;
    }
}
