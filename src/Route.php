<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol;

/**
 * Used to describe an individual route
 *
 */
class Route
{
    /**
     * GET should be used for a request to read data
     *
     * @const string
     */
    const HTTP_GET = 'GET';

    /**
     * POST should be used to create a new record
     *
     * @const string
     */
    const HTTP_POST = 'POST';

    /**
     * PUT is to Update or Replace information
     *
     * @const string
     */
    const HTTP_PUT = 'PUT';

    /**
     * DELETE requests information be removed
     *
     * @const string
     */
    const HTTP_DELETE = 'DELETE';

    /**
     * Hints to look for in the match string
     *
     * @const string
     */
    const HINT_INTEGER = ':int';
    const HINT_NUMBER  = ':num';
    const HINT_STRING  = ':str';

    /**
     * The name of this route.
     *
     * @var string
     */
    protected $name;

    /**
     * List of actions associated with this route
     *
     * @var Route\Action[]
     */
    protected $actions;

    /**
     * The string that will be compared against the incoming URI for a match
     *
     * @var string
     */
    protected $match;

    /**
     * The HTTP method used for this request
     *
     * @var string
     */
    protected $httpMethod;

    /**
     * Defines how many segments after the last filter segment will be allowed
     * as arguments to the action.
     *
     * @var integer
     */
    protected $maxParams;

    /**
     * When checking if this route matches, any arguments found in the URI
     * are passed along into this list.
     *
     * @var array
     */
    protected $foundArguments;

    /**
     * Initializes the Route object
     *
     * @param string $routeName Name of this route
     */
    public function __construct($routeName)
    {
        $this->name = $routeName;

        $this->match          = '';
        $this->actions        = array();
        $this->httpMethod     = self::HTTP_GET;
        $this->maxParams      = null;
        $this->foundArguments = array();
    }

    /**
     * Provides the assigned name of the route
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add a new Action to the route
     *
     * @param Route\Action $action
     *
     * @return $this
     */
    public function addAction(Route\Action $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Provide the list of actions assigned to this route
     *
     * @return Route\Action[]
     */
    public function getActions()
    {
        return $this->actions;
    }
    /**
     * Sets the URI filtering string
     *
     * @param string
     *
     * @return $this
     */
    public function setMatchString($match)
    {
        $this->match = $match;

        return $this;
    }

    /**
     * Provide the match string that will be looked for.
     *
     * @return string
     */
    public function getMatchString()
    {
        return $this->match;
    }

    /**
     * Sets the HTTP request method
     *
     * @param string $method
     *
     * @return $this
     */
    public function setHTTPMethod($method)
    {
        $method = strtoupper($method);

        switch ( $method )
        {
            case self::HTTP_GET:
                $this->httpMethod = $method;
                break;

            case self::HTTP_POST:
                $this->httpMethod = $method;
                break;

            case self::HTTP_PUT:
                $this->httpMethod = $method;
                break;

            case self::HTTP_DELETE:
                $this->httpMethod = $method;
                break;

            default:
                $this->httpMethod = self::HTTP_GET;
        }

        return $this;
    }

    /**
     * Provide the HTTP method this route is for
     *
     * @return string
     */
    public function getHTTPMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Sets the maximum number of segments following the match filter that will
     * be allowed to exist when matching.
     *
     * Setting to null will allow for any number of parameters
     *
     * @param integer|null $maxParameters
     *
     * @return $this
     */
    public function setMaxParameters($maxParameters = null)
    {
        $this->maxParams = intval($maxParameters);

        return $this;
    }

    /**
     * Provide the maximum parameters this route is looking for
     *
     * @return integer
     */
    public function getMaxParameters()
    {
        return $this->maxParams;
    }

    /**
     * Set the arguments found in the URI for a matching route
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function setFoundArguments(array $arguments)
    {
        $this->foundArguments = $arguments;

        return $this;
    }

    /**
     * Get the arguments found in the URI for a matching route
     *
     * @return array
     */
    public function getFoundArguments()
    {
        return $this->foundArguments;
    }
}
