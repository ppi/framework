<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Session component.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class SessionConfig extends Config
{
    /**
     * {@inheritdoc}
     */
    public function __construct($config = array())
    {
        parent::__construct(array_merge(array(
            'aliases' => array(
                'session.class'                     => 'Symfony\Component\HttpFoundation\Session\Session',
                'session.storage.class'             => 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage',
                'session.flashbag.class'            => 'Symfony\Component\HttpFoundation\Session\Flash\FlashBag',
                'session.attribute_bag.class'       => 'Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag',
                'session.storage.native.class'      => 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage',
                'session.handler.native_file.class' => 'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler',
            ),
        ), $config));
    }

    /**
     * {@inheritdoc}
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $config = $serviceManager->get('Config');

        $options =  array_merge(array(
            'auto_start'        => false,
            'storage_id'        => 'session.storage.native',
            'handler_id'        => 'session.handler.native_file',
            'name'              => null,
            'cookie_lifetime'   => null,
            'cookie_path'       => null,
            'cookie_domain'     => null,
            'cookie_secure'     => null,
            'cookie_httponly'   => null,
            'gc_divisor'        => null,
            'gc_probability'    => null,
            'gc_maxlifetime'    => null,
            'save_path'         => $config['parameters']['app.cache_dir'] . '/sessions',
        ), isset($config['session']) ? $config['session'] : array());

        $storageOptions = $options;
        foreach (array('auto_start', 'storage_id', 'handler_id') as $k) {
            unset($storageOptions[$k]);
        }

        // session handler
        if (!$serviceManager->has('session.handler')) {
            $serviceManager->setFactory('session.handler', function($serviceManager) use ($options) {
                $handlerID = $options['session.handler_id'];

                return $handlerID === null ? null : $serviceManager->get($handlerID);
            });
        }

        // session storage native
        if (!$serviceManager->has('session.storage.native')) {
            $serviceManager->setFactory('session.storage.native', function($serviceManager) use ($storageOptions) {
                $class = $serviceManager->get('session.storage.native.class');

                return new $class($storageOptions, $serviceManager->get('session.handler'));
            });
        }

        // session flash bag
        if (!$serviceManager->has('session.flash_bag')) {
            $serviceManager->setFactory('session.flash_bag', function($serviceManager) {
                $class = $serviceManager->get('session.flashbag.class');

                return new $class();
            });
        }

        // session attribute bag
        if (!$serviceManager->has('session.attribute_bag')) {
            $serviceManager->setFactory('session.attribute_bag', function($serviceManager) {
                $class = $serviceManager->get('session.attribute_bag.class');

                return new $class();
            });
        }

        // session handler native file
        $serviceManager->setFactory('session.handler.native_file', function($serviceManager) use ($storageOptions) {
            $class = $serviceManager->get('session.handler.native_file.class');

            return new $class($storageOptions['save_path']);
        });

        // session
        if (!$serviceManager->has('session')) {
            $serviceManager->setFactory('session', function($serviceManager) use ($options) {
                $class = $serviceManager->get('session.class');

                $session = new $class(
                    $serviceManager->get($options['session.storage_id']),
                    $serviceManager->get('session.attribute_bag'),
                    $serviceManager->get('session.flash_bag')
                );
                $session->start();

                return $session;
            });
        }
    }
}
