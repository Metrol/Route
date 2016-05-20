<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

use \Metrol\Route;
use \Metrol\Route\Action;
use \Metrol\Route\Match;
use \Metrol\Route\Request;

/**
 * Insure that routes can be created, given information, and have be able to
 * get that information back.
 *
 */
class RouteAddTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Add some routes, see what all happens...
     *
     */
    public function testRouteCreate()
    {
        $action = new Action;
        $action->setClass('Controller')
            ->addMethod('doSomething');

        $route = new Route('testroute');
        $route->setMatchString('/imaroute/')
            ->setHttpMethod($route::HTTP_GET)
            ->addAction($action);

        $actions = $route->getActions();
        $methods = $actions[0]->getMethods();

        $this->assertEquals('testroute', $route->getName());
        $this->assertEquals('/imaroute/', $route->getMatchString());
        $this->assertEquals('Controller', $actions[0]->getClass());
        $this->assertEquals('doSomething', $methods[0]);
        $this->assertEquals('GET', $route->getHttpMethod());
    }

    /**
     * See if the Route Match object is able to lookup a simple route
     *
     */
    public function testCheckMatchRoute()
    {
        $route = new Route('testroute');
        $route->setMatchString('/imaroute/')
              ->setHttpMethod($route::HTTP_GET);

        $request = new Request;
        $request->setUri('/imaroute/');
        $request->setHttpMethod('GET');

        $match = Match::check($request, $route);
        $this->assertTrue($match);

        $request->setUri('/notaroute/');
        $match = Match::check($request, $route);
        $this->assertFalse($match);
    }

    /**
     * Check that number hinted routes are working properly.
     *
     */
    public function testNumberHintedRouteMatching()
    {
        $route = new Route('View by ID');
        $route->setMatchString('/view/:num/')
            ->setHttpMethod(Route::HTTP_GET);

        $request = new Request;
        $request->setUri('/view/1234/');
        $request->setHttpMethod('GET');

        $match = Match::check($request, $route);

        $this->assertTrue($match);

        $request->setUri('/view/Nope/');
        $match = Match::check($request, $route);

        $this->assertFalse($match);
    }

    /**
     * Check that number hinted routes are working properly.
     *
     */
    public function testIntegerHintedRouteMatching()
    {
        $route = new Route('View by ID');
        $route->setMatchString('/view/:int/')
            ->setHttpMethod(Route::HTTP_GET);

        $request = new Request;
        $request->setHttpMethod('GET');

        // Should match a real integer in the 2nd segment
        $request->setUri('/view/1234/');

        $match = Match::check($request, $route);
        $this->assertTrue($match);

        // The found arguments should have been applied to the route
        $args = $route->getArguments();
        $this->assertEquals(1234, $args[0]);

        // Should not match a floating point
        $request->setUri('/view/12.34/');
        $match = Match::check($request, $route);

        $this->assertFalse($match);

        // Should not match a string
        $request->setUri('/view/Nope/');
        $match = Match::check($request, $route);

        $this->assertFalse($match);
    }

    /**
     * Make a run on the bank for a couple of routes to see if deposits and
     * withdrawls are working.
     *
     */
    public function testRouteBank()
    {
        Route\Bank::
        addRoute((new Route('Im a route'))
            ->setMatchString('/imaroute/')
            ->setHttpMethod(Route::HTTP_GET));

        Route\Bank::
        addRoute((new Route('Im a route post'))
            ->setMatchString('/imaroute/')
            ->setHttpMethod(Route::HTTP_POST));

        Route\Bank::
        addRoute((new Route('View by ID'))
            ->setMatchString('/view/:int/')
            ->setHttpMethod(Route::HTTP_GET));

        $fetched = Route\Bank::getNamedRoute('Im a route post');
        $this->assertNotNull($fetched);
        $this->assertEquals('Im a route post', $fetched->getName());

        $request = new Request;
        $request->setUri('/imaroute/');
        $request->setHttpMethod('GET');

        $fetched = Route\Bank::getRequestedRoute($request);
        $this->assertNotNull($fetched);
        $this->assertEquals('Im a route', $fetched->getName());

        $request->setUri('/view/1234/');
        $fetched = Route\Bank::getRequestedRoute($request);
        $this->assertNotNull($fetched);
        $this->assertEquals('View by ID', $fetched->getName());
    }
}
