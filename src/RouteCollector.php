<?php

namespace AppKit\Http\Server\Router;

use FastRoute\RouteCollector as FastRouteRouteCollector;
use Throwable;

class RouteCollector extends FastRouteRouteCollector {
    public function addRoute($httpMethod, $route, $handler, $extraParameters = []) {
        try {
            // TODO: Stop injecting extraParameters into the handler when the new FastRoute is out
            parent::addRoute(
                $httpMethod,
                $route,
                [ $handler, $extraParameters ]
            );
        } catch(Throwable $e) {
            throw new HttpRouterException(
                $e -> getMessage(),
                previous: $e
            );
        }
    }

    public function any($route, $handler, $extraParameters = []) {
        $this -> addRoute('*', $route, $handler, $extraParameters);
    }

    public function get($route, $handler, $extraParameters = []) {
        $this -> addRoute('GET', $route, $handler, $extraParameters);
    }

    public function post($route, $handler, $extraParameters = []) {
        $this -> addRoute('POST', $route, $handler, $extraParameters);
    }

    public function put($route, $handler, $extraParameters = []) {
        $this -> addRoute('PUT', $route, $handler, $extraParameters);
    }

    public function delete($route, $handler, $extraParameters = []) {
        $this -> addRoute('DELETE', $route, $handler, $extraParameters);
    }

    public function patch($route, $handler, $extraParameters = []) {
        $this -> addRoute('PATCH', $route, $handler, $extraParameters);
    }

    public function head($route, $handler, $extraParameters = []) {
        $this -> addRoute('HEAD', $route, $handler, $extraParameters);
    }

    public function options($route, $handler, $extraParameters = []) {
        $this -> addRoute('OPTIONS', $route, $handler, $extraParameters);
    }
}
