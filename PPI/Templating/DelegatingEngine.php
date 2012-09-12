<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Templating;

use Symfony\Component\Templating\DelegatingEngine as BaseDelegatingEngine;

/**
 * DelegatingEngine selects an engine for a given template.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Templating
 */
class DelegatingEngine extends BaseDelegatingEngine
{
    /**
     * @var array
     */
    protected $globals = array();

    /**
     * Renders a template.
     *
     * @param mixed $name       A template name or a TemplateReferenceInterface
     *                          instance
     * @param array $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     *
     * @throws \InvalidArgumentException If the template does not exist
     * @throws \RuntimeException         If the template cannot be rendered
     *
     * @api
     */
    public function render($name, array $parameters = array())
    {
        $engine = $this->getEngine($name);

        if (!empty($this->globals)) {
            foreach ($this->globals as $key => $val) {
                $engine->addGlobal($key, $val);
            }
        }

        return $engine->render($name, $parameters);
    }

    /**
     * Add a global parameter to the sub-engine selected
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     *
     * @api
     */
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

}
