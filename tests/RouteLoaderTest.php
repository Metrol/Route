<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

use \Metrol\Route;
use \Metrol\Route\Load;
use \Metrol\Route\Bank;

require 'Controller.php';

/**
 * Verifies that routes can be loaded from the supported methods of this
 * library
 *
 */
class RouteLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Configuration coming from an INI file
     *
     * @const string
     */
    const INI_CONF = 'routes.ini';

    /**
     * Load some basic routes from an INI file
     *
     */
    public function testLoadingFromINIFile()
    {
        $confFile = dirname(__FILE__) . '/' . self::INI_CONF;

        $parser = new Load\Ini;
        $parser->setFileName($confFile)->run();

        // Now try to pull some routes out of the Bank
        $route = Bank::getNamedRoute('Stuff View');

        $this->assertEquals('Stuff View', $route->getName());
        $this->assertEquals('GET', $route->getHttpMethod());
        $this->assertEquals('/stuff/:int/', $route->getMatchString());
        $this->assertEquals(0, $route->getMaxParameters());

        $this->assertCount(1, $route->getActions());
        $action = $route->getActions()[0];

        $this->assertEquals('\Metrol\Controller\Stuff', $action->getControllerClass());
        $this->assertEquals('view', $action->getControllerMethod());


        // A more complex route with 3 actions.
        $route = Bank::getNamedRoute('Stuff Delete');
        $this->assertEquals('Stuff Delete', $route->getName());
        $this->assertEquals('DELETE', $route->getHttpMethod());
        $this->assertEquals('/stuff/:int/', $route->getMatchString());
        $this->assertNull($route->getMaxParameters());

        $this->assertCount(3, $route->getActions());

        $action = $route->getActions()[0];
        $this->assertEquals('\Stuff', $action->getControllerClass());
        $this->assertEquals('deleteStuff', $action->getControllerMethod());

        $action = $route->getActions()[1];
        $this->assertEquals('\Metrol\Controller\Log', $action->getControllerClass());
        $this->assertEquals('stuffDelete', $action->getControllerMethod());
    }

    /**
     * Test loading in a routes from a Controller class
     *
     */
    public function testLoadingFromAControllerClass()
    {
        $parser = new Load\Controller;
        $parser->setControllerName('\Controller')->run();

        $route = Bank::getNamedRoute('\Controller:get_view');

        $this->assertEquals('\Controller:get_view', $route->getName());
        $this->assertEquals('/tester/view/', $route->getMatchString());
        $this->assertEquals('GET', $route->getHttpMethod());
        $this->assertNull($route->getMaxParameters());

        // Verify a route with a different HTTP method
        $route = Bank::getNamedRoute('\Controller:post_updatestuff');
        $this->assertEquals('\Controller:post_updatestuff', $route->getName());
        $this->assertEquals('/tester/updatestuff/', $route->getMatchString());
        $this->assertEquals('POST', $route->getHttpMethod());

        // Run some tests on a method that has attributes to parse
        $route = Bank::getNamedRoute('Page View');
        $this->assertEquals('Page View', $route->getName());
        $this->assertEquals('/stuff/:int/', $route->getMatchString());
        $this->assertEquals(0, $route->getMaxParameters());
        $this->assertCount(1, $route->getActions());

        $action = $route->getActions()[0];

        $this->assertEquals('\Controller', $action->getControllerClass() );
        $this->assertEquals('get_pageview', $action->getControllerMethod() );

        // Check that a private method does not create a route.
        $route = Bank::getNamedRoute('\Controller:get_nothing');
        $this->assertNull($route);

        // Methods that don't have one of the expected prefixes should be
        // ignored.
        $route = Bank::getNamedRoute('NonRoute');
        $this->assertNull($route);
    }
}
