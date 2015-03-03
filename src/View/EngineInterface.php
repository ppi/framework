<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\View;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface as BaseEngineInterface;

/**
 * EngineInterface is the interface each engine must implement.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 * @package    PPI
 * @subpackage Templating
 */
interface EngineInterface extends BaseEngineInterface
{
    /**
     * Renders a view and returns a Response.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A Response instance
     *
     * @return Response A Response instance
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null);
}
