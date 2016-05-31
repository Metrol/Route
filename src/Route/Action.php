<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\Route;

/**
 * Describes an action that a route is registered for
 *
 */
class Action
{
    /**
     * The class name of the invokable Controller
     *
     * @var string
     */
    protected $controllerClass;

    /**
     * A method within a Controller that needs to be called
     *
     * @var string
     */
    protected $method;

    /**
     * Initializes the Action Definition
     *
     * @param string $action Take in a string with a "Class:Action" format to
     *                       simplify creating a single action Action.
     */
    public function __construct($action = null)
    {
        $this->controllerClass = '';
        $this->method          = '';

        if ( $action !== null )
        {
            $this->setAction($action);
        }
    }

    /**
     * Takes in a colon delimited string and seperates it out to the Controller/
     * Method pair that makes up an action.
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        if ( strpos($action, ':') === false )
        {
            return $this;
        }

        $action = preg_replace('/:{2,}/', ':', $action);

        if ( substr_count($action, ':') != 1 )
        {
            return $this;
        }

        list($class, $method) = explode(':', $action);

        $this->setControllerClass($class)
            ->setControllerMethod($method);

        return $this;
    }

    /**
     * Sets the class to be instantiated
     *
     * @param string $className Name of the controller class
     *
     * @return $this
     */
    public function setControllerClass($className)
    {
        // Strip out any spaces
        $className = preg_replace('/\s+/', '', $className);

        // Just in case name spaces came in with old style delimiters
        $className = str_replace('/', '\\', $className);
        $className = str_replace('.', '\\', $className);
        $className = str_replace('_', '\\', $className);

        // Insure no duplicate delimiters
        $className = preg_replace('~\\\{2,}~', '\\', $className);

        // Make sure we've got a leading back slash.  All controller classes
        // should be fully qualified.
        if ( substr($className, 0, 1) != '\\' )
        {
            $className = '\\'.$className;
        }

        // Strip off any trailing backslashes that may have worked their way in
        // here.
        if ( substr($className, -1) == '\\' )
        {
            $className = substr($className, 0, -1);
        }

        $this->controllerClass = $className;

        return $this;
    }

    /**
     * Assigns the method to be called
     *
     * @param string $method Name of the method in the Controller Class to call
     *
     * @return $this
     */
    public function setControllerMethod($method)
    {
        // Make sure we didn't get a method that looks like a function call
        if ( strpos($method, '(') !== false )
        {
            $method = substr($method, 0, strpos($method, '('));
        }

        // Any spaces to be converted to underscores
        $method = preg_replace('/\s+/', ' ', $method);
        $method = preg_replace('/\s+/', '_', $method);

        $this->method = $method;

        return $this;
    }

    /**
     * Determines if there is a controller name and at least one method
     * specified.
     *
     * @return boolean
     */
    public function isReady()
    {
        $rtn = true;

        if ( empty($this->controllerClass) )
        {
            $rtn = false;
        }

        if ( empty($this->method) )
        {
            $rtn = false;
        }

        return $rtn;
    }

    /**
     * Provide the class name of the controller to instantiate
     *
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * Provide the list of methods that need to be called within the Controller
     *
     * @return string
     */
    public function getControllerMethod()
    {
        return $this->method;
    }
}
