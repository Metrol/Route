<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
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
    private static array $routes = [];

    /**
     * Make a route deposit to the bank
     *
     */
    public static function addRoute(Metrol\Route $route): void
    {
        self::$routes[$route->getName()] = $route;
    }

    /**
     * Provide a URI string for the given named route
     *
     */
    static public function uri(string $routeName, array $args = null): string
    {
        $route = self::getNamedRoute($routeName);

        if ( is_null($route) )
        {
            return '';
        }

        $revRoute = new Reverse($route);

        if ( ! empty($args) )
        {
            $revRoute->addArgs($args);
        }

        return $revRoute->output();
    }

    /**
     * Find a route by name
     *
     */
    public static function getNamedRoute(string $routeName): Metrol\Route|null
    {
        if ( isset(self::$routes[ $routeName ]) )
        {
            return self::$routes[ $routeName ];
        }

        return null;
    }

    /**
     * Provide the URL for a named route with arguments
     *
     */
    public static function getNamedURL(string $routeName, mixed ...$args): string
    {
        $route = self::getNamedRoute($routeName);

        if ( is_null($route) )
        {
            return '';
        }

        $reverseRoute = $route->getReverse();

        foreach ( $args as $argument )
        {
            $reverseRoute->addArg($argument);
        }

        return $reverseRoute->output();
    }

    /**
     * Find a route for the specified Request
     *
     */
    public static function getRequestedRoute(Metrol\Route\Request $request): Metrol\Route|null
    {
        foreach ( array_reverse(self::$routes) as $route )
        {
            $matched = Metrol\Route\MatchRoute::check($request, $route);

            if ( $matched )
            {
                return $route;
            }
        }

        return null;
    }

    /**
     * Removes all routes from the bank, to start things up fresh.  Mostly just
     * need this for testing purposes.
     *
     */
    static public function clearAllRoutes(): void
    {
        self::$routes = [];
    }

    /**
     * Dump the routes into an array
     *
     */
    public static function dump(): array
    {
        $rtn = [];

        foreach ( array_reverse(self::$routes) as $route )
        {
            $actionList = [];

            foreach ( $route->getActions() as $action )
            {
                $actionList[] = $action->getControllerClass() . ':' .
                    $action->getControllerMethod();
            }

            $params = $route->getMaxParameters();

            if ( $params === null )
            {
                $params = 'null';
            }

            $rtn[] = [
                'routeName' => $route->getName(),
                'method'    => $route->getHttpMethod(),
                'match'     => $route->getMatchString(),
                'params'    => $params,
                'actions'   => $actionList
            ];
        }

        return $rtn;
    }

    /**
     * Provide the array of routes stored here
     *
     */
    public static function getAllRoutes(): array
    {
        return self::$routes;
    }

    /**
     * List out all the routes for diagnostic purposes in HTML format
     *
     */
    public static function dumpHTML(): string
    {
        $out = <<<HTML
<table>
    <thead>
        <tr>
            <td>Route Name</td>
            <td>HTTP</td>
            <td>Match</td>
            <td>Params</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>

HTML;

        foreach ( array_reverse(self::$routes) as $route )
        {
            $actionList = [];

            foreach ( $route->getActions() as $action )
            {
                $actionList[] = $action->getControllerClass().':'.
                    $action->getControllerMethod();
            }

            $actionCell = implode('<br>', $actionList);
            $paramCell = $route->getMaxParameters();

            if ( $paramCell === null )
            {
                $paramCell = 'null';
            }

            $out .= <<<HTML
        <tr>
            <td>{$route->getName()}</td>
            <td>{$route->getHttpMethod()}</td>
            <td>{$route->getMatchString()}</td>
            <td>$paramCell</td>
            <td>$actionCell</td>
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
