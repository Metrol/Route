<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\Route\Load;
use Metrol\Route;
use Metrol\Route\Bank;
use Metrol\Route\Action;

/**
 * Called by the Route\Load object to parse an INI file and create routes to
 * go into the Bank.
 *
 * This object should not be called directly.  Use Route\Load to bring this into
 * play.
 *
 */
class Ini
{
    /**
     * The INI key to look for the prefix for the controller in use
     *
     * @const
     */
    const KEY_ACTION_PREFIX = 'actionPrefix';
    const KEY_MATCH         = 'match';
    const KEY_METHOD        = 'method';
    const KEY_ACTION        = 'action';
    const KEY_MAX_PARAMS    = 'params';

    /**
     * File name being looked at for routes
     *
     * @var string
     */
    private $fileName;

    /**
     * The parsed values
     *
     * @var array
     */
    private $parsed;

    /**
     * Initialize the object
     *
     */
    public function __construct()
    {
        $this->fileName = null;
        $this->parsed   = null;
    }

    /**
     *
     * @param string $fileName
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * There is already an assumption that the validity of the file name has
     * already been checked before this is even attempted.
     *
     */
    public function run()
    {
        $this->parsed = parse_ini_file($this->fileName, true);

        if ( $this->parsed === false )
        {
            echo 'Parsing INI file ', htmlentities($this->fileName), ' failed';
            echo '<br>', PHP_EOL;
            echo 'Exiting...', PHP_EOL;
            exit;
        }

        $this->buildRoutes();
    }

    /**
     * Takes the parsed information, builds out the routes, and adds those
     * routes to the Bank to be looked up later.
     *
     */
    private function buildRoutes()
    {
        $actionPrefix = $this->getActionPrefix();

        foreach ( $this->parsed as $routeName => $routeInfo )
        {
            $route = new Route($routeName);

            $this->setMatch($route, $routeInfo);
            $this->setMethod($route, $routeInfo);
            $this->setMaxParams($route, $routeInfo);
            $this->setActions($route, $routeInfo, $actionPrefix);

            Bank::addRoute($route);
        }
    }

    /**
     * Fetch the Action Prefix if it exists.
     *
     * @return string
     */
    private function getActionPrefix()
    {
        $actionPrefix = ''; // Set to the prefix of the controller, if available

        if ( isset($this->parsed[self::KEY_ACTION_PREFIX]) )
        {
            $actionPrefix = $this->parsed[self::KEY_ACTION_PREFIX];
            unset($this->parsed[self::KEY_ACTION_PREFIX]);
        }

        return $actionPrefix;
    }

    /**
     *
     * @param Route $route
     * @param array $routeInfo
     */
    private function setMatch(Route $route, array $routeInfo)
    {
        if ( isset($routeInfo[self::KEY_MATCH]) )
        {
            $route->setMatchString($routeInfo[self::KEY_MATCH]);
        }
    }

    /**
     *
     * @param Route $route
     * @param array $routeInfo
     */
    private function setMethod(Route $route, array $routeInfo)
    {
        if ( isset($routeInfo[self::KEY_METHOD]) )
        {
            $route->setHttpMethod($routeInfo[self::KEY_METHOD]);
        }
    }

    /**
     *
     * @param Route $route
     * @param array $routeInfo
     */
    private function setMaxParams(Route $route, array $routeInfo)
    {
        if ( isset($routeInfo[self::KEY_MAX_PARAMS]) )
        {
            $route->setMaxParameters($routeInfo[self::KEY_MAX_PARAMS]);
        }
    }

    /**
     *
     * @param Route  $route
     * @param array  $routeInfo
     * @param string $actionPrefix   Action Controller prefix
     */
    private function setActions(Route $route, array $routeInfo, $actionPrefix)
    {
        if ( ! isset($routeInfo[self::KEY_ACTION]) )
        {
            return;
        }

        if ( is_array($routeInfo[self::KEY_ACTION]) )
        {
            $actions = $routeInfo[self::KEY_ACTION];
        }
        else
        {
            $actions = array( $routeInfo[self::KEY_ACTION] );
        }

        foreach ( $actions as $actionString )
        {
            if ( substr($actionString, 0, 1) != '\\' )
            {
                $actionString = $actionPrefix.'\\'.$actionString;
            }

            $action = new Action($actionString);

            $route->addAction($action);
        }
    }
}
