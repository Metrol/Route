<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol/Route
 * @version       1.0
 * @copyright (c) 2022, Michael Collette
 */

namespace Metrol\Route;

use Metrol;

/**
 * Contains the logic for comparing a route to a request to see if it matches.
 *
 */
class MatchRoute
{
    /**
     * Instance of this object
     *
     */
    private static ?MatchRoute $instance;

    /**
     * The route that needs to be compared
     *
     */
    private Metrol\Route $route;

    /**
     * HTTP Request coming through
     *
     */
    private Request $request;

    /**
     * Found Arguments
     *
     */
    private array $args;

    /**
     * For testing purposes, records which step in the process decided that the
     * route was not a match.  This should not be relied upon for anything but
     * testing.
     *
     * @var string
     */
    public static string $noMatchReason = '';

    /**
     * Private constructor, need to use static methods instead.
     *
     */
    private function __construct()
    {
        // Nothing to see here... move along.
    }

    /**
     * Run the comparison on the route versus the request.  Will return true on
     * a match, and will populate the arguments of the route if any are found.
     *
     */
    public static function check(Metrol\Route\Request $request, Metrol\Route $route): bool
    {
        $inst = static::getInstance();

        $inst->request = $request;
        $inst->route   = $route;
        $inst->args    = [];

        $rtn = $inst->run();

        static::endInstance();

        return $rtn;
    }

    /**
     * Handles delegating the matching and argument finding to the appropriate
     * methods.  If this does match, the found arguments will be passed back to
     * the route.
     *
     */
    private function run(): bool
    {
        if ( !$this->checkBasics() )
        {
            return false;
        }

        if ( !$this->checkSegments() )
        {
            return false;
        }

        if ( !empty($this->args) )
        {
            $this->route->setArguments($this->args);
        }

        return true;
    }

    /**
     * Check out the easy stuff, like http method and the segment count
     * to see if we can get out of here early.
     *
     */
    private function checkBasics(): bool
    {
        $req = $this->request;
        $rt  = $this->route;

        if ( strtoupper($req->getHttpMethod()) != $rt->getHttpMethod() )
        {
            self::$noMatchReason = 'Wrong HTTP Method';
            return false;
        }

        // Just so as not to waste time, make sure there's a string to match
        //
        if ( empty($rt->getMatchString()) )
        {
            self::$noMatchReason = 'No match string specified in the route';
            return false;
        }

        // There had best be a slash somewhere in the URI.
        //
        if ( !str_contains($req->getUri(), '/') )
        {
            self::$noMatchReason = 'No slashes in the requested URI';
            return false;
        }

        return true;
    }

    /**
     * Walk through the segments of the URI to see if there's the proper number,
     * and they match the pattern in the route match string
     *
     */
    private function checkSegments(): bool
    {
        $req = $this->request;
        $rt  = $this->route;

        $reqSegments = $this->explodeURI($req->getUri());
        $rtSegments  = $this->explodeURI($rt->getMatchString());

        // Be sure to handle a doc root request properly
        //
        if ( empty($reqSegments) and $rt->getMatchString() == '/' )
        {
            return true;
        }
        elseif ( empty($reqSegments) and $rt->getMatchString() != '/' )
        {
            self::$noMatchReason = 'No requested segments, and not root doc';
            return false;
        }

        $reqSegmentCount = count($reqSegments);
        $rtSegmentCount  = count($rtSegments);

        // Need to at LEAST have as many segments in the Request as in the
        // Route match filter.
        //
        if ( $reqSegmentCount < $rtSegmentCount )
        {
            self::$noMatchReason = 'Not enough segments in the request to match the route';
            return false;
        }

        // The URI segments can't exceed the number of segments in the filter plus
        // the allowed parameters.
        //
        if ( $rt->getMaxParameters() !== null)
        {
            if ( $reqSegmentCount > $rtSegmentCount + $rt->getMaxParameters() )
            {
                self::$noMatchReason = 'Too many segments in the requested segment';

                return false;
            }
        }

        if ( ! $this->matchSegments($reqSegments, $rtSegments) )
        {
            return false;
        }

        return true;
    }

    /**
     * Walk through all the segments looking to see if things are matching up
     *
     * @param string[] $reqSegments Segments from the requested URI
     * @param string[] $rtSegments Segments from the Route match string
     *
     * @return boolean TRUE if all matched
     */
    private function matchSegments(array $reqSegments, array $rtSegments): bool
    {
        foreach ( $rtSegments as $segIdx => $rtSegment )
        {
            if ( str_contains($rtSegment, ':') )
            {
                if ( ! $this->hintMatch($reqSegments[ $segIdx ], $rtSegment) )
                {
                    return false;
                }

                continue;
            }

            // Fall through to a Literal match if no special characters
            if ( ! $this->literalMatch($reqSegments[$segIdx], $rtSegment) )
            {
                return false;
            }
        }

        $this->appendExtraSegments($reqSegments, $rtSegments);

        return true;
    }

    /**
     * Any extra segments not specified in the requested match and less than the
     * maximum parameters need to be added as arguments.
     *
     * @param string[] $reqSegments
     * @param string[] $rtSegments
     */
    private function appendExtraSegments(array $reqSegments, array $rtSegments): void
    {
        $reqCount = count($reqSegments);
        $rtCount  = count($rtSegments);

        if ( $reqCount == $rtCount )
        {
            return;
        }

        for ( $i = $rtCount; $i < $reqCount; $i++ )
        {
            $this->args[] = $reqSegments[$i];
        }
    }

