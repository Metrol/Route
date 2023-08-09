<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol;

/**
 * Used to describe an individual route
 *
 */
class Route
{
    /**
     * Hints to look for in the match string
     *
     */
    const HINT_INTEGER = ':int';
    const HINT_NUMBER  = ':num';
    const HINT_STRING  = ':str';

    /**
     * GET should be used for a request to read data
     *
     */
    const HTTP_GET = 'GET';

    /**
     * POST should be used to create a new record
     *
     */
    const HTTP_POST = 'POST';

    /**
     * PUT is to Update or Replace information
     *
     */
    const HTTP_PUT = 'PUT';

    /**
     * DELETE requests information be removed
     *
     */
    const HTTP_DELETE = 'DELETE';

    /**
     * List of all the allowed methods that can be processed here
     *
     */
    static private array $allowedMethods = [
       self::HTTP_GET,
       self::HTTP_POST,
       self::HTTP_PUT,
       self::HTTP_DELETE
    ];

    /**
     * The name of this route.
     *
     */
    protected string $name;

    /**
     * List of actions associated with this route
     *
     * @var Route\Action[]
     */
    protected array $actions = [];

    /**
     * The string that will be compared against the incoming URI for a match
     *
     */
    protected string $match = '';

    /**
     * The HTTP method used for this request
     *
     */
    protected string $httpMethod = self::HTTP_GET;

    /**
     * Defines how many segments after the last filter segment will be allowed
     * as arguments to the action.
     *
     */
    protected int $maxParams = 0;

    /**
     * When checking if this route matches, any arguments found in the URI
     * are passed along into this list.
     *
     */
    protected array $foundArguments = [];

    /**
     * Initializes the Route object
     *
     * @param string $routeName Name of this route
     */
    public function __construct(string $routeName)
    {
        $this->name = $routeName;
    }

    /**
     * Provides the assigned name of the route
     *
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Provide the Route Reversal object for this route to produce a URL
     *
     */
    public function getReverse(): Route\Reverse
    {
        return new Route\Reverse($this);
    }

    /**
     * Add a new Action to the route
     *
     */
    public function addAction(Route\Action $action): static
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Provide the list of actions assigned to this route
     *
     */
    public function getActions(): array
    {
        return $this->actions;
    }
    /**
     * Sets the URI filtering string
     *
     */
    public function setMatchString(string $match): static
    {
        $this->match = $match;

        return $this;
    }

    /**
     * Provide the match string that will be looked for.
     *
     */
    public function getMatchString(): string
    {
        return $this->match;
    }

    /**
     * Sets the HTTP request method.
     *
     */
    public function setHttpMethod(string $method): static
    {
        $method = strtoupper($method);

        if ( in_array($method, self::$allowedMethods) )
        {
            $this->httpMethod = $method;
        }

        return $this;
    }

    /**
     * Provide the HTTP method this route is for
     *
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * Sets the maximum number of segments following the match filter that will
     * be allowed to exist when matching.
     *
     */
    public function setMaxParameters(int $maxParameters): static
    {
        $this->maxParams = $maxParameters;

        return $this;
    }

    /**
     * Provide the maximum parameters this route is looking for
     *
     */
    public function getMaxParameters(): int
    {
        return $this->maxParams;
    }

    /**
     * Set the arguments found in the URI for a matching route
     *
     */
    public function setArguments(array $arguments): static
    {
        $this->foundArguments = $arguments;

        return $this;
    }

    /**
     * Get the arguments found in the URI for a matching route
     *
     */
    public function getArguments(): array
    {
        return $this->foundArguments;
    }
}
