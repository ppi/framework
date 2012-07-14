<?php

namespace PPI\Templating;

use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;

/**
 *
 * Internal representation of a template.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Paul Dragoonis <paul@ppi.io>
 * @author Vítor Brandão <noisebleed@noiselabs.org>
 */
class TemplateReference extends BaseTemplateReference
{
    const APP_VIEWS_DIRECTORY = 'views';
    const MODULE_VIEWS_DIRECTORY = 'resources/views';

    public function __construct($module = null, $controller = null, $name = null, $format = null, $engine = null)
    {
        $this->parameters = array(
            'module'     => $module,
            'controller' => $controller,
            'name'       => $name,
            'format'     => $format,
            'engine'     => $engine,
        );
    }

    /**
     * Returns the path to the template
     *  - as a path when the template is not part of a module
     *  - as a resource when the template is part of a module
     *
     * @return string A path to the template or a resource
     */
    public function getPath()
    {
        $controller = str_replace('\\', '/', $this->get('controller'));

        $path = (empty($controller) ? '' : $controller.'/').$this->get('name').'.'.$this->get('format').'.'.$this->get('engine');

        return empty($this->parameters['module']) ? self::APP_VIEWS_DIRECTORY.'/'.$path : '@'.$this->get('module').'/'.self::MODULE_VIEWS_DIRECTORY.'/'.$path;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return sprintf('%s:%s:%s.%s.%s', $this->parameters['module'], $this->parameters['controller'], $this->parameters['name'], $this->parameters['format'], $this->parameters['engine']);
    }
}
