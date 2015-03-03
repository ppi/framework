<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\View;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator as BaseTemplateLocator;

/**
 * The PPI Template Locator.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Templating
 */
class TemplateLocator extends BaseTemplateLocator
{
    /**
     * Returns the path to the views directory in the app dir.
     *
     * @return string The path to the app directory.
     */
    public function getAppPath()
    {
        return $this->locator->getAppPath() . DIRECTORY_SEPARATOR . TemplateReference::APP_VIEWS_DIRECTORY;
    }

    /**
     * Returns an array of paths to modules views dir.
     *
     * @return array An array of paths to each loaded module
     */
    public function getModulesPath()
    {
        $paths = $this->locator->getModulesPath();
        foreach (array_keys($paths) as $module) {
            $paths[$module] .= DIRECTORY_SEPARATOR . TemplateReference::MODULE_VIEWS_DIRECTORY;
        }

        return $paths;
    }
}
