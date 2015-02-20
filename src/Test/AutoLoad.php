<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Test;

/**
 * Testing Autoloader
 */
class AutoLoad
{
    /**
     * Will autoload using PSR-0 standards
     *
     * @link http://phpmaster.com/autoloading-and-the-psr-0-standard/
     * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
     * @param  string $className
     * @return void
     */
    public static function autoload($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        require $fileName;
    }

    /**
     * Will register the method with spl_autoload_register
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register('\PPI\Test\AutoLoad::autoload');
    }
}
