<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\Route\Load;
use Metrol\Route;
use Metrol\Route\Bank;
use Metrol\Route\Action;
use ReflectionMethod;
use ReflectionObject;

/**
 * Parses the method names and phpDoc blocks for route information to create
 * routes and add them to the bank
 *
 */
class Controller
{
    /**
     * Method prefixes that indicate what HTTP method should be matched.
     *
     * @const
     */
    const METHOD_GET    = 'get_';
    const METHOD_POST   = 'post_';
    const METHOD_DELETE = 'delete_';
    const METHOD_PUT    = 'put_';

    /**
     *
     * @const
     */
    const ATTR_MATCH     = '@match';
    const ATTR_NAME      = '@routename';
    const ATTR_MAX_PARAM = '@maxparam';

    /**
     * The controller class name in question
     *
     * @var string
     */
    private $controllerName;

    /**
     * Method prefixes to look for when parsing the controller class.
     *
     * @var array
     */
    private $methodPrefixes;

    /**
     * A list of docBlock attributes to look for when parsing the class
     *
     * @var array
     */
    private $attributes;

    /**
     * Initialize the object
     *
     */
    public function __construct()
    {
        $this->controllerName = '';

        $this->methodPrefixes = [
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_DELETE
        ];

        $this->attributes = [
            self::ATTR_MATCH,
            self::ATTR_NAME,
            self::ATTR_MAX_PARAM
        ];
    }

    /**
     * The fully qualified controller name that should be looked at.
     *
     * @param string
     *
     * @return $this
     */
    public function setControllerName($name): Controller
    {
        $this->controllerName = $name;

        return $this;
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
        $controller = new $this->controllerName();

        $refl = new ReflectionObject($controller);

        $methods = $refl->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ( $methods as $method )
        {
            if ( !$this->isMethodARoute($method) )
            {
                continue;
            }

            $attribs = $this->parseDocBlockForAttributes($method->getDocComment());

            $route = $this->createRoute($method, $attribs);
            $this->setMatch($route, $refl, $method, $attribs);
            $this->setMethod($route, $method);
            $this->setAction($route, $method);
            $this->setParams($route, $attribs);

            Bank::addRoute($route);
        }
    }

    /**
     * Create the route to be added into the bank
     *
     * @param ReflectionMethod $method
     * @param array             $attribs
     *
     * @return Route
     */
    private function createRoute(ReflectionMethod $method, array $attribs): Route
    {
        if ( isset($attribs[self::ATTR_NAME]) )
        {
            $routeName = $attribs[self::ATTR_NAME];
        }
        else
        {
            $routeName = $this->controllerName . ':' . $method->getName();
        }

        return new Route($routeName);
    }

    /**
     * Checks the provided method to see if it should be used as a route.
     *
     * @param ReflectionMethod $method
     *
     * @return boolean
     */
    private function isMethodARoute(ReflectionMethod $method): bool
    {
        $methodName = $method->getName();

        $rtn = false;

        foreach ( $this->methodPrefixes as $prefix )
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
     * @param Route             $route
     * @param ReflectionObject  $reflCont
     * @param ReflectionMethod  $method
     * @param array             $attribs
     */
    private function setMatch(Route $route, ReflectionObject $reflCont,
                              ReflectionMethod $method, array $attribs): void
    {
        if ( isset($attribs[self::ATTR_MATCH]) )
        {
            $match = $attribs[self::ATTR_MATCH];
        }
        else
        {
            if ( $reflCont->hasConstant('MATCH_PREFIX') )
            {
                $class    = $this->controllerName;
                $matchPre = $class::MATCH_PREFIX;
            }
            else
            {
                $cname    = strtolower($reflCont->getShortName());
                $matchPre = '/' . $cname . '/';
            }

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

            if ( substr($match, -1) != '/' )
            {
                $match .=  '/';
            }
        }

        $route->setMatchString($match);
    }

    /**
     * Sets the HTTP method for the action
     *
     * @param Route             $route
     * @param ReflectionMethod $method
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
     * @param Route  $route
     * @param ReflectionMethod $method
     */
    private function setAction(Route $route, ReflectionMethod $method): void
    {
        $action = new Action;
        $action->setControllerClass($this->controllerName);
        $action->setControllerMethod($method->getName());

        $route->addAction($action);
    }

    /**
     * Sets the maximum parameters to the route if specified in the attributes.
     *
     * @param Route $route
     * @param array $attribs
     */
    private function setParams(Route $route, array $attribs): void
    {
        if ( isset($attribs[self::ATTR_MAX_PARAM]) )
        {
            $route->setMaxParameters( $attribs[self::ATTR_MAX_PARAM] );
        }
        else
        {
            $route->setMaxParameters(0);
        }
    }

    /**
     * Parse through the provided docBlock text looking for attributes that can
     * be passed to a route.
     *
     * @param string $docBlock
     *
     * @return array Documentation attributes
     */
    private function parseDocBlockForAttributes($docBlock): array
    {
        $rtn = array();

        // See that we've got some kind of attributes before going any further
        if ( strpos($docBlock, '* @') === false )
        {
            return $rtn;
        }

        $docLines = preg_split ('/$\R?^/m', $docBlock);

        foreach ( $docLines as $docLine )
        {
            $docLine = trim(str_replace('* ', '', $docLine));

            foreach ( $this->attributes as $attrib )
            {
                if ( strpos($docLine, $attrib) !== false )
                {
                    $value = substr($docLine, strlen($attrib) + 1 );

                    $rtn[$attrib] = trim($value);
                }
            }
        }

        return $rtn;
    }
}
