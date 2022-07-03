<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol;

/**
 * The object that handles what will come back to the request
 *
 */
class Response
{
    /**
     * Common HTTP Status codes
     *
     * @const
     */
    const OK           = 200;
    const CREATED      = 201;
    const ACCEPTED     = 202;
    const MOVED_PERM   = 301;
    const FOUND        = 302;
    const BAD_REQUEST  = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN    = 403;
    const NOT_FOUND    = 404;

    /**
     * The response that will be sent back to a request
     *
     * @var string
     */
    protected $out;

    /**
     * Initializes the response object
     *
     */
    public function __construct()
    {
        $this->out = '';
    }
}
