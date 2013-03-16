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
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * Logger is PSR-3 compliant logger based on Monolog\Logger.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class Logger extends BaseLogger implements LoggerInterface, DebugLoggerInterface
{
    /**
     * @see Symfony\Component\HttpKernel\Log\DebugLoggerInterface
     */
    public function getLogs()
    {
        if ($logger = $this->getDebugLogger()) {
            return $logger->getLogs();
        }
    }

    /**
     * @see Symfony\Component\HttpKernel\Log\DebugLoggerInterface
     */
    public function countErrors()
    {
        if ($logger = $this->getDebugLogger()) {
            return $logger->countErrors();
        }
    }

    /**
     * Returns a DebugLoggerInterface instance if one is registered with this logger.
     *
     * @return DebugLoggerInterface A DebugLoggerInterface instance or null if none is registered
     */
    private function getDebugLogger()
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof DebugLoggerInterface) {
                return $handler;
            }
        }
    }
}
