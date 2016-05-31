<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\Route;
use Metrol;

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
     * @const
     */
    const EXT_INI  = 'ini';
    const EXT_JSON = 'json';

    /**
     * File name being looked at for routes
     *
     * @var string
     */
    private $fileName;

    /**
     * Initialize the object
     *
     */
    public function __construct()
    {
        $this->fileName = null;
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
     * Initiates the configuration loader
     *
     * @return $this
     */
    public function run()
    {
        $ext    = $this->testFile();
        $parser = null;

        switch ( $ext )
        {
            case self::EXT_INI:
                $parser = new Load\Ini;
                break;

            case self::EXT_JSON:
                $parser = new Load\Json;
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
    private function testFile()
    {
        if ( $this->fileName == null )
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

        $ext = pathinfo($this->fileName, \PATHINFO_EXTENSION);
        $ext = strtolower($ext);

        switch ( $ext )
        {
            case self::EXT_INI:
                return self::EXT_INI;
                break;

            case self::EXT_JSON:
                return self::EXT_JSON;
                break;

            default:
                echo 'Unknown configuration file type<br>';
                echo PHP_EOL, 'Exiting...', PHP_EOL;
                exit;
        }

    }
}
