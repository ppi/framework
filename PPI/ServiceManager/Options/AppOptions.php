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

namespace PPI\ServiceManager\Options;

/**
 * Holds PPI application configuration. (defaults and user-defined).
 *
 * And remember, all options are lowercase!
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class AppOptions extends AbstractOptions
{
    /**
     * Constructor.
     *
     * $parameters['config'] holds user configuration defined in app.config.php.
     *
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = array())
    {
        if (isset($parameters['config'])) {
            $parameters = array_merge($parameters, $parameters['config']);
            unset($parameters['config']);
        }

        parent::__construct($parameters);
    }
}
