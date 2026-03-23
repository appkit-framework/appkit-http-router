<?php

namespace AppKit\Http\Server\Router;

use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

class HttpRouter {
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    private $dispatcher;

    function __construct($setupRoutesCallback) {
        $this -> dispatcher = simpleDispatcher(
            $setupRoutesCallback,
            [ 'routeCollector' => RouteCollector::class ]
        );
    }

    public function matchRequest($request) {
        $routeInfo = $this -> dispatcher -> dispatch(
            $request -> getMethod(),
            $request -> getUrl()
        );

        switch($routeInfo[0]) {
            case Dispatcher::FOUND:
                [, [$handler, $extra], $params] = $routeInfo;
                return [self::FOUND, $handler, $params, $extra, null];

            case Dispatcher::NOT_FOUND:
                return [self::NOT_FOUND, null, null, null, null];

            case Dispatcher::METHOD_NOT_ALLOWED:
                [, $allow] = $routeInfo;
                return [self::METHOD_NOT_ALLOWED, null, null, null, $allow];
        }
    }
}
