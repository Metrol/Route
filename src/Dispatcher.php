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
     * The Request object passed into the constructor that will be used to
     * determine which route to run actions from, and will be passed into
     * the actions run.
     *
     * @var Request
     */
    protected $request;

    /**
     * Takes in and saves the Route that will be loaded up along with the
     * Request that came across.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
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

            $controllerClass = $action->getControllerClass();
            $method          = $action->getControllerMethod();

            $controller = new $controllerClass($this->request);

            $out = $controller->$method($arguments);
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
