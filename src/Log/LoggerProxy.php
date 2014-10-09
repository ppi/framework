<?php


namespace PPI\Log;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

class LoggerProxy
{

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Sets the instance of LoggerInterface used by this proxy.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(PsrLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * An implementation of the magic method {@code __call} which proxies any method calls through to the underlying {@link LoggerInterface}.
     */
    public function __call($method, $args)
    {
        if (!is_null($this->logger)) {
            if (!method_exists($this->logger, $method)) {
                throw new \Exception(sprintf("Error: called unknown method '%s' with args %s", $method, print_r($args, true)));
            }

            return call_user_func_array(array($this->logger, $method), $args);
        }

        //@todo - should we really return from here?
        return null;
    }
}