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
use Symfony\Component\Yaml\Parser as YamlParser;
use Zend\Stdlib\ArrayUtils;

/**
 * YamlFileLoader loads app configuration from a YAML file.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Config
 */
class YamlFileLoader extends FileLoader
{
    private $yamlParser;

    /**
     * Loads a Yaml file.
     *
     * @param  mixed                     $file The resource
     * @param  string                    $type The resource type
     * @return array                     Array with configuration
     * @throws \InvalidArgumentException
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $content = $this->loadFile($path);

        // empty file
        if (null === $content) {
            return array();
        }

        // imports (Symfony)
        $content = $this->parseImports($content, $path);

        // @include (Zend)
        $content = $this->parseIncludes($content, $path);

        // not an array
        if (!is_array($content)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

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

    /**
     * Loads a YAML file.
     *
     * @param  string                   $file
     * @return array                    The file content
     * @throws InvalidArgumentException
     */
    protected function loadFile($file)
    {
        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        return $this->yamlParser->parse(file_get_contents($file));
    }

    /**
     * Parses all imports. We support this to make Symfony users happy.
     *
     * @param  array  $content
     * @param  string $file
     * @return array
     */
    protected function parseImports($content, $file)
    {
        if (!isset($content['imports'])) {
            return $content;
        }

        foreach ($content['imports'] as $import) {
            $this->setCurrentDir(dirname($file));
            $content = ArrayUtils::merge($this->import($import['resource'], null, isset($import['ignore_errors'])
                ? (Boolean) $import['ignore_errors'] : false, $file), $content);
        }

        unset($content['imports']);

        return $content;
    }

    /**
     * Process the array for @include. We support this to make Zend users happy.
     * @see http://framework.zend.com/manual/2.0/en/modules/zend.config.reader.html#zend-config-reader-yaml
     *
     * @param  array  $content
     * @param  string $file
     * @return array
     */
    protected function parseIncludes(array $content, $file)
    {
        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $content[$key] = $this->parseIncludes($value, $file);
            }

            if ('@include' === trim($key)) {
                $this->setCurrentDir(dirname($file));
                unset($content[$key]);
                $content = array_replace_recursive($content, $this->import($value, null, false, $file));
            }
        }

        return $content;
    }
}
