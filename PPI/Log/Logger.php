<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Log;

use Monolog\Logger as BaseLogger;

/**
 * Logger is PSR-3 compliant logger based on Monolog\Logger.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 * @author Paul Dragooni <paul@ppi.io>
 */
class Logger extends BaseLogger {}