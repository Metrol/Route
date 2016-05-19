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
use \Metrol\Request;

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
            ->setHTTPMethod($route::HTTP_GET)
            ->addAction($action);

        $actions = $route->getActions();
        $methods = $actions[0]->getMethods();

        $this->assertEquals('testroute', $route->getName());
        $this->assertEquals('/imaroute/', $route->getMatchString());
        $this->assertEquals('Controller', $actions[0]->getClass());
        $this->assertEquals('doSomething', $methods[0]);
        $this->assertEquals('GET', $route->getHTTPMethod());
    }

    /**
     * See if the Route Match object is able to lookup a simple route
     *
     */
    public function testCheckMatchRoute()
    {
        $route = new Route('testroute');
        $route->setMatchString('/imaroute/')
              ->setHTTPMethod($route::HTTP_GET);

        $request = new Request;
        $request->server()->uri = '/imaroute/';
        $request->server()->method = 'GET';

        $match = Match::check($request, $route);
        $this->assertTrue($match);

        $request->server()->uri = '/notaroute/';
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
            ->setHTTPMethod(Route::HTTP_GET);

        $request = new Request;
        $request->server()->uri = '/view/1234/';
        $request->server()->method = 'GET';

        $match = Match::check($request, $route);

        $this->assertTrue($match);

        $request->server()->uri = '/view/Nope/';
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
            ->setHTTPMethod(Route::HTTP_GET);

        $request = new Request;
        $request->server()->method = 'GET';

        // Should match a real integer in the 2nd segment
        $request->server()->uri = '/view/1234/';

        $match = Match::check($request, $route);
        $this->assertTrue($match);

        // The found arguments should have been applied to the route
        $args = $route->getFoundArguments();
        $this->assertEquals(1234, $args[0]);

        // Should not match a floating point
        $request->server()->uri = '/view/12.34/';
        $match = Match::check($request, $route);

        $this->assertFalse($match);

        // Should not match a string
        $request->server()->uri = '/view/Nope/';
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
            ->setHTTPMethod(Route::HTTP_GET));

        Route\Bank::
        addRoute((new Route('Im a route post'))
            ->setMatchString('/imaroute/')
            ->setHTTPMethod(Route::HTTP_POST));

        Route\Bank::
        addRoute((new Route('View by ID'))
            ->setMatchString('/view/:int/')
            ->setHTTPMethod(Route::HTTP_GET));

        $fetched = Route\Bank::getNamedRoute('Im a route post');
        $this->assertNotNull($fetched);
        $this->assertEquals('Im a route post', $fetched->getName());

        $request = new Request;
        $request->server()->uri = '/imaroute/';
        $request->server()->method = 'GET';

        $fetched = Route\Bank::getRequestedRoute($request);
        $this->assertNotNull($fetched);
        $this->assertEquals('Im a route', $fetched->getName());

        $request->server()->uri = '/view/1234/';
        $fetched = Route\Bank::getRequestedRoute($request);
        $this->assertNotNull($fetched);
        $this->assertEquals('View by ID', $fetched->getName());
    }
}
