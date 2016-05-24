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
     * List of methods within a Controller that need to be called
     *
     * @var string[]
     */
    protected $methodSet;

    /**
     * Initializes the Action Definition
     *
     * @param string $action Take in a string with a "Class:Action" format to
     *                       simplify creating a single action Action.
     */
    public function __construct($action = null)
    {
        $this->controllerClass = '';
        $this->methodSet       = array();

        if ( $action !== null )
        {
            $this->setAction($action);
        }
    }

    /**
     * Looks for an action string that was passed into the constructor, and
     * attempts to put it into a class/method assignment.
     *
     * @param string $action
     */
    private function setAction($action)
    {
        if ( strpos($action, ':') === false )
        {
            return;
        }

        $action = preg_replace('/:{2,}/', ':', $action);

        if ( substr_count($action, ':') != 1 )
        {
            return;
        }

        list($class, $method) = explode(':', $action);

        $this->setClass($class)->addMethod($method);
    }

    /**
     * Sets the class to be instantiated
     *
     * @param string $className Name of the controller class
     *
     * @return $this
     */
    public function setClass($className)
    {
        $className = str_replace('/', '\\', $className);
        $className = str_replace('.', '\\', $className);
        $className = str_replace('_', '\\', $className);

        $this->controllerClass = $className;

        return $this;
    }

    /**
     * Puts a new method on the stack to be called
     *
     * @param string $method Name of the method in the Controller Class to call
     *
     * @return $this
     */
    public function addMethod($method)
    {
        if ( !in_array($method, $this->methodSet) )
        {
            $this->methodSet[] = $method;
        }
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

        if ( empty($this->methodSet) )
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
    public function getClass()
    {
        return $this->controllerClass;
    }

    /**
     * Provide the list of methods that need to be called within the Controller
     *
     * @return string[]
     */
    public function getMethods()
    {
        return $this->methodSet;
    }

}
