<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Config;

use Symfony\Component\Filesystem\Filesystem;
use Zend\Stdlib\ArrayUtils;

/**
 * ConfigManager extends ConfigLoader capabilities with lazy-loading and a caching mechanism,
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class ConfigManager extends ConfigLoader
{
    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @var boolean
     */
    protected $cacheEnabled;

    /**
     * @var array
     */
    protected $configs = array();

    /**
     * @var null|array
     */
    protected $mergedConfig;

    /**
     * @var bool
     */
    protected $skipConfig = false;

    /**
     * Constructor.
     *
     * @param string       $cachePath
     * @param boolean      $cacheEnabled
     * @param string|array $paths        A path or an array of paths where to look for resources
     */
    public function __construct($cachePath, $cacheEnabled, $paths = array())
    {
        $this->cachePath = $cachePath;
        $this->cacheEnabled = (bool) $cacheEnabled;

        if ((true === $this->cacheEnabled) && file_exists($this->cachePath)) {
            $this->skipConfig = true;
            $this->mergedConfig = require $this->cachePath;
        }

        parent::__construct($paths);
    }

    /**
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     * @return $this
     */
    public function addConfig($resource, $type)
    {
        if (!$this->skipConfig) {
            $this->configs[] = array(
                'resource'  => $resource,
                'type'      => $type
            );
        }

        return $this;
    }

    public function getMergedConfig()
    {
        if (null === $this->mergedConfig) {
            $this->mergedConfig = array();
            foreach ($this->configs as $config) {
                $this->mergedConfig = ArrayUtils::merge($this->mergedConfig,
                    $this->load($config['resource'], $config['type']));
            }

            if ($this->cacheEnabled) {
                $mode = 0666 & ~umask();
                $content = "<?php\nreturn " . var_export($this->mergedConfig, 1) . ';';
                $filesystem = new Filesystem();
                $filesystem->dumpFile($this->cachePath, $content, $mode);
            }
        }

        return $this->mergedConfig;
    }
}
