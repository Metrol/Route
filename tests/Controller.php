<?php
/**
 * This is pretend controller that will be parsed for route information
 *
 */
class Controller
{
    /**
     * What the match strings should start with instead of the controller name
     * as the first segment.
     *
     */
    const MATCH_PREFIX = '/tester/';


    public function __construct()
    {
        // Not much to do in here.
    }

    /**
     * Called to view a page.
     *
     * @param array
     *
     * @return string
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
     * @param array
     *
     * @return string
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
     * @param array
     *
     * @return string
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
     * @param array
     *
     * @return string
     */
    public function get_(array $args): string
    {
        return '';
    }

    /**
     * See if the HTTP method is properly parsed
     *
     * @param array
     *
     * @return string
     */
    public function post_updatestuff(array $args): string
    {
        return '';
    }

    /**
     * Shouldn't be turned into a route because it's private
     *
     * @param array
     *
     * @return string
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
     * @param array
     *
     * @return string
     */
    public function notARoute(array $args): string
    {

    }
}
