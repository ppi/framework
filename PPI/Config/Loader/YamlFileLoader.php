<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Config\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * YamlFileLoader loads app configuration from a YAML file.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Config
 */
class YamlFileLoader extends FileLoader
{
    /**
     * Loads a Yaml file.
     *
     * @param mixed  $file The resource
     * @param string $type The resource type
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $content = $this->loadFile($path);

        // empty file
        if (null === $content) {
            $config = array();
        }

        // imports
        $content = $this->parseImports($content, $file);

        // not an array
        if (!is_array($content)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        return $content;
    }

    /**
     * Loads a YAML file.
     *
     * @param string $file
     *
     * @return array The file content
     */
    protected function loadFile($file)
    {
        return Yaml::parse($file);
    }

    /**
     * Parses all imports
     *
     * @param array  $content
     * @param string $file
     */
    private function parseImports($content, $file)
    {
        if (!isset($content['imports'])) {
            return $content;
        }

        foreach ($content['imports'] as $import) {
            $this->setCurrentDir(dirname($file));
            $content = array_merge($content, $this->import($import['resource'], null, isset($import['ignore_errors']) ? (Boolean) $import['ignore_errors'] : false, $file));
        }

        unset($content['imports']);

        return $content;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
