<?php
namespace Application\Routing;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

class Router
{

    const DEFAULT_MATCH = 'default';
    const ERROR_NO_DEF  = 'ERROR: you must supply a default match';

    protected $request;
    protected $requestUri;
    protected $uriParts;
    protected $docRoot;
    protected $config;
    protected $routeMatch;

    public function __construct(ServerRequestInterface $request, $docRoot, $config)
    {
        $this->config = $config;
        $this->docRoot = $docRoot;
        $this->request = $request;
        $this->requestUri = $request->getServerParams()['REQUEST_URI'];
        $this->uriParts = explode('/', $this->requestUri);
        if (!isset($config[self::DEFAULT_MATCH])) {
            throw new InvalidArgumentException(self::ERROR_NO_DEF);
        }
    }
    public function getRequest()
    {
        return $this->request;
    }
    public function getDocRoot()
    {
        return $this->docRoot;
    }
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }
    public function isFileOrDir()
    {
        $fn = $this->docRoot . '/' . $this->requestUri;
        $fn = str_replace('//', '/', $fn);
        if (file_exists($fn)) {
            return $fn;
        } else {
            return '';
        }
    }
    public function match()
    {
        foreach ($this->config as $key => $route) {
            if (preg_match($route['uri'], $this->requestUri, $matches)) {
                $this->routeMatch['key'] = $key;
                $this->routeMatch['match'] = $matches;
                return $route['exec'];
            }
        }
        return $this->config[self::DEFAULT_MATCH]['exec'];
    }
}
