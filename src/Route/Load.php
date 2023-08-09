<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Route;

use const PATHINFO_EXTENSION;

/**
 * Acts as a front end to various route loader objects that will populate the
 * route bank.
 *
 */
class Load
{
    /**
     * File extensions that are supported by this object.
     *
     */
    const EXT_INI  = 'ini';
    const EXT_JSON = 'json';

    /**
     * File name being looked at for routes
     *
     */
    private string $fileName = '';

    /**
     * Initialize the route file loader
     *
     */
    public function __construct()
    {
    }

    /**
     * Specify the file to look up routes in
     *
     */
    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Initiates the configuration loader
     *
     */
    public function run(): static
    {
        $ext    = $this->testFile();
        $parser = null;

        switch ( $ext )
        {
            case self::EXT_INI:
                $parser = new Load\Ini;
                break;

            case self::EXT_JSON:
                // @todo Future support for JSON coming some day
                // $parser = new Load\Json;
                break;
        }

        $parser->setFileName($this->fileName)
            ->run();

        return $this;
    }

    /**
     * Runs some basic checks on the file before trying to parse anything.
     * Just kills all execution if unable to read the configuration file.  This
     * should not be a subtle error.
     *
     * @return string The file extension found
     */
    private function testFile(): string
    {
        if ( empty($this->fileName) )
        {
            echo 'Your attempt to load a route has failed due to no file being ',
                'specified.<br>', PHP_EOL, 'Exiting....', PHP_EOL;

            exit;
        }

        if ( ! is_readable($this->fileName) )
        {
            echo 'Your attempt to load a route from ';
            echo htmlentities($this->fileName);
            echo ' has failed either because it does not exist or is not ';
            echo 'able to be read.  Check your settings and permissions.<br>';
            echo PHP_EOL, 'Exiting...', PHP_EOL;

            exit;
        }

        $ext = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $ext = strtolower($ext);

        switch ( $ext )
        {
            case self::EXT_INI:
                return self::EXT_INI;

            case self::EXT_JSON:
                return self::EXT_JSON;

            default:
                echo 'Unknown configuration file type<br>';
                echo PHP_EOL, 'Exiting...', PHP_EOL;
                exit;
        }
    }
}
