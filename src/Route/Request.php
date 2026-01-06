<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Route;

use Metrol;

/**
 * Defines a request that can be compared against a route
 *
 */
class Request
{
    const string DEFAULT_URI         = '/';
    const string DEFAULT_HTTP_METHOD = Metrol\Route::HTTP_GET;

    private const array ALLOWED_METHODS = [
        'GET', 'POST', 'PUT', 'DELETE'
    ];

    /**
     * The URI being passed in as part of the request
     *
     */
    protected string $uri = self::DEFAULT_URI;

    /**
     * The HTTP method being requested
     *
     */
    protected string $httpMethod = self::DEFAULT_HTTP_METHOD;

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

        if ( isset($_SERVER['REQUEST_METHOD']) )
        {
            $this->setHttpMethod($_SERVER['REQUEST_METHOD']);
        }
    }

    /**
     * Used to set the information in this object based on a Metrol\Request
     * object.
     *
     */
    public function setRequest(Metrol\Request $request): static
    {
        $this->setUri($request->server()->uri);
        $this->setHttpMethod($request->server()->method);

        return $this;
    }

    /**
     *
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     *
     */
    public function setUri(string $uri): static
    {
        if ( str_contains($uri, '?') )
        {
            $uri = substr($uri, 0, strpos($uri, '?') );
        }

        $this->uri = $uri;

        return $this;
    }

    /**
     *
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     *
     */
    public function setHttpMethod(string $httpMethod): static
    {
        $httpMethod = strtoupper($httpMethod);

        if ( in_array($httpMethod, self::ALLOWED_METHODS) )
        {
            $this->httpMethod = $httpMethod;
        }

        return $this;
    }
}
