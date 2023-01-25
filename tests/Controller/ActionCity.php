<?php
namespace Metrol\Tests\Controller;

use Metrol\Tests\Controller;

/**
 * This is pretend controller that will be parsed for route information
 *
 */
class ActionCity extends Controller
{
    /**
     * What the match strings should start with instead of the controller name
     * as the first segment.
     *
     */
    const MATCH_PREFIX = '/tester/';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Called to view a page.
     *
     */
    public function get_view(array $args): string
    {
        return '';
    }

    /**
     * Has a custom route name and match string in the docBlock here.
     *
     * @match     /stuff/:int/
     * @routename Page View
     * @maxparam  0
     *
     */
    public function get_pageview(array $args): string
    {
        return '';
    }

    /**
     * Has an underscore in the middle of the method name, which should be
     * turned into a slash for the match string.
     *
     * @routename Page View Wide
     * @maxparam  0
     *
     */
    public function get_page_view_wide(array $args): string
    {
        return '';
    }

    /**
     * The match string should be at the root of the prefix
     *
     * @routename Page Index Root
     * @maxparam  0
     *
     */
    public function get_(array $args): string
    {
        return '';
    }

    /**
     * See if the HTTP method is properly parsed
     *
     */
    public function post_updatestuff(array $args): string
    {
        return '';
    }

    /**
     * Shouldn't be turned into a route because it's private
     *
     */
    private function get_nothing(array $args): string
    {
        return '';
    }

    /**
     * Should be ignored since it doesn't have an expected prefix
     *
     * @routename NonRoute
     *
     */
    public function notARoute(array $args): string
    {
        return '';
    }
}
