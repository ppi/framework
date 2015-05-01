<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\View;

use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;

/**
 * Internal representation of a template.
 *
 * @author     Victor Berchet <victor@suumit.com>
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão
 */
class TemplateReference extends BaseTemplateReference
{
    /**
     * @var string
     */
    const APP_VIEWS_DIRECTORY = 'views';

    /**
     * @var string
     */
    const MODULE_VIEWS_DIRECTORY = 'resources/views';

    /**
     * Constructor.
     *
     * @param null $module
     * @param null $controller
     * @param null $name
     * @param null $format
     * @param null $engine
     */
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
     *  - as a resource when the template is part of a module.
     *
     * @return string A path to the template or a resource
     */
    public function getPath()
    {
        $controller = str_replace('\\', '/', $this->get('controller'));

        $path = (empty($controller) ? '' : $controller . '/') . $this->get('name') . '.' . $this->get('format') . '.' . $this->get('engine');

        return empty($this->parameters['module']) ?
            self::APP_VIEWS_DIRECTORY . '/' . $path : '@' . $this->get('module') . '/' . self::MODULE_VIEWS_DIRECTORY . '/' . $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return sprintf('%s:%s:%s.%s.%s', $this->parameters['module'], $this->parameters['controller'],
            $this->parameters['name'], $this->parameters['format'], $this->parameters['engine']);
    }
}
