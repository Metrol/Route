<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Route\Load;
use ReflectionClass;

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
        $docBlockLoad = new Controller\DocBlock($this->reflect);
        $docBlockLoad->run();
    }
}
