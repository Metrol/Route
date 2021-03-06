<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

use PHPUnit\Framework\TestCase;
use Metrol\Route;
use Metrol\Route\Action;
use Metrol\Route\MatchRoute;
use Metrol\Route\Request;

/**
 * Insure that routes can be created, given information, and have be able to
 * get that information back.
 *
 */
class RouteAddTest extends TestCase
{
    /**
     * Add some routes, see what all happens...
     *
     */
    public function testRouteCreate()
    {
        $action = new Action;
        $action->setControllerClass('Controller')
            ->setControllerMethod('doSomething');

        $route = new Route('testroute');
        $route->setMatchString('/imaroute/')
            ->setHttpMethod($route::HTTP_GET)
            ->addAction($action);

        $actions = $route->getActions();
        $methods = $actions[0]->getControllerMethod();

        $this->assertEquals('testroute', $route->getName());
        $this->assertEquals('/imaroute/', $route->getMatchString());
        $this->assertEquals('\Controller', $actions[0]->getControllerClass());
        $this->assertEquals('doSomething', $methods);
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

        $match = MatchRoute::check($request, $route);
        $this->assertTrue($match);

        $request->setUri('/notaroute/');
        $match = MatchRoute::check($request, $route);
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

        $match = MatchRoute::check($request, $route);

        $this->assertTrue($match);

        $request->setUri('/view/Nope/');
        $match = MatchRoute::check($request, $route);

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

        $match = MatchRoute::check($request, $route);
        $this->assertTrue($match);

        // The found arguments should have been applied to the route
        $args = $route->getArguments();
        $this->assertEquals(1234, $args[0]);

        // Should not match a floating point
        $request->setUri('/view/12.34/');
        $match = MatchRoute::check($request, $route);

        $this->assertFalse($match);

        // Should not match a string
        $request->setUri('/view/Nope/');
        $match = MatchRoute::check($request, $route);

        $this->assertFalse($match);
    }

    /**
     * Make sure that extra arguments not hinted are properly gathered up and
     * passed to the route.
     *
     */
    public function testOverflowArguments()
    {
        $route = (new Route('View by ID'))->setMatchString('/view/:int/');
        $request = (new Request)
            ->setUri('/view/1234/abcd/xyz/')
            ->setHttpMethod('GET');

        $match = MatchRoute::check($request, $route);
        $args  = $route->getArguments();

        $this->assertTrue($match);
        $this->assertEquals('1234', $args[0]);
        $this->assertEquals('abcd', $args[1]);
        $this->assertEquals('xyz',  $args[2]);

        // Now set a max parameter value to make sure we don't get false hits
        $route->setMaxParameters(1);
        $match = MatchRoute::check($request, $route);

        $this->assertFalse($match);

        $route->setMaxParameters(2);
        $match = MatchRoute::check($request, $route);
        $this->assertTrue($match);
    }

    /**
     * Make a run on the bank for a couple of routes to see if deposits and
     * withdrawals are working.
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
