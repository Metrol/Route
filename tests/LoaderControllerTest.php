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
    public function testLoadingFromAControllerClass()
    {
        $this->assertTrue(true);

        $controllerName = '\Metrol\Tests\Controller\ActionCity';

        $parser = new Load\Controller;
        $parser->setControllerName($controllerName)->run();

        $action = $controllerName . ':get_view';

        $route = Bank::getNamedRoute($action);
        $this->assertEquals($action, $route->getName());

        $this->assertEquals('/tester/view/', $route->getMatchString());
        $this->assertEquals('GET', $route->getHttpMethod());
        $this->assertEquals(0, $route->getMaxParameters());

        // Verify a route with a different HTTP method
        $action = $controllerName . ':post_updatestuff';
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
}
