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
    public function get_view(array $args)
    {
        return '';
    }

    /**
     * Has a custom route name and match string in the docBlock here.
     *
     * @match /stuff/:int/
     * @routeName Page View
     *
     * @param array
     *
     * @return string
     */
    public function get_pageview(array $args)
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
    private function get_nothing(array $args)
    {
        return '';
    }
}
