<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

use \Metrol\Route;
use \Metrol\Route\Action;

/**
 * A little dedicated testing for the Action object
 *
 */
class RouteActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that an action can be properly passed into the constructor
     *
     */
    public function testConstructor()
    {
        $act = new Action('TestClass:testMethodToCall');

        $this->assertEquals('TestClass', $act->getClass());
        $method = $act->getMethods()[0];
        $this->assertEquals('testMethodToCall', $method);

        // Make sure this doesn't work without the proper delimeter
        $act = new Action('TestClass-testMethodToCall');
        $this->assertEquals('', $act->getClass());

        // Multiple delimeters should work.
        $act = new Action('TestClass::testMethodToCall');
        $this->assertEquals('TestClass', $act->getClass());
        $this->assertEquals('testMethodToCall', $act->getMethods()[0]);

        // Multiple delimeters should work. Try an odd number
        $act = new Action('TestClass:::testMethodToCall');
        $this->assertEquals('TestClass', $act->getClass());
        $this->assertEquals('testMethodToCall', $act->getMethods()[0]);
    }
}
