<?php

namespace PPI\Framework\Exception;

use PPI\Framework\Debug\ExceptionHandler;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class SymfonyExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {

        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $exception = $event->getException();
        $data = array(
            'error' => array(
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            )
        );
        $handler = new ExceptionHandler(true, 'UTF-8', 'PPI Framework', '2.2', true);

        // SF2 has its own custom output buffer registered which calls \Symfony\Component\Debug\ExceptionHandler::cleanOutput
        // This truncates the HTML markup output and gives you a dodgy page, so we delete it and start a fresh one.
        $status = ob_get_status();
        if (!empty($status)) {
            ob_end_clean();
            ob_start();
        }

        $response = $handler->createResponse($exception);
        $event->setResponse($response);
    }
}