<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Config\Loader;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * IniFileLoader loads parameters from INI files.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class IniFileLoader extends FileLoader
{
    /**
     * Loads a resource.
     *
     * @param mixed  $file The resource
     * @param string $type The resource type
     *
     * @throws InvalidArgumentException When ini file is not valid
     */
    public function load($file, $type = null)
    {
        $path   = $this->locator->locate($file);
        $config = array();

        $result = parse_ini_file($path, true);
        if (false === $result || array() === $result) {
            throw new InvalidArgumentException(sprintf('The "%s" file is not valid.', $file));
        }

        if (isset($result['parameters']) && is_array($result['parameters'])) {
            $config['parameters'] = array();
            foreach ($result['parameters'] as $key => $value) {
                $config['parameters'][$key] = $value;
            }
        }
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return bool true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'ini' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
