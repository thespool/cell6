<?php

namespace Core;

/**
 * Class Router
 * @package Core
 */
class Router {

    protected $container = null;

    protected $routes = array();
    protected $currentRouteName = null;
    protected $currentRoute = null;
    protected $currentRouteResult = null;
    protected $request = null;

    /**
     * Router constructor.
     * @param Container $container
     * @param Request $request
     */
    function __construct(Container $container, Request $request) {
        $this->container = $container;
        $this->request = $request;
    }

    /**
     * @param $name
     * @param $uri
     * @param $class
     * @param $method
     * @param array $paramsConstraints
     */
    public function get($name, $uri, $class, $method, array $paramsConstraints = array()) {
        $this->routes[$name] = $this->container->make("\Core\Route", array('params' => array('uri' => $uri, 'class' => $class, 'method' => $method, 'requestMethod' => 'get'), 'paramsConstraints' => $paramsConstraints));
    }

    /**
     * @param $name
     * @param $uri
     * @param $class
     * @param $method
     * @param array $paramsConstraints
     */
    public function post($name, $uri, $class, $method, array $paramsConstraints = array()) {
        $this->routes[$name] = $this->container->make("\Core\Route", array('params' => array('uri' => $uri, 'class' => $class, 'method' => $method, 'requestMethod' => 'post'), 'paramsConstraints' => $paramsConstraints));
    }

    /**
     * @param $name
     * @param $uri
     * @param $class
     * @param $method
     * @param array $paramsConstraints
     */
    public function any($name, $uri, $class, $method, array $paramsConstraints = array()) {
        $this->routes[$name] = $this->container->make("\Core\Route", array('params' => array('uri' => $uri, 'class' => $class, 'method' => $method), 'paramsConstraints' => $paramsConstraints));
    }

    /**
     * @param $class
     * @param $method
     */
    public function error404($class, $method) {
        $this->routes['404'] = $this->container->make("\Core\Route", array('params' => array('class' => $class, 'method' => $method)));
    }

    /**
     * @param $class
     * @param $method
     */
    public function error500($class, $method) {
        $this->routes['500'] = $this->container->make("\Core\Route", array('params' => array('class' => $class, 'method' => $method)));
    }

    /**
     * @param $name
     * @return mixed
     * @throws RuntimeException
     */
    public function getRoute($name) {
        if (!array_key_exists($name, $this->routes)) {
            throw new RuntimeException('Router: Route ' . $name . ' not found.');
        }
        return $this->routes[$name];
    }

    /**
     * @param $uri
     * @return bool|mixed|RouteMatchResult
     */
    public function match($uri) {
        $uri = trim($uri);
        foreach ($this->routes as $name => $route) {
            $result = $route->match($uri);

            if ($result !== false) {
                return $this->container->make('\Core\RouteMatchResult', array('name' => $name, 'route' => $route, 'requestParams' => $result));
            }
        }
        return false;
    }

    /**
     * @param $name
     * @param array $params
     * @return mixed
     */
    public function createUri($name, array $params = array()) {
        return $this->getRoute($name)->createUri($params);
    }

    /**
     * @param $name
     * @param array $params
     * @return string
     */
    public function createUrl($name, array $params = array()) {
        return $this->request->getBaseUrl() . $this->getRoute($name)->createUri($params);
    }

    /**
     * @param $routeName
     * @param array $params
     * @param array $query
     * @return string
     */
    public function route($routeName, array $params = array(), array $query = array()) {
        return $this->createUrl($routeName, $params) . (!empty($query) ? '?' . http_build_query($query) : '');
    }
}

class RouteMatchResult {
    private $request;
    private $name;
    private $route;
    private $requestParams = array();

    /**
     * RouteMatchResult constructor.
     * @param Request $request
     * @param $name
     * @param $route
     * @param array $requestParams
     * @internal param array $params
     */
    public function __construct(Request $request, $name, $route, array $requestParams) {
        $this->request = $request;
        $this->name = $name;
        $this->route = $route;
        $this->requestParams = $requestParams;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getRequestParams() {
        return $this->requestParams;
    }

    /**
     * Generate uri for this route. Params can be override
     *
     * @param array $params
     * @return mixed
     */
    public function getUri(array $params = array()) {
        return $this->route->createUri(array_merge($this->requestParams, $params));
    }

    /**
     * Generate url for this route. Params can be override
     *
     * @param array $params
     * @return string
     */
    public function getUrl(array $params = array()) {
        return $this->request->getBaseUrl() . $this->getUri($params);
    }

    /**
     * Return route output
     *
     * @return mixed
     */
    public function getResponse() {
        return $this->route->getResponse($this->requestParams);
    }
}