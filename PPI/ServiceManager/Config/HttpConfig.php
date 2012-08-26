<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Http component.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class HttpConfig extends Config
{
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        // HTTP Request
        $serviceManager->setFactory('http.request', function($serviceManager) {
            return HttpRequest::createFromGlobals();
        })->setAlias('request', 'http.request');

        // HTTP Response
        $serviceManager->setFactory('http.response', function($serviceManager) {
            return new HttpResponse();
        })->setAlias('response', 'http.response');
    }
}
