<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Module\Controller;

use Zend\ModuleManager\ModuleManagerInterface;

/**
 * ControllerNameParser converts controller from the short notation a:b:c
 * (BlogModule:Post:index) to a fully-qualified class::method string
 * (Module\BlogModule\Controller\PostController::indexAction).
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Module
 */
class ControllerNameParser
{
    protected $moduleManager;

    /**
     * Constructor.
     *
     * @param ModuleManagerInterface $moduleManager A ModuleManagerInterface instance
     */
    public function __construct(ModuleManagerInterface $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Converts a short notation a:b:c to a class::method.
     *
     * @param string $controller A short notation controller (a:b:c)
     *
     * @return string A string with class::method
     *
     * @throws \InvalidArgumentException when the specified module is not enabled
     *                                   or the controller cannot be found
     */
    public function parse($controller)
    {
        if (3 != count($parts = explode(':', $controller))) {
            throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid a:b:c controller string.', $controller));
        }

        list($moduleAlias, $controller, $action) = $parts;
        $controller = str_replace('/', '\\', $controller);
        $module = $this->moduleManager->getModuleByAlias($moduleAlias);

        if (null === $module) {
            // this throws an exception if there is no such module
            $msg = sprintf('Unable to find controller "%s:%s" - module alias "%s" does not exist.', $moduleAlias, $controller, $moduleAlias);
        } else {
            $class = $module->getNamespace().'\\Controller\\'.$controller;
            if (class_exists($class)) {
                return $class.'::'.$action.'Action';
            }

            $msg = sprintf('Unable to find controller "%s:%s" - class "%s" does not exist.', $moduleAlias, $controller, $class);
        }

        throw new \InvalidArgumentException($msg);
    }
}
