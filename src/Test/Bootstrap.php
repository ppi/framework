<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Test;

require_once __DIR__ . '/../Autoload.php';
require_once __DIR__ . '/AutoLoad.php';

\PPI\Framework\Autoload::config(array(
    'loader'    => new Autoload(),
));
\PPI\Framework\Autoload::register();
