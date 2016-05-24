<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol;
use Metrol\Request;

/**
 * Converts a Route into the calls to action
 *
 */
class Dispatcher
{
    /**
     *
     * @var Route
     */
    protected $route;

    /**
     *
     * @var Request
     */
    protected $request;


    /**
     * Takes in and saves the Route that will be loaded up along with the
     * Request that came across.
     *
     */
    public function __construct()
    {
        $this->request = new Request;
    }

    /**
     * Executes the actions found in the route, then outputs the last action
     * back to the caller.
     *
     * @return string
     */
    public function run()
    {
        $out = '';

        $route = $this->findRoute();

        if ( $route == null )
        {
            return $out;
        }

        $actions   = $route->getActions();
        $arguments = $route->getArguments();

        foreach ( $actions as $action )
        {
            if ( ! $action->isReady() )
            {
                continue;
            }

            $controllerClass = $action->getClass();
            $methods         = $action->getMethods();

            $controller = new $controllerClass($this->request);

            foreach ( $methods as $method )
            {
                $out = $controller->$method($arguments);
            }
        }

        return $out;
    }

    /**
     * Attempt to find a route in the Bank based on the incoming request
     *
     * @return Route|null
     */
    private function findRoute()
    {
        $rtReq = new Route\Request;

        return Route\Bank::getRequestedRoute($rtReq);
    }
}
