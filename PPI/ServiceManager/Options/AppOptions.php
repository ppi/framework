<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */
namespace PPI\ServiceManager\Options;

/**
 * Holds PPI application configuration. (defaults and user-defined).
 *
 * And remember, all options are lowercase!
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class AppOptions extends AbstractOptions
{
    /**
     * Constructor.
     *
     * $parameters['config'] holds user configuration defined in app.config.php.
     *
     * @param array $parameters An array of parameters
     *
     * @return void
     */
    public function __construct(array $parameters = array())
    {
        parent::__construct(array_merge($this->getDefaultOptions(), $parameters));
    }

    /**
     * @todo Add inline documentation.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        $defaults = array(
            // app core parameters
            'environment'               => 'production',
            'debug'                     => false,
            'app.root_dir'              => null,
            'app.cache_dir'             => '%app.root_dir%/cache',
            'app.logs_dir'              => '%app.root_dir%/logs',
            'app.module_dirs'           => null,
            'app.modules'               => array(),
            'app.charset'               => 'UTF-8',
            'app.locale'                => 'en',

            'app.auto_dispatch'         => true,

            // templating
            'templating.engines'        => array('php'),
            'templating.globals'        => array(),

            // routing
            '404RouteName'              => 'Framework_404',

            // datasource
            'useDataSource'             => false
        );

        $defaults['app.root_dir'] = getcwd().'/app';

        return $defaults;
    }

}
