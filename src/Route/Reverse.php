<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
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
     * @var Route
     */
    private $route;

    /**
     * A list of arguments that will be turned into URL segments
     *
     * @var array
     */
    private $arguments;

    /**
     * List of key/values to be appended to the URL to be passed as a GET
     * request.
     *
     * @var array
     */
    private $getArgs;

    /**
     * Store the route and instantiate the object.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;

        $this->arguments = array();
        $this->getArgs   = array();
    }

    /**
     * Produces the same as the output() method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->output();
    }

    /**
     * Output the URL created here
     *
     * @return string
     */
    public function output()
    {
        $segmentsExp = explode('/', $this->route->getMatchString());
        $segments = array();

        // Make sure to weed out any empty segments from the explode
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
            if ( substr($segment, 0, 1) == ':' )
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

        if ( substr($out, -1) != '/' )
        {
            $out .= '/';
        }

        if ( ! empty($this->getArgs) )
        {
            $out .= '?';
            $pairs = array();

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
     * @param string $arg
     *
     * @return $this
     */
    public function addArg($arg)
    {
        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * Add multiple arguments at once to the argument stack
     *
     * @param array $args
     *
     * @return $this
     */
    public function addArgs(array $args)
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
     * @return $this
     */
    public function clearArgs()
    {
        $this->arguments = array();

        return $this;
    }

    /**
     * Add key value pair to the string to append as a GET request
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addGet($key, $value)
    {
        $this->getArgs[$key] = $value;

        return $this;
    }

    /**
     * Removes all the GET string arguments
     *
     * @return $this
     */
    public function clearGetArgs()
    {
        $this->getArgs = array();

        return $this;
    }
}
