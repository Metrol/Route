<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Route\Load\Controller;
use Metrol\Route;
use Metrol\Route\Bank;
use Metrol\Route\Action;
use ReflectionMethod;
use ReflectionClass;
use ReflectionException;

/**
 * Parses the method names and php doc blocks for route information to create
 * routes and add them to the bank
 *
 */
class DocBlock
{
    /**
     * Method prefixes that indicate what HTTP method should be matched.
     *
     */
    const METHOD_GET    = 'get_';
    const METHOD_POST   = 'post_';
    const METHOD_DELETE = 'delete_';
    const METHOD_PUT    = 'put_';

    private const PREFIX_REF = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_DELETE
    ];

    /**
     * Doc block flags to look for
     *
     */
    const TAG_MATCH     = '@match';
    const TAG_NAME      = '@routename';
    const TAG_MAX_PARAM = '@maxparam';

    private const TAG_REF = [
        self::TAG_MATCH,
        self::TAG_NAME,
        self::TAG_MAX_PARAM
    ];

    /**
     * The reflection object for the controller to parse through
     *
     */
    private ReflectionClass $reflect;

    /**
     * Initialize the object
     *
     */
    public function __construct(ReflectionClass $reflectionObj)
    {
        $this->reflect = $reflectionObj;
    }

    /**
     * There is already an assumption that the validity of the file name has
     * already been checked before this is even attempted.
     *
     */
    public function run(): void
    {
        $this->buildRoutes();
    }

    /**
     * Takes the parsed information, builds out the routes, and adds those
     * routes to the Bank to be looked up later.
     *
     */
    private function buildRoutes(): void
    {
        $methods = $this->reflect->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ( $methods as $method )
        {
            if ( !$this->isMethodARoute($method) )
            {
                continue;
            }

            $tags = $this->parseDocBlockForTags($method->getDocComment());

            $route = $this->createRoute($method, $tags);
            $this->setMatch($route,  $method, $tags);
            $this->setMethod($route, $method);
            $this->setAction($route, $method);
            $this->setParams($route, $tags);

            Bank::addRoute($route);
        }
    }

    /**
     * Create the route to be added into the bank
     *
     */
    private function createRoute(ReflectionMethod $method, array $tags): Route
    {
        $routeName = $this->getRouteName($method, $tags);

        return new Route($routeName);
    }

    /**
     * Checks the provided method to see if it should be used as a route.
     *
     */
    private function isMethodARoute(ReflectionMethod $method): bool
    {
        $methodName = $method->getName();

        $rtn = false;

        foreach ( self::PREFIX_REF as $prefix )
        {
            if ( strlen($methodName) < strlen($prefix) )
            {
                continue;
            }

            $methodPre = substr($methodName, 0, strlen($prefix) );

            if ( $methodPre == $prefix )
            {
                $rtn = true;
                break;
            }

        }

        return $rtn;
    }

    /**
     * Set the match string for the router to look for
     *
     */
    private function setMatch(Route            $route,
                              ReflectionMethod $method,
                              array            $tags): void
    {
        if ( isset($tags[self::TAG_MATCH]) )
        {
            $match = $tags[self::TAG_MATCH];
        }
        else
        {
            $matchPre = $this->getMatchPrefix();

            $methodName = substr($method->getName(), strpos($method->getName(), '_') + 1);

            if ( empty($methodName) )
            {
                $match = $matchPre;
            }
            else
            {
                $methodMatch = str_replace('_', '/', $methodName);
                $match       = $matchPre . $methodMatch;
            }

            if ( ! str_ends_with($match, '/') )
            {
                $match .=  '/';
            }
        }

        $route->setMatchString($match);
    }

    /**
     * Sets the HTTP method for the action
     *
     */
    private function setMethod(Route $route, ReflectionMethod $method): void
    {
        $mName = $method->getName();

        $httpMethod = substr($mName, 0, strpos($mName, '_'));
        $httpMethod = strtoupper($httpMethod);

        $route->setHttpMethod($httpMethod);
    }

    /**
     * Based on the controller name and specified method, add an action for
     * the route.
     *
     */
    private function setAction(Route $route, ReflectionMethod $method): void
    {
        $action = new Action;
        $action->setControllerClass($this->reflect->name);
        $action->setControllerMethod($method->getName());

        $route->addAction($action);
    }

    /**
     * Sets the maximum parameters to the route if specified in the tags.
     *
     */
    private function setParams(Route $route, array $tags): void
    {
        if ( isset($tags[self::TAG_MAX_PARAM]) )
        {
            $route->setMaxParameters( $tags[self::TAG_MAX_PARAM] );
        }
        else
        {
            $route->setMaxParameters(0);
        }
    }

    /**
     * Parse through the provided docBlock text looking for tags that can
     * be passed to a route.
     *
     */
    private function parseDocBlockForTags(string $docBlock): array
    {
        $rtn = [];

        // See that we've got some kind of tag before going any further
        if ( ! str_contains($docBlock, '* @') )
        {
            return $rtn;
        }

        $docLines = preg_split ('/$\R?^/m', $docBlock);

        foreach ( $docLines as $docLine )
        {
            $docLine = trim(str_replace('* ', '', $docLine));

            foreach (self::TAG_REF as $tag )
            {
                if ( str_contains($docLine, $tag) )
                {
                    $value = substr($docLine, strlen($tag) + 1 );

                    $rtn[$tag] = trim($value);
                }
            }
        }

        return $rtn;
    }

    /**
     * Provide either the default name built on the controller/action or use
     * the provided name from the docblock tag.
     *
     */
    private function getRouteName(ReflectionMethod $method, array $tags): string
    {
        if ( isset($tags[self::TAG_NAME]) )
        {
            return $tags[self::TAG_NAME];
        }

        $controllerName = $this->reflect->name;
        $parentControllerName = $this->reflect->getParentClass()->name;
        $parNameLen = strlen($parentControllerName) + 1;

        $linkPrefix = substr($controllerName, $parNameLen);
        $linkPrefix = str_replace('\\', ' ', $linkPrefix);

        $methodName = $method->getName();

        $methodName = str_replace(self::PREFIX_REF, '', $methodName);

        $methodName = str_replace('_', ' ', $methodName);

        $routeName = ucwords($linkPrefix . ' ' . $methodName);

        return trim($routeName);
    }

    /**
     * Provide the prefix of the match string based on either the controller
     * name or a class constant in the controller named MATCH_PREFIX.
     *
     */
    private function getMatchPrefix(): string
    {
        if ( $this->reflect->hasConstant('MATCH_PREFIX') )
        {
            $class = $this->reflect->name;

            return $class::MATCH_PREFIX;
        }

        // Otherwise, calculate what the prefix should be based on the class
        // itself.
        $controllerName = $this->reflect->name;
        $parentControllerName = $this->reflect->getParentClass()->name;
        $parNameLen = strlen($parentControllerName) + 1;

        $linkPrefix = substr($controllerName, $parNameLen);
        $linkPrefix = str_replace('\\', '/', $linkPrefix);
        $linkPrefix = '/' . $linkPrefix . '/';

        return strtolower($linkPrefix);
    }
}
