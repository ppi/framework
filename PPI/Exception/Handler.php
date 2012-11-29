<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Exception;

/**
 * Handler class
 *
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage Exception
 */
class Handler
{
    /**
     * The exception handlers
     *
     * @var array
     */
    protected $_handlers = array();

    /**
     * Handler statuses
     *
     * @var array
     */
    protected $_handlerStatus = array();

    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $e)
    {
        $trace = $e->getTrace();

        $error = array(
               'file'    => $e->getFile(),
               'line'    => $e->getLine(),
               'message' => $e->getMessage()
        );

        try {

            // Execute each callback
            foreach ($this->_handlers as $handler) {
                $this->_handlerStatus[] = array(
                    'object'   => get_class($handler),
                    'response' => $handler->handle($e)
                );
            }

            require(__DIR__ . '/templates/fatal.php');
            exit;

        } catch (\Exception $e) {
            require(__DIR__ . '/templates/fatal.php');
            exit;
        }

    }

    /**
     * @todo Add inline documentation.
     *
     * @param type $errno
     * @param type $errstr
     * @param type $errfile
     * @param type $errline
     *
     * @return void
     */
    public function handleError($errno = '', $errstr = '', $errfile = '', $errline = '')
    {
        $error = array(
            'message' => $errstr,
            'file'    => $errfile,
            'line'    => $errline
        );

        try {
            throw new \Exception('');
        } catch (\Exception $e) {
            try {
                // Execute each callback
                foreach ($this->_handlers as $handler) {
                    $this->_handlerStatus[] = array(
                        'object'   => get_class($handler),
                        'response' => $handler->handle($e)
                    );
                }
            } catch (\Exception $e) {
            }

            $trace = $e->getTrace();
        }

        require(__DIR__ . '/templates/fatal.php');
        exit;
    }

    /**
     * Add an Exception callback
     *
     * @param HandlerInterface $handler
     *
     * @return void
     */
    public function addHandler(\PPI\Exception\HandlerInterface $handler)
    {
        $this->_handlers[] = $handler;
    }

}
