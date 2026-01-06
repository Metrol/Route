<?php

namespace Metrol\Route\Load\Controller;

use Metrol\Route;
use Metrol\Route\{Action, Bank};
use Metrol\Route\Attributes\Route as AttrRoute;
use ReflectionClass;
use ReflectionAttribute;
use ReflectionMethod;

class Attributes
{
    /**
     * The reflection object for the controller to parse through
     *
     */
    private ReflectionClass $reflect;

    /**
     * Instantiate the Controller Attribute loader
     *
     */
    public function __construct(ReflectionClass $reflect)
    {
        $this->reflect = $reflect;
    }

    /**
     * Add routes based on the attribute of each action
     *
     */
    public function run(): void
    {
        $methods = $this->reflect->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method)
        {
            $attributes = $method->getAttributes(AttrRoute::class,
                ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attrib)
            {
                /**
                 * @var AttrRoute $attrRoute
                 */
                $attrRoute = $attrib->newInstance();

                $routeName = $this->getRouteName($method, $attrRoute);
                $route = new Route($routeName);

                $route->setMatchString($this->getMatch($method, $attrRoute));
                $route->addAction($this->getAction($method));
                $route->setHttpMethod($attrRoute->method);
                $route->setMaxParameters($attrRoute->maxParam);

                Bank::addRoute($route);
            }
        }
    }

    /**
     * Provide either the default name built on the controller/action or use
     * the provided name from the docblock tag.
     *
     */
    private function getRouteName(ReflectionMethod $method, AttrRoute $attrRoute): string
    {
        if (!empty($attrRoute->name))
        {
            return $attrRoute->name;
        }

        $controllerName = $this->reflect->name;
        $parentControllerName = $this->reflect->getParentClass()->name;
        $parNameLen = strlen($parentControllerName) + 1;

        $linkPrefix = substr($controllerName, $parNameLen);
        $linkPrefix = str_replace('\\', ' ', $linkPrefix);

        $methodName = $method->getName();
        $httpMethod = strtolower($attrRoute->method);

        $methodName = str_replace('_', ' ', $methodName);

        $routeName = ucwords($linkPrefix . ' ' . $httpMethod. ' ' . $methodName);

        return trim($routeName);
    }

    /**
     * Provide either the default name built on the controller/action or use
     * the provided name from the docblock tag.
     *
     */
    private function getMatch(ReflectionMethod $method, AttrRoute $attrRoute): string
    {
        if ( ! empty($attrRoute->match) )
        {
            return $attrRoute->match;
        }

        $matchPre = $this->getMatchPrefix();

        $methodName = $method->getName();

        if ( empty($methodName) )
        {
            $match = $matchPre;
        }
        else
        {
            $methodMatch = str_replace('_', '/', $methodName);
            $match       = $matchPre . $methodMatch;
        }

        if ( ! str_ends_with($match, '/') )
        {
            $match .=  '/';
        }

        // Add argument types if specified
        if ( ! empty($attrRoute->args) )
        {
            $argString = implode('/', $attrRoute->args);

            $match .= $argString . '/';
        }

        return $match;
    }

    /**
     * Assemble the action to run
     *
     */
    private function getAction(ReflectionMethod $method): Action
    {
        $action = new Action;
        $action->setControllerClass($this->reflect->name);
        $action->setControllerMethod($method->getName());

        return $action;
    }

    /**
     * Provide the prefix of the match string based on either the controller
     * name or a class constant in the controller named MATCH_PREFIX.
     *
     */
    private function getMatchPrefix(): string
    {
        if ( $this->reflect->hasConstant('MATCH_PREFIX') )
        {
            $class = $this->reflect->name;

            return $class::MATCH_PREFIX;
        }

        // Otherwise, calculate what the prefix should be based on the class
        // itself.
        $controllerName = $this->reflect->name;
        $parentControllerName = $this->reflect->getParentClass()->name;
        $parNameLen = strlen($parentControllerName) + 1;

        $linkPrefix = substr($controllerName, $parNameLen);
        $linkPrefix = str_replace('\\', '/', $linkPrefix);
        $linkPrefix = '/' . $linkPrefix . '/';

        return strtolower($linkPrefix);
    }
}
