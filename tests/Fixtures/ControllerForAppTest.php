<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\Fixtures;

use PPI\Framework\Module\Controller as BaseController;

/**
 * @author Paul Dragoonis <paul@ppi.io>
 */
class ControllerForAppTest extends BaseController
{
    public function indexAction()
    {
        return 'Working Response From Controller Index Action';
    }

    public function __invoke()
    {
        return 'Working Response From Controller Invoke Action';
    }
}
