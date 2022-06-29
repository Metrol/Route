<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\Tests;

use PHPUnit\Framework\TestCase;
use Metrol\Route\Request;

/**
 * The Route library has it's own little request parser.  Verify some of what
 * it should be doing.
 *
 */
class RouteRequestTest extends TestCase
{
    /**
     * Make sure the URI setter works properly, and strips off any GET arguments
     * before storing.
     *
     */
    public function testSetURI()
    {
        // Test that the URI is automatically loaded from the $_SERVER global
        $testURI = 'http://www.example.com/sna/fu/';
        $_SERVER['REQUEST_URI'] = $testURI;
        $req = new Request;
        $this->assertEquals($testURI, $req->getUri() );

        // Test setting the URI manually
        $testURI = 'http://www.example.com/foo/bar/';
        $req->setUri($testURI);
        $this->assertEquals($testURI, $req->getUri() );

        // Make sure GET arguments are stripped off
        $testURIGet = 'http://www.example.com/foo/bar/?id=123';
        $req->setUri($testURIGet);
        $this->assertEquals($testURI, $req->getUri() );
    }

    /**
     * Verify the http method is found, processed, and is able to be manually
     * set and fetched.
     *
     */
    public function testHttpMethod()
    {
        // The default method should be GET
        $req = new Request;
        $this->assertEquals('GET', $req->getHttpMethod() );

        // Make sure the method loads correctly from the $_SERVER global
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $req = new Request;
        $this->assertEquals('POST', $req->getHttpMethod() );

        // If an unknown method is attempted, no change to the object
        $req->setHttpMethod('blah');
        $this->assertEquals('POST', $req->getHttpMethod() );

        // Now check for a valid method, and that it is converted to upper case
        $req->setHttpMethod('delete');
        $this->assertEquals('DELETE', $req->getHttpMethod() );

        // Now check for a valid method, and that it is converted to upper case
        $req->setHttpMethod('pUt');
        $this->assertEquals('PUT', $req->getHttpMethod() );
    }
}
