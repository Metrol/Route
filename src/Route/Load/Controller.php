<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Route\Load;
use Metrol\Route\Attributes\Route as AttrRoute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

/**
 * Parses the method names and phpDoc blocks for route information to create
 * routes and add them to the bank
 *
 */
class Controller
{
    private ReflectionClass $reflect;

    /**
     * Initialize the object
     *
     */
    public function __construct(ReflectionClass $reflectionClassObject)
    {
        $this->reflect = $reflectionClassObject;
    }

    /**
     * There is already an assumption that the validity of the file name has
     * already been checked before this is even attempted.
     *
     */
    public function run(): void
    {
        $methods = $this->reflect->getMethods(ReflectionMethod::IS_PUBLIC);

        $parseAttributes = false;

        foreach ( $methods as $method )
        {
            $attributes = $method->getAttributes(AttrRoute::class,
                ReflectionAttribute::IS_INSTANCEOF);

            if (count($attributes) > 0)
            {
                $parseAttributes = true;
            }
        }

        if ( $parseAttributes )
        {
            $attrLoad = new Controller\Attributes($this->reflect);
            $attrLoad->run();

            return;
        }

        $docBlockLoad = new Controller\DocBlock($this->reflect);
        $docBlockLoad->run();
    }
}
