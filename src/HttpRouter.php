<?php

namespace AppKit\Http\Server\Router;

use AppKit\Http\Message\HttpError;

use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

class HttpRouter {
    private $dispatcher;

    function __construct($setupRoutesCallback) {
        $this -> dispatcher = simpleDispatcher(
            $setupRoutesCallback,
            [ 'routeCollector' => RouteCollector::class ]
        );
    }

    public function matchRequest($request) {
        $method = $request -> getMethod();
        $path = $request -> getUri() -> getPath();

        $routeInfo = $this -> dispatcher -> dispatch($method, $path);
        switch($routeInfo[0]) {
            case Dispatcher::FOUND:
                [, [$handler, $extraParameters], $pathParameters] = $routeInfo;
                return [$handler, $pathParameters, $extraParameters];

            case Dispatcher::NOT_FOUND:
                throw new HttpError(404);

            case Dispatcher::METHOD_NOT_ALLOWED:
                [, $allowedMethods] = $routeInfo;
                throw new HttpError(
                    405,
                    headers: [ 'Allow' => $allowedMethods ]
                );
        }
    }
}
