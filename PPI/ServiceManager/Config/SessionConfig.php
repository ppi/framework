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

namespace PPI\ServiceManager\Config;

use Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Session component.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class SessionConfig extends AbstractConfig
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(

        // internal configuration
        'app.session.class'                     => 'Symfony\Component\HttpFoundation\Session\Session',
        'app.session.storage.class'             => 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage',
        'app.session.flashbag.class'            => 'Symfony\Component\HttpFoundation\Session\Flash\FlashBag',
        'app.session.attribute_bag.class'       => 'Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag',
        'app.session.storage.native.class'      => 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage',
        'app.session.handler.native_file.class' => 'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler',

        // user level configuration
        'session'                               => array(
            'storage_id'                        => 'session.storage.native',
            'handler_id'                        => 'session.handler.native_file',
            'name'                              => null,
            'cookie_lifetime'                   => null,
            'cookie_path'                       => null,
            'cookie_domain'                     => null,
            'cookie_secure'                     => null,
            'cookie_httponly'                   => null,
            'gc_divisor'                        => null,
            'gc_probability'                    => null,
            'gc_maxlifetime'                    => null,
            'save_path'                         => '%app.cache_dir%/sessions'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $serviceManager->getOptions()->add($this->getDefaultOptions());

        $config = $serviceManager->getOption('session');

        // session storage
        $serviceManager->setOption('app.session.storage', $config['storage_id']);

        $options = array();
        foreach (array('name', 'cookie_lifetime', 'cookie_path', 'cookie_domain', 'cookie_secure', 'cookie_httponly', 'gc_maxlifetime', 'gc_probability', 'gc_divisor', 'save_path') as $key) {
            // @todo - the default values are null, so isset() fails, make sure this is intentional
            if (isset($config[$key])) {
                $options[$key] = $config[$key];
            }
        }

        $serviceManager->setOption('app.session.storage.options', $options);

        // session handler (the internal callback registered with PHP session management)
        $serviceManager->setFactory('session.handler', function($serviceManager) use ($config) {
            if (null == $config['handler_id']) {
                // Set the handler class to be null
                return null;
            } else {
                return $serviceManager->get($config['handler_id']);
            }
        });

        // session storage native
        $serviceManager->setFactory('session.storage.native', function($serviceManager) {           
            $class = $serviceManager->getOption('app.session.storage.native.class');

            return new $class(
                $serviceManager->getOption('app.session.storage.options'),
                $serviceManager->get('session.handler')
            );
        });

        // session flash bag
        $serviceManager->setFactory('session.flash_bag', function($serviceManager) {
            $class = $serviceManager->getOption('app.session.flashbag.class');

            return new $class();
        });

        // session attribute bag
        $serviceManager->setFactory('session.attribute_bag', function($serviceManager) {
            $class = $serviceManager->getOption('app.session.attribute_bag.class');
            return new $class();
        });

        // session handler native file
        $serviceManager->setFactory('session.handler.native_file', function($serviceManager) use ($config) {
            $class = $serviceManager->getOption('app.session.handler.native_file.class');
            $storageOptions = $serviceManager->getOption('app.session.storage.options');
            return new $class($storageOptions['save_path']);
        });

        // session
        $serviceManager->setFactory('session', function($serviceManager) {
            $class = $serviceManager->getOption('app.session.class');

            $session = new $class(
                $serviceManager->get($serviceManager->getOption('app.session.storage')),
                $serviceManager->get('session.attribute_bag'),
                $serviceManager->get('session.flash_bag')
            );
            $session->start();

            return $session;
        });
    }
}
