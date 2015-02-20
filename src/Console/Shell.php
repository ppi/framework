<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Console;

use Symfony\Component\Console\Shell as BaseShell;

/**
 * Shell.
 *
 * @author      Vítor Brandão <vitor@ppi.io>
 * @package     PPI
 * @subpackage  Console
 */
class Shell extends BaseShell
{
    /**
     * Returns the shell header.
     *
     * @return string The header string
     */
    protected function getHeader()
    {
        return <<<EOF
<info>
           _____   _____ |_|
          / __  | /  __ | /|
         | |__| || |__| || |
         |  ___/ |  ___/ | |
         | |     | |     |_|
         |/      |/

</info>
EOF
        . parent::getHeader();
    }
}
