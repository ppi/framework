<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Templating\Php;

use Symfony\Component\Templating\Storage\FileStorage,
    Symfony\Component\Templating\Loader\LoaderInterface,
    Symfony\Component\Config\FileLocatorInterface,
    Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * FilesystemLoader is a loader that read templates from the filesystem.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 * @author     Paul Dragoonis <paul@ppi.io>
 * @package    PPI
 * @subpackage Templating
 */
class FileSystemLoader implements LoaderInterface
{
    /**
     * @todo Add inline documentation.
     *
     * @var FileLocatorInterface
     */
    protected $locator;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface $locator A FileLocatorInterface instance
     *
     * @return void
     */
    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Loads a template.
     *
     * @param TemplateReferenceInterface $template A template
     *
     * @return Storage|boolean False if the template cannot be loaded, a Storage instance otherwise
     */
    public function load(TemplateReferenceInterface $template)
    {
        try {
            $file = $this->locator->locate($template);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return new FileStorage($file);
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param TemplateReferenceInterface $template The template name as an array
     * @param integer                    $time     The last modification time of the cached template (timestamp)
     *
     * @return integer|boolean
     */
    public function isFresh(TemplateReferenceInterface $template, $time)
    {
        if (false === $storage = $this->load($template)) {
            return false;
        }

        if (!is_readable((string) $storage)) {
            return false;
        }

        return filemtime((string) $storage) < $time;
    }

}
