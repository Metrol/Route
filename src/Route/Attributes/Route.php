<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */
namespace Metrol\Route\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    /**
     * Hints to look for in the match string
     *
     */
    const string HINT_INT = ':int';
    const string HINT_NUM = ':num';
    const string HINT_STR = ':str';

    /**
     * GET should be used for a request to read data
     *
     */
    const string GET = 'GET';

    /**
     * POST should be used to create a new record
     *
     */
    const string POST = 'POST';

    /**
     * PUT is to Update or Replace information
     *
     */
    const string PUT = 'PUT';

    /**
     * DELETE requests information be removed
     *
     */
    const string DELETE = 'DELETE';

    /**
     * List of all the allowed methods that can be processed here
     *
     */
    private const array METHOD_REF = [
        self::GET,
        self::POST,
        self::PUT,
        self::DELETE
    ];

    /**
     * List of the allowed type hints
     *
     */
    private const array TYPE_HINT_REF = [
        self::HINT_STR,
        self::HINT_INT,
        self::HINT_NUM
    ];

    public string $match    = '';
    public string $method   = self::GET;
    public string $name     = '';
    public int    $maxParam = 0;

    /**
     * Appends arguments to the end of the URL
     *
     */
    public array $args = [];

    public function __construct(?string $match    = null,
                                string  $method   = self::GET,
                                ?string $name     = null,
                                ?int    $maxParam = null,
                                ?array  $args     = null
                                )
    {
        if ( ! is_null($match) )
        {
            $this->match = $match;
        }

        if ( in_array(strtoupper($method), self::METHOD_REF) )
        {
            $this->method = strtoupper($method);
        }

        if ( ! is_null($name) )
        {
            $this->name = $name;
        }

        if ( ! is_null($maxParam) )
        {
            $this->maxParam = $maxParam;
        }

        if ( ! is_null($args) )
        {
            foreach ( $args as $typeHint )
            {
                if ( in_array( strtolower($typeHint), self::TYPE_HINT_REF) )
                {
                    $this->args[] = $typeHint;
                }
            }
        }
    }
}
