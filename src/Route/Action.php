<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
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
     */
    protected string $controllerClass = '';

    /**
     * A method within a Controller that needs to be called
     *
     */
    protected string $method = '';

    /**
     * Initializes the Action Definition
     *
     * @param string|null $action Take in a string with a "Class:Action" format to
     *                       simplify creating a single Action.
     */
    public function __construct(?string $action = null)
    {
        $this->controllerClass = '';
        $this->method          = '';

        if ( $action !== null )
        {
            $this->setAction($action);
        }
    }

    /**
     * Takes in a colon delimited string and separates it out to the Controller
     * Method pair that makes up an action.
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction(string $action): static
    {
        if ( ! str_contains($action, ':') )
        {
            return $this;
        }

        $action = preg_replace('/:{2,}/', ':', $action);

        if ( substr_count($action, ':') != 1 )
        {
            return $this;
        }

        [$class, $method] = explode(':', $action);

        $this->setControllerClass($class)
            ->setControllerMethod($method);

        return $this;
    }

    /**
     * Sets the class to be instantiated
     *
     */
    public function setControllerClass(string $className): static
    {
        // Strip out any spaces
        $className = preg_replace('/\s+/', '', $className);

        // Just in case name spaces came in with old style delimiters
        $className = str_replace('/', '\\', $className);
        $className = str_replace('.', '\\', $className);
        $className = str_replace('_', '\\', $className);

        // Insure no duplicate delimiters
        $className = preg_replace('~\\\{2,}~', '\\', $className);

        // Make sure we've got a leading backslash.  All controller classes
        // should be fully qualified.
        if ( !str_starts_with($className, '\\') )
        {
            $className = '\\'.$className;
        }

        // Strip off any trailing backslashes that may have worked their way in
        // here.
        if ( str_ends_with($className, '\\') )
        {
            $className = substr($className, 0, -1);
        }

        $this->controllerClass = $className;

        return $this;
    }

    /**
     * Assigns the method to be called
     *
     */
    public function setControllerMethod(string $method): static
    {
        // Make sure we didn't get a method that looks like a function call
        if ( str_contains($method, '(') )
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
     */
    public function isReady(): bool
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
     */
    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    /**
     * Provide the list of methods that need to be called within the Controller
     *
     */
    public function getControllerMethod(): string
    {
        return $this->method;
    }
}
