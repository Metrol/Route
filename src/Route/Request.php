<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\Route;

use Metrol;

/**
 * Defines a request that can be compared against a route
 *
 */
class Request
{
    const DEFAULT_URI = '/';
    const DEFAULT_HTTP_METHOD = Metrol\Route::HTTP_GET;

    /**
     * The URI being passed in as part of the request
     *
     * @var string
     */
    protected $uri;

    /**
     * The HTTP method being requested
     *
     * @var string
     */
    protected $httpMethod;

    /**
     * Initializes the Route Request Definition
     *
     */
    public function __construct()
    {
        if ( isset($_SERVER['REQUEST_URI']) )
        {
            $this->setUri($_SERVER['REQUEST_URI']);
        }
        else
        {
            $this->uri = self::DEFAULT_URI;
        }

        if ( isset($_SERVER['REQUEST_METHOD']) )
        {
            $this->setHttpMethod($_SERVER['REQUEST_METHOD']);
        }
        else
        {
            $this->httpMethod = self::DEFAULT_HTTP_METHOD;
        }
    }

    /**
     * Used to set the information in this object based on a Metrol\Request
     * object.
     *
     * @param Metrol\Request $request
     *
     * @return $this
     */
    public function setRequest(Metrol\Request $request)
    {
        $this->setUri($request->server()->uri);
        $this->setHttpMethod($request->server()->method);

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     *
     * @param string $uri
     *
     * @return $this
     */
    public function setUri($uri)
    {
        if ( strpos($uri, '?') !== false )
        {
            $uri = substr($uri, 0, strpos($uri, '?') );
        }

        $this->uri = $uri;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     *
     * @param string $httpMethod
     *
     * @return $this
     */
    public function setHttpMethod($httpMethod)
    {
        $httpMethod = strtoupper($httpMethod);

        $allowedMethods = [
            'GET', 'POST', 'PUT', 'DELETE'
        ];

        if ( in_array($httpMethod, $allowedMethods) )
        {
            $this->httpMethod = $httpMethod;
        }

        return $this;
    }
}
