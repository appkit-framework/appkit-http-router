<?php

namespace AppKit\Http\Server\Router;

use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

class HttpRouter {
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;
    public const REDIRECT = 3;

    public const TS_REDIRECT = 0;
    public const TS_REDIRECT_BOTH = 1;
    public const TS_STRICT = 2;
    public const TS_LOOSE = 3;

    private $tsMode;

    private $dispatcher;

    function __construct($setupRoutesCallback, $tsMode = self::TS_REDIRECT) {
        $this -> tsMode = $tsMode;

        $this -> dispatcher = simpleDispatcher(
            $setupRoutesCallback,
            [ 'routeCollector' => RouteCollector::class ]
        );
    }

    public function matchRequest($request) {
        $requestPath = $request -> getPath();
        $method = $request -> getMethod();

        $routeInfo = $this -> dispatcher -> dispatch($method, $requestPath);

        switch($routeInfo[0]) {
            case Dispatcher::FOUND:
                [, [$handler, $extra], $params] = $routeInfo;
                return [self::FOUND, $handler, $params, $extra];

            case Dispatcher::NOT_FOUND:
                if($this -> tsMode == self::TS_STRICT)
                    return [self::NOT_FOUND, null, null, null];
                return $this -> matchAlternative($method, $requestPath);

            case Dispatcher::METHOD_NOT_ALLOWED:
                [, $allow] = $routeInfo;
                return [self::METHOD_NOT_ALLOWED, $allow, null, null];
        }
    }

    private function matchAlternative($method, $requestPath) {
        $requestTs = str_ends_with($requestPath, '/');
        $alterPath = $requestTs ? rtrim($requestPath, '/') : $requestPath.'/';

        $routeInfo = $this -> dispatcher -> dispatch($method, $alterPath);

        if($routeInfo[0] == Dispatcher::NOT_FOUND)
            return [self::NOT_FOUND, null, null, null];

        switch($this -> tsMode) {
            case self::TS_REDIRECT:
                if($requestTs)
                    return [self::NOT_FOUND, null, null, null];
                return [self::REDIRECT, $alterPath, null, null];

            case self::TS_REDIRECT_BOTH:
                return [self::REDIRECT, $alterPath, null, null];

            case self::TS_LOOSE:
                if($routeInfo[0] == Dispatcher::FOUND) {
                    [, [$handler, $extra], $params] = $routeInfo;
                    return [self::FOUND, $handler, $params, $extra];
                } else {
                    [, $allow] = $routeInfo;
                    return [self::METHOD_NOT_ALLOWED, $allow, null, null];
                }

            default:
                throw new HttpRouterException('Invalid trailing slash mode');
        }
    }
}
