<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\Fixtures;

use PPI\Framework\App;

/**
 * Class AppForDispatchTest.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class AppForDispatchTest extends App
{

    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->booted = true; // Force it to not boot
    }

    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
    }

}