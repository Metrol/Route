<?php
namespace Metrol\Tests\Controller;

use Metrol\Tests\Controller;
use Metrol\Route\Attributes\Route;

/**
 * This is pretend controller uses Attributes instead of doc blocks to
 * establish routes
 *
 */
class AttributeCity extends Controller
{
    const MATCH_PREFIX = '/tester/';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Called to view a page.
     *
     */
    #[Route(match: '/')]
    public function view(array $args): string
    {
        return '';
    }

    /**
     * Has a custom route name and match string in the docBlock here.
     *
     */
    #[Route(name: 'Funky Page View', args: [Route::HINT_INT, Route::HINT_STR])]
    public function pageview(array $args): string
    {
        return '';
    }

    /**
     * Has an underscore in the middle of the method name, which should be
     * turned into a slash for the match string.
     *
     */
    #[Route]
    public function page_view_wide(array $args): string
    {
        return '';
    }

    /**
     * The match string should be at the root of the prefix
     *
     */
    #[Route(match: '/', maxParam: 3)]
    public function index(array $args): string
    {
        return '';
    }

    /**
     * See if the HTTP method is properly parsed
     *
     */
    #[Route(method: Route::POST)]
    public function updatestuff(array $args): string
    {
        return '';
    }

    /**
     * Shouldn't be turned into a route because it's private
     *
     */
    #[Route(name: 'I should not exist in the routes')]
    private function nothing(array $args): string
    {
        return '';
    }
}
