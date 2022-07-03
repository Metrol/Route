<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Route;

use Metrol;
use Metrol\Route;

/**
 * Takes in a Route and can be used for creating a URL from it to be used as
 * a link.
 *
 */
class Reverse
{
    /**
     * The route this object will be working with.
     *
     */
    private Route $route;

    /**
     * A list of arguments that will be turned into URL segments
     *
     */
    private array $arguments;

    /**
     * List of key/values to be appended to the URL to be passed as a GET
     * request.
     *
     */
    private array $getArgs;

    /**
     * Store the route and instantiate the object.
     *
     */
    public function __construct(Route $route)
    {
        $this->route = $route;

        $this->arguments = [];
        $this->getArgs   = [];
    }

    /**
     * Produces the same as the output() method
     *
     */
    public function __toString(): string
    {
        return $this->output();
    }

    /**
     * Output the URL created here
     *
     */
    public function output(): string
    {
        $segmentsExp = explode('/', $this->route->getMatchString());
        $segments = [];

        // Make sure to weed out any empty segments
        foreach ( $segmentsExp as $segExp )
        {
            if ( ! empty($segExp) )
            {
                $segments[] = $segExp;
            }
        }

        $maxArgs  = $this->route->getMaxParameters();
        $arguments = $this->arguments;

        // Put arguments into segments that have hints in them.  They'll start
        // with a colon.
        foreach ( $segments as $idx => $segment )
        {
            if ( str_starts_with($segment, ':') )
            {
                $arg = array_shift($arguments);

                if ( $arg !== null )
                {
                    $segments[$idx] = urlencode($arg);
                }
            }
        }

        // Any arguments left, add them to the existing segments up to the max
        // number of parameters.
        $argCount = 0;

        foreach ( $arguments as $arg )
        {
            if ( $maxArgs === null or $argCount < $maxArgs )
            {
                $segments[] = urlencode($arg);
            }

            if ( $maxArgs !== null and $argCount >= $maxArgs )
            {
                break;
            }

            $argCount++;
        }

        $out = '/';
        $out .= implode('/', $segments);

        if ( !str_ends_with($out, '/') )
        {
            $out .= '/';
        }

        if ( ! empty($this->getArgs) )
        {
            $out .= '?';
            $pairs = [];

            foreach ( $this->getArgs as $key => $value )
            {
                $pairs[] = urlencode($key). '=' . urlencode($value);
            }

            $out .= implode('&', $pairs);
        }

        return $out;
    }

    /**
     * Add an argument to the stack to be applied to the URL segments
     *
     */
    public function addArg(string $arg): static
    {
        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * Add multiple arguments at once to the argument stack
     *
     */
    public function addArgs(array $args): static
    {
        foreach ( $args as $arg )
        {
            $this->arguments[] = $arg;
        }

        return $this;
    }

    /**
     * Removes all the arguments that have been passed into this route
     *
     */
    public function clearArgs(): static
    {
        $this->arguments = [];

        return $this;
    }

    /**
     * Add key value pair to the string to append as a GET request
     *
     */
    public function addGet(string $key, string $value): static
    {
        $this->getArgs[$key] = $value;

        return $this;
    }

    /**
     * Removes all the GET string arguments
     *
     */
    public function clearGetArgs(): static
    {
        $this->getArgs = [];

        return $this;
    }
}
