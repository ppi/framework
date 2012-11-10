<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */
namespace PPI\ServiceManager\Config;

use Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Session component.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
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
            'session.storage_id'                        => 'session.storage.native',
            'session.handler_id'                        => 'session.handler.native_file',
            'session.save_path'                         => '%app.cache_dir%/sessions',
            'session.name'                              => null,
            'session.cookie_lifetime'                   => null,
            'session.cookie_path'                       => null,
            'session.cookie_domain'                     => null,
            'session.cookie_secure'                     => null,
            'session.cookie_httponly'                   => null,
            'session.gc_divisor'                        => null,
            'session.gc_probability'                    => null,
            'session.gc_maxlifetime'                    => null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        
        $smOptions = $serviceManager->getOptions();
        
        foreach($this->getDefaultOptions() as $defaultKey => $defaultVal) {
            if(!$smOptions->has($defaultKey)) {
                $smOptions->set($defaultKey, $defaultVal);
            }
        }

        // session storage
        $serviceManager->setOption('app.session.storage', $smOptions->get('session.storage_id'));

        $sessionOptions = array();
        foreach (array('name', 'cookie_lifetime', 'cookie_path', 'cookie_domain', 'cookie_secure', 'cookie_httponly', 'gc_maxlifetime', 'gc_probability', 'gc_divisor', 'save_path') as $key) {
            $sessionOptions[$key] = $smOptions->get('session.' . $key);
        }

        $serviceManager->setOption('app.session.storage.options', $sessionOptions);

        // session handler (the internal callback registered with PHP session management)
        $serviceManager->setFactory('session.handler', function($serviceManager) use ($smOptions) {
            $handlerID = $smOptions->get('session.handler_id');
            return $handlerID === null ? null : $serviceManager->get($handlerID);
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
        $serviceManager->setFactory('session.handler.native_file', function($serviceManager) use ($smOptions) {
            $class = $smOptions->get('app.session.handler.native_file.class');
            $storageOptions = $smOptions->get('app.session.storage.options');
            return new $class($storageOptions['save_path']);
        });

        // session
        $serviceManager->setFactory('session', function($serviceManager) use($smOptions) {
            $class = $serviceManager->getOption('app.session.class');

            $session = new $class(
                $serviceManager->get($smOptions->get('app.session.storage')),
                $serviceManager->get('session.attribute_bag'),
                $serviceManager->get('session.flash_bag')
            );
            $session->start();

            return $session;
        });
    }

}
