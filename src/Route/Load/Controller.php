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
    }

    /**
     * The fully qualified controller name that should be looked at.
     *
     * @param string
     *
     * @return $this
     */
    public function setControllerName($name)
    {
        $this->controllerName = $name;

        return $this;
    }

    /**
     * There is already an assumption that the validity of the file name has
     * already been checked before this is even attempted.
     *
     */
    public function run()
    {
        $this->buildRoutes();
    }


    /**
     * Takes the parsed information, builds out the routes, and adds those
     * routes to the Bank to be looked up later.
     *
     */
    private function buildRoutes()
    {
        $controller = new $this->controllerName();

        $refl = new \ReflectionObject($controller);

        $methods = $refl->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ( $methods as $method )
        {
            if ( !$this->isMethodARoute($method) )
            {
                continue;
            };

            $route = $this->createRoute($method);
            $this->setMatch($route, $refl, $method);
            $this->setMethod($route, $method);
            $this->setAction($route, $method);

            Bank::addRoute($route);
        }
    }

    /**
     * Create the route to be added into the bank
     *
     * @param \ReflectionMethod $method
     *
     * @return Route
     */
    private function createRoute(\ReflectionMethod $method)
    {
        $routeName = $this->controllerName.':'.$method->getName();

        $route = new Route($routeName);

        return $route;
    }

    /**
     * Checks the provided method to see if it should be used as a route.
     *
     * @param \ReflectionMethod $method
     *
     * @return boolean
     */
    private function isMethodARoute(\ReflectionMethod $method)
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
     *
     * @param Route             $route
     * @param \ReflectionObject $reflCont
     * @param \ReflectionMethod $method
     */
    private function setMatch(Route $route,\ReflectionObject $reflCont,
                              \ReflectionMethod $method)
    {
        if ( $reflCont->hasConstant('MATCH_PREFIX') )
        {
            $class = $this->controllerName;
            $matchPre = $class::MATCH_PREFIX;
        }
        else
        {
            $cname = strtolower($reflCont->getShortName());
            $matchPre = '/' . $cname . '/';
        }

        $methodMatch = substr($method->getName(), strpos($method->getName(), '_') + 1);

        $match = $matchPre.$methodMatch.'/';

        $route->setMatchString($match);
    }

    /**
     *
     * @param Route             $route
     * @param \ReflectionMethod $method
     */
    private function setMethod(Route $route, \ReflectionMethod $method)
    {
        $mName = $method->getName();

        $httpMethod = substr($mName, 0, strpos($mName, '_'));
        $httpMethod = strtoupper($httpMethod);

        $route->setHttpMethod($httpMethod);
    }

    /**
     *
     * @param Route  $route
     * @param \ReflectionMethod $method
     */
    private function setAction(Route $route, \ReflectionMethod $method)
    {
        $action = new Action;
        $action->setControllerClass($this->controllerName);
        $action->setControllerMethod($method->getName());

        $route->addAction($action);
    }
}
