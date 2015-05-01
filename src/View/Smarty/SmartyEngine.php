<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\View\Smarty;

use NoiseLabs\Bundle\SmartyBundle\SmartyEngine as BaseSmartyEngine;
use PPI\Framework\Log\LoggerInterface;
use PPI\Framework\View\GlobalVariables;
use PPI\Framework\View\TemplateLocator;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * SmartyEngine is an engine able to render Smarty templates.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class SmartyEngine extends BaseSmartyEngine
{
    /**
     * @var \PPI\Framework\View\TemplateLocator
     */
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
     * @param LoggerInterface|null        $logger  A LoggerInterface instance or null
     */
    public function __construct(\Smarty $smarty, TemplateLocator $locator, TemplateNameParserInterface $parser,
                                LoaderInterface $loader, array $options = array(), GlobalVariables $globals = null,
                                LoggerInterface $logger = null)
    {
        $this->smarty  = $smarty;
        $this->locator = $locator;
        $this->parser  = $parser;
        $this->loader  = $loader;
        $this->globals = array();
        $this->logger  = null;

        // There are no default extensions.
        $this->extensions = array();

        /*
         * Register an handler for 'logical' filenames of the type:
         * <code>file:Application:index:index.html.smarty</code>
         */
        $this->smarty->default_template_handler_func = array($this, 'smartyDefaultTemplateHandler');

        /*
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

        /*
         * @note muteExpectedErrors() was activated to workaround the following issue:
         *
         * <code>Warning: filemtime(): stat failed for /path/to/smarty/cache/3ab50a623e65185c49bf17c63c90cc56070ea85c.one.tpl.php
         * in /path/to/smarty/libs/sysplugins/smarty_resource.php</code>
         *
         * This means that your application registered a custom error hander
         * (using set_error_handler()) which is not respecting the given $errno
         * as it should. If, for whatever reason, this is the desired behaviour
         * of your custom error handler, please call muteExpectedErrors() after
         * you've registered your custom error handler.
         *
         * muteExpectedErrors() registers a custom error handler using
         * set_error_handler(). The error handler merely inspects $errno and
         * $errfile to determine if the given error was produced deliberately
         * and must be ignored, or should be passed on to the next error handler.
         */
        $smarty->muteExpectedErrors();
    }
}
