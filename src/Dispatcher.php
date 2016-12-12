<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol;

/**
 * Converts a Route into the calls to action
 *
 */
class Dispatcher
{
    /**
     * Errors that may occur when trying to get a route
     *
     * @const integer
     */
    const READY_TO_EXECUTE     = 0;
    const ROUTE_NOT_FOUND      = 12;
    const ACTION_NOT_FOUND     = 25;

    /**
     * The Request object passed into the constructor that will be used to
     * determine which route to run actions from, and will be passed into
     * the actions run.
     *
     * @var Request
     */
    protected $request;

    /**
     * The status of the route request following a run.
     *
     * @var integer|null
     */
    protected $routeStatus = null;

    /**
     * Set of found actions to be executed
     *
     * @var Route\Action[]
     */
    protected $actions = [];

    /**
     * The argument values found in the request to be passed along to the
     * action.
     *
     * @var array
     */
    protected $arguments = [];

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
     * Performs a look up on the request to establish all the actions that need
     * to be executed.  This will also set the route status flag accordingly.
     *
     * @return $this
     */
    public function run()
    {
        $route = $this->findRoute();

        if ( $route == null )
        {
            $this->routeStatus = self::ROUTE_NOT_FOUND;

            return $this;
        }

        $this->actions   = $route->getActions();
        $this->arguments = $route->getArguments();

        $this->verifyActions();

        return $this;
    }

    /**
     * Provide the route status following run() being called
     *
     * @return integer|null
     */
    public function getRouteStatus()
    {
        return $this->routeStatus;
    }

    /**
     * Make sure all the controllers and actions in the route exist and are
     * ready to be run.  Set the route status accordingly.
     *
     * @return void
     */
    protected function verifyActions()
    {
        foreach ( $this->actions as $action )
        {
            if ( ! $action->isReady() )
            {
                $this->routeStatus = self::ACTION_NOT_FOUND;

                return;
            }

            $controllerClass = $action->getControllerClass();

            if ( ! class_exists($controllerClass) )
            {
                $this->routeStatus = self::ACTION_NOT_FOUND;

                return;
            }

            $method = $action->getControllerMethod();

            if ( ! method_exists($controllerClass, $method) )
            {
                $this->routeStatus = self::ACTION_NOT_FOUND;

                return;
            }
        }

        // Route was found, and all the actions are ready to go.
        $this->routeStatus = self::READY_TO_EXECUTE;
    }

    /**
     * Execute the action based on the found route following a run.
     *
     * @return string
     *
     * @throws \UnderflowException
     * @throws \OutOfBoundsException
     */
    public function execute()
    {
        if ( $this->routeStatus === null )
        {
            throw new \UnderflowException('Can not execute a controller action without run() first');
        }

        if ( $this->routeStatus !== self::READY_TO_EXECUTE )
        {
            throw new \OutOfBoundsException('The appropriate routing information not ready to execute');
        }

        $out = '';

        foreach ( $this->actions as $action )
        {
            $controllerClass = $action->getControllerClass();
            $method          = $action->getControllerMethod();

            $controller = new $controllerClass($this->request);

            $out = $controller->$method($this->arguments);
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
