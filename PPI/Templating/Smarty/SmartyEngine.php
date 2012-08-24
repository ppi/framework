<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     Templating
 * @copyright   Copyright (c) 2012 Paul Dragoonis <dragoonis@php.net>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Templating\Smarty;

use PPI\Templating\GlobalVariables;
use PPI\Templating\TemplateLocator;
use NoiseLabs\Bundle\SmartyBundle\SmartyEngine as BaseSmartyEngine;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * SmartyEngine is an engine able to render Smarty templates.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 * @author Vítor Brandão <vitor@ppi.io>
 */
class SmartyEngine extends BaseSmartyEngine
{
    protected $locator;

    /**
     * Constructor.
     *
     * @param \Smarty                     $smarty  A \Smarty instance
     * @param TemplateLocator             $locator A TemplateLocator instance
     * @param TemplateNameParserInterface $parser  A TemplateNameParserInterface instance
     * @param LoaderInterface             $loader  A LoaderInterface instance
     * @param array                       $options An array of \Smarty properties
     * @param GlobalVariables|null        $globals A GlobalVariables instance or null
     */
    public function __construct(\Smarty $smarty, TemplateLocator $locator, TemplateNameParserInterface $parser, LoaderInterface $loader,
    array $options = array(), GlobalVariables $globals = null)
    {
        $this->smarty = $smarty;
        $this->locator = $locator;
        $this->parser = $parser;
        $this->loader = $loader;
        $this->globals = array();

        // There are no default extensions.
        $this->extensions = array();

        /**
         * Register an handler for 'logical' filenames of the type:
         * <code>file:Application:index:index.html.smarty</code>
         */
        $this->smarty->default_template_handler_func = array($this,  'smartyDefaultTemplateHandler');

        /**
         * Define a set of template dirs to look for. This will allow the
         * usage of the following syntax:
         * <code>file:[Application]/index/index.html.tpl</code>
         *
         * See {@link http://www.smarty.net/docs/en/resources.tpl} for details
         */
        $this->smarty->setTemplateDir($this->locator->getAppPath());
        $this->smarty->addTemplateDir($this->locator->getModulesPath());

        foreach ($options as $property => $value) {
            $this->smarty->{$this->smartyPropertyToSetter($property)}($value);
        }

        if (null !== $globals) {
            $this->addGlobal('app', $globals);
        }
    }
}
