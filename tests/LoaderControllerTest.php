<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Tests;

use PHPUnit\Framework\TestCase;
use Metrol\Route\{Load, Bank};
use ReflectionClass;

/**
 * Verifies that routes can be loaded from a controller class, as well as a
 * defined parent controller
 *
 */
class LoaderControllerTest extends TestCase
{
    /**
     * Test loading in a routes from a Controller class
     *
     */
    public function testLoadingFromAControllerClass(): void
    {
        // Make sure all routes going into the bank are from here
        Bank::clearAllRoutes();
        $controllerName = '\Metrol\Tests\Controller\ActionCity';

        $refClass = new ReflectionClass($controllerName);

        $parser = new Load\Controller($refClass);
        $parser->run();

        $action = 'ActionCity Get View';

        $route = Bank::getNamedRoute($action);
        $this->assertEquals($action, $route->getName());

        $this->assertEquals('/tester/view/', $route->getMatchString());
        $this->assertEquals('GET', $route->getHttpMethod());
        $this->assertEquals(0, $route->getMaxParameters());

        // Verify a route with a different HTTP method
        $action = 'ActionCity Post Updatestuff';
        $route = Bank::getNamedRoute($action);

        $this->assertEquals($action, $route->getName());
        $this->assertEquals('/tester/updatestuff/', $route->getMatchString());
        $this->assertEquals('POST', $route->getHttpMethod());

        // Run some tests on a method that has attributes to parse
        $route = Bank::getNamedRoute('Page View');
        $this->assertEquals('Page View', $route->getName());
        $this->assertEquals('/stuff/:int/', $route->getMatchString());
        $this->assertEquals(0, $route->getMaxParameters());
        $this->assertCount(1, $route->getActions());

        // Check that the bank can return a named link
        $this->assertEquals('/stuff/1234/', Bank::uri('Page View', [1234]) );

        // The named link should not allow more arguments than allowed
        $this->assertEquals('/stuff/1234/', Bank::uri('Page View', [1234, 3454]) );

        // Allow for a couple more parameters, check they take, and no more
        $route->setMaxParameters(2);
        $this->assertEquals('/stuff/1234/3454/2222/', Bank::uri('Page View', [1234, 3454, 2222]) );
        $this->assertEquals('/stuff/1234/3454/2222/', Bank::uri('Page View', [1234, 3454, 2222, 3333]) );

        $action = $route->getActions()[0];

        $this->assertEquals($controllerName, $action->getControllerClass() );
        $this->assertEquals('get_pageview', $action->getControllerMethod() );

        // Check that a private method does not create a route.
        $route = Bank::getNamedRoute('\Controller:get_nothing');
        $this->assertNull($route);

        // Methods that don't have one of the expected prefixes should be
        // ignored.
        $route = Bank::getNamedRoute('NonRoute');
        $this->assertNull($route);

        // Check that extra underscores turn into slashes from the action to the
        // match string
        $route = Bank::getNamedRoute('Page View Wide');
        $this->assertEquals('/tester/page/view/wide/', $route->getMatchString());

        $route = Bank::getNamedRoute('Page Index Root');
        $this->assertEquals('/tester/', $route->getMatchString());
    }

    /**
     * Test that method names with underscores break up into segments in the
     * link and spaces for the route name
     *
     */
    public function testMethodDirectories(): void
    {
        // Make sure all routes going into the bank are from here
        Bank::clearAllRoutes();
        $controllerName = '\Metrol\Tests\Controller\ActionCity';

        $refClass = new ReflectionClass($controllerName);

        $parser = new Load\Controller($refClass);
        $parser->run();

        $action = 'ActionCity Get Report Annual';

        $route = Bank::getNamedRoute($action);
        $this->assertEquals($action, $route->getName());

        $this->assertEquals('/tester/report/annual/', $route->getMatchString());
        $this->assertEquals('GET', $route->getHttpMethod());
        $this->assertEquals(0, $route->getMaxParameters());
    }

    /**
     * Test loading multiple controller classes with the parent loader
     *
     */
    public function testParentControllerLoader(): void
    {
        // Make sure all routes going into the bank are from here
        Bank::clearAllRoutes();

        $parentControllerName = '\Metrol\Tests\Controller';

        $parser = new Load\ControllerParent($parentControllerName);
        $parser->run();

        $action = 'ActionCity Get View';

        $route = Bank::getNamedRoute($action);
        $this->assertEquals($action, $route->getName());

        $this->assertEquals('/tester/view/', $route->getMatchString());
        $this->assertEquals('GET', $route->getHttpMethod());
        $this->assertEquals(0, $route->getMaxParameters());

        // Verify a route with a different HTTP method
        $action = 'ActionCity Updatestuff';
        $route = Bank::getNamedRoute($action);

        $this->assertEquals($action, $route->getName());
        $this->assertEquals('/tester/updatestuff/', $route->getMatchString());
        $this->assertEquals('POST', $route->getHttpMethod());

        // Pull in an action from the Extra\API controller
        $actionName = 'API Data Fetch';
        $route = Bank::getNamedRoute($actionName);
        $this->assertEquals($actionName, $route->getName());
        $this->assertEquals('/extra/api/datafetch/', $route->getMatchString());
        $this->assertEquals('/extra/api/datafetch/', Bank::uri($actionName));


        $this->assertTrue(true);
    }
}
