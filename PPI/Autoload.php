<?php
/**
 * The PPI Autoloader
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppiframework.com
 */
namespace PPI;

/**
 *
 * It is able to load classes that use either:
 *
 *  * The technical interoperability standards for PHP 5.3 namespaces and
 *    class names (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md);
 *
 *  * The PEAR naming convention for classes (http://pear.php.net/).
 *
 *  Example usage:
 *
 *  PPI\Autoload::add('Symfony', PPI_VENDOR_PATH . 'Symfony')
 *  PPI\Autoload::register();
 *
 */

class Autoload
{
    /**
     * The ClassLoader object
     *
     * @var null|object
     */
    protected static $_loader = null;

    protected static $_options = array();

    protected static $_registeredNamespaces = array();

    /**
     * Add some items to the class config
     *
     * @static
     * @param array $config
     */
    public static function config(array $config)
    {
        self::$_options = array_merge($config, self::$_options);
    }

    /**
     * Add a namespace to the autoloader path
     *
     * @static
     * @param string $key
     * @param string $path
     */
    public static function add($key, $path)
    {
        self::$_registeredNamespaces[$key] = true;
        self::$_options['loader']->registerNamespace($key, $path);
    }

    /**
     * Add a library prefix to the autoloader, eg: 'Twig_', or 'Swift_'
     *
     * @static
     * @param $prefix
     * @param $path
     */
    public static function addPrefix($prefix, $path)
    {
        self::$_options['loader']->registerPrefix($prefix, $path);
    }

    /**
     * Register the autoloader namespaces or prefixes thus far.
     *
     * @static
     *
     */
    public static function register()
    {
        self::$_options['loader']->register();
    }

    /**
     * Check if a namespace has been registered. This is a workaround as the default self::$_options['loader']
     * class does not have an exists() method.
     *
     * @static
     * @param  string $key
     * @return bool
     */
    public static function exists($key)
    {
        return isset(self::$_registeredNamespaces[$key]);
    }

}
