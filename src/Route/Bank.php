<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\Route;

use Metrol;

/**
 * Maintains the list of routes a site has registered.
 *
 */
class Bank
{
    /**
     * List of route objects
     *
     * @var Metrol\Route[]
     */
    private static $routes = array();

    /**
     * Make a route deposit to the bank
     *
     * @param Metrol\Route $route
     */
    public static function addRoute(Metrol\Route $route)
    {
        self::$routes[$route->getName()] = $route;
    }

    /**
     * Find a route by name
     *
     * @param string $routeName
     *
     * @return Metrol\Route|null
     */
    public static function getNamedRoute($routeName)
    {
        if ( isset(self::$routes[ $routeName ]) )
        {
            return self::$routes[ $routeName ];
        }

        return null;
    }

    /**
     * Find a route for the specified Request
     *
     * @param Metrol\Route\Request $request
     *
     * @return Metrol\Route|null
     */
    public static function getRequestedRoute(Metrol\Route\Request $request)
    {
        foreach ( array_reverse(self::$routes) as $route )
        {
            $matched = Metrol\Route\Match::check($request, $route);

            if ( $matched )
            {
                return $route;
            }
        }

        return null;
    }

    /**
     * List out all the routes for diagnostic purposes in HTML format
     *
     * @return string
     */
    public static function dumpHTML()
    {
        $out = <<<HTML
<table>
    <thead>
        <tr>
            <td>Route Name</td>
            <td>HTTP Method</td>
            <td>Match String</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>

HTML;

        foreach ( self::$routes as $route )
        {
            $actionCel = implode('<br>', $route->getActions() );

            $out .= <<<HTML
        <tr>
            <td>{$route->getName()}</td>
            <td>{$route->getHttpMethod()}</td>
            <td>{$route->getMatchString()}</td>
            <td>{$actionCel}</td>
        </tr>
        
HTML;
        }

        $out .= <<<HTML
    </tbody>
</table>

HTML;

        return $out;
    }
}
