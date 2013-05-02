<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Module\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ControllerResolver.
 * @see Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Module
 */
class ControllerResolver extends BaseControllerResolver
{
    /**
     * Constructor.
     *
     * @param ServiceLocatorInterface $serviceManager A ServiceLocatorInterface instance
     * @param ControllerNameParser    $parser         A ControllerNameParser instance
     * @param LoggerInterface         $logger         A LoggerInterface instance
     */
    public function __construct(ServiceLocatorInterface $serviceManager, ControllerNameParser $parser, LoggerInterface $logger = null)
    {
        $this->serviceManager = $serviceManager;
        $this->parser = $parser;

        parent::__construct($logger);
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return mixed A PHP callable
     *
     * @throws \LogicException           When the name could not be parsed
     * @throws \InvalidArgumentException When the controller class does not exist
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            $count = substr_count($controller, ':');
            if (2 == $count) {
                // controller in the a:b:c notation then
                $controller = $this->parser->parse($controller);
            } elseif (1 == $count) {
                // controller in the service:method notation
                list($service, $method) = explode(':', $controller, 2);

                return array($this->serviceManager->get($service), $method);
            } else {
                throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
            }
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $controller = new $class();
        if ($controller instanceof ServiceLocatorAwareInterface) {
            $controller->setServiceLocator($this->serviceManager);
        }

        return array($controller, $method);
    }
}