    /**
     * Compares the filter segment to a literal looking for a match
     *
     * @param string $reqSegment Segment from the requested URI
     * @param string $rtSegment  Segment from the Route match string
     *
     * @return boolean
     */
    protected function literalMatch(string $reqSegment, string $rtSegment): bool
    {
        $rtn = false;

        if ( $reqSegment == $rtSegment )
        {
            $rtn = true;
        }

        if ( ! $rtn )
        {
            self::$noMatchReason = 'Literal segments ' . $reqSegment . ' and '
                . $rtSegment . ' did not match';
        }

        return $rtn;
    }

    /**
     * Compares the URI segment to a type hinted filter
     *
     * @param string $reqSegment Segment from the requested URI
     * @param string $rtSegment  Segment from the Route match string
     *
     * @return boolean
     */
    protected function hintMatch(string $reqSegment, string $rtSegment): bool
    {
        switch ( substr($rtSegment, 0, 4) )
        {
            case Metrol\Route::HINT_INTEGER:
                $rtn = $this->compareInteger($reqSegment, $rtSegment);
                break;

            case Metrol\Route::HINT_NUMBER:
                $rtn = $this->compareNumber($reqSegment, $rtSegment);
                break;

            case Metrol\Route::HINT_STRING:
                $rtn = $this->compareString($reqSegment, $rtSegment);
                break;

            default:
                $rtn = false;
        }

        if ( $rtn )
        {
            $this->args[] = $reqSegment;
        }

        return $rtn;
    }

    /**
     * Used to test a numeric hint match
     *
     * @param string $reqSegment Segment from the requested URI
     * @param string $rtSegment  Segment from the Route match string
     *
     * @return boolean
     */
    protected function compareNumber(string $reqSegment, string $rtSegment): bool
    {
        if ( !is_numeric($reqSegment) )
        {
            static::$noMatchReason = 'Number hinted segment not numeric';

            return false;
        }

        // If there's only 4 chars, then it's just the type hint.
        // Otherwise, need to check for a range
        //
        if ( strlen($rtSegment) == 4 )
        {
            return true;
        }

        $specSect = substr($rtSegment, 4);

        if ( str_starts_with($specSect, '[') and str_ends_with($specSect, ']') )
        {
            $specs = substr($specSect, 1, -1);

            if ( ! str_contains($specs, '-') )
            {
                if ( $reqSegment == $specs )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                $specParts = explode('-', $specs);
                $min = $specParts[0];
                $max = $specParts[1];

                if ( strlen($min) > 0 and floatval($reqSegment) < floatval($min) )
                {
                    return false;
                }

                if ( strlen($max) > 0 and floatval($reqSegment) > floatval($max) )
                {
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Used to test an integer hint match
     *
     * @param string $reqSegment Segment from the requested URI
     * @param string $rtSegment  Segment from the Route match string
     *
     * @return boolean
     */
    protected function compareInteger(string $reqSegment, string $rtSegment): bool
    {
        if ( ! is_numeric($reqSegment) )
        {
            return false;
        }

        if ( $reqSegment != intval($reqSegment) )
        {
            return false;
        }

        return $this->compareNumber(intval($reqSegment), $rtSegment);
    }

    /**
     * Used to test a string hint match
     *
     * @param string $reqSegment Segment from the requested URI
     * @param string $rtSegment  Segment from the Route match string
     *
     * @return boolean
     */
    protected function compareString(string $reqSegment, string $rtSegment): bool
    {
        // If there's only 4 chars, then it's just the type hint
        if ( strlen($rtSegment) == 4 )
        {
            return true;
        }

        $reqSegmentLength = strlen($reqSegment);
        $specSect  = substr($rtSegment, 4);

        if ( str_starts_with($specSect, '[') and str_ends_with($specSect, ']') )
        {
            $specs = substr($specSect, 1, -1);

            if ( ! str_contains($specs, '-') )
            {
                if ( $reqSegmentLength == intval($specs) )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                $specParts = explode('-', $specs);
                $min = $specParts[0];
                $max = $specParts[1];

                if ( strlen($min) > 0 and $reqSegmentLength < intval($min) )
                {
                    return false;
                }

                if ( strlen($max) > 0 and $reqSegmentLength > intval($max) )
                {
                    return false;
                }

                return true;
            }
        }

        return true;
    }

    /**
     * Breaks apart the input URI into an array of segments
     *
     */
    private function explodeURI(string $uri): array
    {
        $uriSegments = [];

        if ( !str_contains($uri, '/') )
        {
            return $uriSegments;
        }

        $uriParts = explode('/', $uri);

        foreach ( $uriParts as $uriSegment )
        {
            if ( $uriSegment != '' )
            {
                $uriSegments[] = $uriSegment;
            }
        }

        return $uriSegments;
    }

    /**
     * Get a single instance of this object for internal use
     *
     */
    private static function getInstance(): MatchRoute
    {
        if ( ! isset(static::$instance) or is_null(static::$instance) )
        {
            static::$instance = new MatchRoute;
            static::$noMatchReason = '';
        }

        return static::$instance;
    }

    /**
     * Kill the instance that has been instantiated.
     *
     */
    private static function endInstance(): void
    {
        unset(static::$instance->request);
        unset(static::$instance->route);
        unset(static::$instance->args);

        static::$instance = null;
    }
}
