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
 * Verifies that routes can be loaded from INI files
 *
 */
class LoaderIniTest extends TestCase
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
        $this->assertEquals(0, $route->getMaxParameters());

        $this->assertCount(3, $route->getActions());

        $action = $route->getActions()[0];
        $this->assertEquals('\Stuff', $action->getControllerClass());
        $this->assertEquals('deleteStuff', $action->getControllerMethod());

        $action = $route->getActions()[1];
        $this->assertEquals('\Metrol\Controller\Log', $action->getControllerClass());
        $this->assertEquals('stuffDelete', $action->getControllerMethod());
    }
}
