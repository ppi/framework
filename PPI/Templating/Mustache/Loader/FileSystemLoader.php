<?php
/**
 * This file is part of the PPI Framework.
 *
 * @package     Templating
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 *
 */

namespace PPI\Templating\Mustache\Loader;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * This engine knows how to render Mustache templates.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class FileSystemLoader extends \Mustache_Loader_FilesystemLoader
{
    protected $locator;
    protected $parser;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface        $locator A FileLocatorInterface instance
     * @param TemplateNameParserInterface $parser  A TemplateNameParserInterface instance
     */
    public function __construct(FileLocatorInterface $locator, TemplateNameParserInterface $parser)
    {
        $this->locator = $locator;
        $this->parser = $parser;
        $this->cache = array();
    }

    /**
     * Helper function for getting a Mustache template file name.
     *
     * @param string $name
     *
     * @return string Template file name
     */
    protected function getFileName($name)
    {
        $name = (string) $name;

        try {
            $template = $this->parser->parse($name);
            $file = $this->locator->locate($template);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to find template "%s".', $name));
        }

        return $file;
    }
}
