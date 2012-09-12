<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI;

/**
 * The PPI Autoloader.
 *
 * It is able to load classes that use either:
 *
 *  * The technical interoperability standards for PHP 5.3 namespaces and
 *    class names (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md);
 *
 *  Example usage:
 *
 *  PPI\Autoload::add('Symfony', PPI_VENDOR_PATH . '/path/to/src/Symfony')
 *  PPI\Autoload::register();
 *
 * @package    PPI
 * @subpackage Core
 */
class Autoload
{
    /**
     * The ClassLoader object
     *
     * @var null|object
     */
    protected static $_loader = null;

    /**
     * @var array
     */
    protected static $_options = array();

    /**
     * @var array
     */
    protected static $_registeredNamespaces = array();

    /**
     * Add some items to the class config
     *
     * @param array $config
     *
     * @return void
     *
     * @static
     */
    public static function config(array $config)
    {
        self::$_options = array_merge($config, self::$_options);
    }

    /**
     * Add a namespace to the autoloader path
     *
     * @param string $key
     * @param string $path
     *
     * @return void
     *
     * @static
     */
    public static function add($key, $path)
    {
        self::$_registeredNamespaces[$key] = true;
        self::$_options['loader']->add($key, $path);
    }

    /**
     * Register the autoloader namespaces or prefixes thus far.
     *
     * @return void
     *
     * @static
     */
    public static function register()
    {
        self::$_options['loader']->register();
    }

    /**
     * Check if a namespace has been registered. This is a workaround as the default self::$_options['loader']
     * class does not have an exists() method.
     *
     * @param string $key
     *
     * @return boolean
     *
     * @static
     */
    public static function exists($key)
    {
        return isset(self::$_registeredNamespaces[$key]);
    }

}
