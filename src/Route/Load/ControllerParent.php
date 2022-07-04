<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Route\Load;

use ReflectionClass;
use ReflectionException;

/**
 * Takes a parent controller in as a starting point to search for other
 * controllers with actions.  From these, actions will be added to the
 * routing table.
 *
 * It is assumed that the parent controller has no actions.
 * Child controllers must be within the namespace of the parent, as well as
 * extend from the parent controller to be valid to look at.
 *
 */
class ControllerParent
{
    /**
     * Extension used for PHP files
     *
     */
    const PHP_EXT = '.php';

    /**
     * The controller class name in question
     *
     */
    private string $parentControllerName;

    /**
     * Listing of the names of all the child controllers
     *
     */
    private array $controllerSet;

    /**
     * Instantiate the Parent Controller Route Loader
     *
     */
    public function __construct(string $parentControllerName)
    {
        $this->parentControllerName = $parentControllerName;
    }

    /**
     * Start the process of getting all the class names of the child
     * controllers from the parent.
     *
     */
    public function run(): void
    {
        $this->assembleFiles();

        $this->createRoutes();
    }

    /**
     * Fetch all the controller names that are a child of the parent controller
     *
     */
    private function assembleFiles(): void
    {
        try
        {
            $ref = new ReflectionClass($this->parentControllerName);
        }
        catch ( ReflectionException )
        {
            // If looking up the parent controller didn't work, terminate
            // everything so the developer can fix the problem;
            echo 'Parent controller not found', PHP_EOL;
            echo 'Looking for class: ' . $this->parentControllerName;

            exit;
        }

        $scanDir = $ref->getFileName();

        $extPos = strpos($scanDir, self::PHP_EXT);
        $scanDir = substr($scanDir, 0, $extPos) . '/';

        $parentControllerSuffix = $this->getClassSuffix($this->parentControllerName);

        foreach ( $this->getAllFilesListing($scanDir) as $scanFile )
        {
            if ( ! str_contains($scanFile, '.php') )
            {
                continue;
            }

            $rootContPos = strrpos($scanFile, $parentControllerSuffix);
            $contFile = str_replace($parentControllerSuffix, '', $scanFile);
            $contFile = str_replace('.php', '', $contFile);

            $contClass = substr($contFile, $rootContPos);
            $contClass = str_replace('/', '\\', $contClass);
            $contClass = $this->parentControllerName . $contClass;

            $this->controllerSet[] = $contClass;
        }
    }

    /**
     * Pass each of the controller names into the Load Controller class and
     * have that fill in the routing table.
     *
     */
    private function createRoutes(): void
    {
        foreach ( $this->controllerSet as $controllerName )
        {
            try
            {
                $contReflect = new ReflectionClass($controllerName);
            }
            catch ( ReflectionException )
            {
                continue;
            }

            if ( ! $contReflect->isSubclassOf($this->parentControllerName)  )
            {
                continue;
            }

            $controllerLoader = new Controller;
            $controllerLoader
                ->setControllerName($controllerName)
                ->setMatchPrefix($this->getControllerLinkPrefix($controllerName))
                ->run();
        }
    }

    /**
     * Figure out the link prefix for the controller name
     *
     */
    private function getControllerLinkPrefix( string $controllerName ): string
    {
        $parNameLen = strlen($this->parentControllerName) + 1;

        $linkPrefix = substr($controllerName, $parNameLen);
        $linkPrefix = str_replace('\\', '/', $linkPrefix);
        $linkPrefix = '/' . $linkPrefix . '/';

        return strtolower($linkPrefix);
    }

    /**
     * A file utility to provide all the files under all the directories from
     * the given root directory
     *
     */
    private function getAllFilesListing(string $rootDirector): array
    {
        $fileList = array_diff(scandir($rootDirector), ['.', '..']);

        foreach ($fileList as &$item)
        {
            $item = $rootDirector . $item;
        }

        unset($item);

        foreach ($fileList as $item)
        {
            if ( is_dir($item) )
            {
                $fileList = array_merge($fileList,
                    $this->getAllFilesListing($item . DIRECTORY_SEPARATOR));
            }
        }

        return $fileList;
    }

    /**
     * Provide the suffix, or last segment, of a class name
     *
     */
    private function getClassSuffix(string $className): string
    {
        $parts = explode('\\', $className);
        $parts = array_reverse($parts);

        return $parts[0];
    }
}
