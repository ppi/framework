<?php


namespace PPI\Log;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * This is a class which wraps around Psr\Log to provide a proxy to an underlying Logger implementation. This enables us
 * to boot up the PPI framework without actually setting a logger in the ServiceManager.
 *
 * @package PPI
 * @author Gary Tierney
 */
class LoggerProxy implements LoggerInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
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
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
