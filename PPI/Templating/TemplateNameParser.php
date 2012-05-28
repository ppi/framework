<?php

/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */

namespace PPI\Templating;

use Symfony\Component\Templating\TemplateNameParser as BaseTemplateNameParser;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;

/**
 * TemplateNameParser converts template names from the short notation
 * "module:template.format.engine" to TemplateReferenceInterface instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Paul Dragoonis <paul@ppi.io>
 */

class TemplateNameParser extends BaseTemplateNameParser {

	protected $_cache = array();
	
	/**
	 * Parses a template to an array of parameters.
	 *
	 * @param string $name A template name
	 *
	 * @return TemplateReferenceInterface A template
	 */
    public function parse($name) {
        if ($name instanceof TemplateReferenceInterface) {
            return $name;
        } elseif (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        // normalize name
        $name = str_replace(':/', ':', preg_replace('#/{2,}#', '/', strtr($name, '\\', '/')));

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('Template name "%s" contains invalid characters.', $name));
        }

        $parts = explode(':', $name);
        if (3 !== count($parts)) {
            throw new \InvalidArgumentException(sprintf('Template name "%s" is not valid (format is "module:template.format.engine").', $name));
        }
		
        $elements = explode('.', $parts[2]);
        if (3 > count($elements)) {
            throw new \InvalidArgumentException(sprintf('Template name "%s" is not valid (format is "module:template.format.engine").', $name));
        }
		
        $engine = array_pop($elements);
        $format = array_pop($elements);
		$module = $parts[0];
		$controller = $parts[1];
        $template = new TemplateReference($module, $controller, implode('.', $elements), $format, $engine);

        return $this->cache[$name] = $template;
    }

    /**
     * Convert a filename to a template.
     *
     * @param string $file The filename
     *
     * @return TemplateReferenceInterface A template
     */
    public function parseFromFilename($file)
    {
        $parts = explode('/', strtr($file, '\\', '/'));

        $elements = explode('.', array_pop($parts));
        if (3 > count($elements)) {
            return false;
        }
        $engine = array_pop($elements);
        $format = array_pop($elements);

        return new TemplateReference('', implode('/', $parts), implode('.', $elements), $format, $engine);
    }

}