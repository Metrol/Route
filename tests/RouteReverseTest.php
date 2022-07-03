<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

use PHPUnit\Framework\TestCase;
use \Metrol\Route;
use \Metrol\Route\Reverse;

/**
 * Verifies the ability of the Route\Reverse object to produce a correct URL
 * from the Route information and supplied arguments
 *
 */
class RouteReverseTest extends TestCase
{
    /**
     * Create a test route, get a reversal on it to produce a URL
     *
     */
    public function testRouteReversal()
    {
        $route = new Route('Cool Route');
        $route->setMatchString('/view/:int/stuff/:str/other');

        $reverse = new Reverse($route);

        // Before any arguments are put in place.
        $this->assertEquals('/view/:int/stuff/:str/other/', $reverse->output());

        // Add a couple of arguments, one that will require encoding
        $reverse->addArg(123)->addArg('How dy');

        $this->assertEquals('/view/123/stuff/How+dy/other/', $reverse->output());

        // More arguments
        $reverse->addArg('tack to end');

        $this->assertEquals('/view/123/stuff/How+dy/other/tack+to+end/', $reverse->output());
    }

    /**
     * Same kind of test, only this time adding stuff to the GET string
     *
     */
    public function testRouteGetString()
    {
        $route = new Route('Cool Route');
        $route->setMatchString('/view/:int/');

        // This time, pull out the Reverse object from the route itself
        $reverse = $route->getReverse();

        $reverse->addArg(456)
            ->addGet('type', 'saucy')
            ->addGet('format', 52);

        $this->assertEquals('/view/456/?type=saucy&format=52', $reverse->output());
    }

    /**
     * Make sure the max parameters of the route is respected, or the URL won't
     * match if trying to route back.
     *
     */
    public function testMaxParameters()
    {
        $route = new Route('Cool Route');
        $route->setMatchString('/view/:int/');
        $route->setMaxParameters(2);
        $reverse = $route->getReverse();

        $reverse->addArg(456);

        // Only filling in the hinted parameter
        $this->assertEquals('/view/456/', $reverse->output());

        $reverse->addArgs(['one', 'two']);

        // This should be the maximum arguments to pass in
        $this->assertEquals('/view/456/one/two/', $reverse->output());

        $reverse->addArg('three')->addArg('four');

        // Should not have added anything
        $this->assertEquals('/view/456/one/two/', $reverse->output());
    }

    /**
     * Testing the ability to reset the arguments for the reverse route
     *
     */
    public function testClearArguments()
    {
        $route = new Route('Cool Route');
        $route->setMatchString('/view/:int/');
        $reverse = $route->getReverse();

        $reverse->addArgs([123, 456, 789]);

        $this->assertEquals('/view/123/456/789/', $reverse->output());

        $reverse->clearArgs()->addArg('555');

        $this->assertEquals('/view/555/', $reverse->output());

        // Now to test the GET arguments
        $reverse->addGet('foo', 'bar')
            ->addGet('sna', 'fu');

        // See that the GET string has been appended
        $this->assertEquals('/view/555/?foo=bar&sna=fu', $reverse->output());

        $reverse->addGet('foo', 'baz');

        // The addGet will replace the keyed value rather than append.
        $this->assertEquals('/view/555/?foo=baz&sna=fu', $reverse->output());

        $reverse->clearGetArgs();

        // All of the GET string is now removed, ready for new data
        $this->assertEquals('/view/555/', $reverse->output());
    }
}
