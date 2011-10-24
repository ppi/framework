<?php
/**
 * 
 * Replaces class page links in source text with XHTML anchors.
 * 
 * Class page links are in this format ...
 * 
 *     [[Class]]
 *     [[Class]]es
 *     [[Class | display this instead]]
 *     [[Class::Page]]
 *     [[Class::$property]]
 *     [[Class::method()]]
 *     [[Class::CONSTANT]]
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ClassPage.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_ClassPage extends Solar_Markdown_Plugin
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string constant A string template for the xml:id for 
     * "Constants" page links.
     * 
     * @config string overview A string template for the xml:id for 
     * "Overview" page links.
     * 
     * @config string method A string template for the xml:id for 
     * individual method page links.
     * 
     * @config string other A string template for the xml:id for 
     * all other types of page links.
     * 
     * @config string property A string template for the xml:id for 
     * "Properties" page links.
     * 
     * @config string package A string template for the xml:id for 
     * "Package" page links.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown_Apidoc_ClassPage = array(
        'constant'  => 'class.{:class}.Constants.{:page}',
        'overview'  => 'class.{:class}.Overview',
        'method'    => 'class.{:class}.{:page}',
        'other'     => 'class.{:class}.{:page}',
        'property'  => 'class.{:class}.Properties.{:page}',
        'package'   => 'package.{:package}',
    );
    
    /**
     * 
     * This is a span plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_span = true;
    
    /**
     * 
     * A list of classes recognized as being native to PHP.
     * 
     * @var array
     * 
     */
    protected $_php_classes = array('Exception');
    
    /**
     * 
     * Parses the source text for Class::Page links.
     * 
     * @param string $text The source text.
     * 
     * @return string The parsed text.
     * 
     */
    public function parse($text)
    {
        $regex = '/\[\[(.*?)(\|.*?)?\]\](\w*)?/';
        return preg_replace_callback(
            $regex,
            array($this, '_parse'),
            $text
        );
    }
    
    /**
     * 
     * Support callback for parsing class page links.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $spec = $matches[1];
        
        // the display text
        if (empty($matches[2])) {
            // no pipe was specified, use the spec as the text
            $text = $spec;
        } else {
            // a pipe was specified; take it off, and trim the rest
            $text = trim(substr($matches[2], 1));
        }
        
        $atch = empty($matches[3]) ? null  : trim($matches[3]);
        
        if (strtolower(substr($spec, 0, 5)) == 'php::') {
            $func = substr($spec, 5);
            $link = $this->_getPhpFunctionLink($func, $text, $atch);
        } else {
            $link = $this->_getClassPageLink($spec, $text, $atch);
        }
        
        return $this->_toHtmlToken($link);
    }
    
    /**
     * 
     * Builds a link to functions on php.net pages.
     * 
     * @param string $func The PHP function name.
     * 
     * @param string $text The displayed text for the link.
     * 
     * @param string $atch Additional non-linked text.
     * 
     * @return string The replacement text.
     * 
     */
    protected function _getPhpFunctionLink($func, $text, $atch)
    {
        $func = trim($func);
        
        if (! $text) {
            $text = $func;
        }
        
        if (substr($func, -2) == '()') {
            $func = substr($func, 0, -2);
        }
        
        $href = "http://php.net/$func";
        
        return '<link xlink:href="' . $this->_escape($href) . '">'
             . $this->_escape($text . $atch)
             . '</link>';
    }
    
    /**
     * 
     * Builds a link to classes on php.net pages.
     * 
     * @param string $class The PHP class.
     * 
     * @param string $page The page for that class, typically a method name.
     * 
     * @param string $text The displayed text for the link.
     * 
     * @param string $atch Additional non-linked text.
     * 
     * @return string The replacement text.
     * 
     */
    protected function _getPhpClassLink($class, $page, $text, $atch)
    {
        if (! $text) {
            $text = $page;
        }
        
        // massage page name
        $page = preg_replace('[^a-zA-Z0-9]', '', $page);
        
        // http://php.net/manual/en/exception.getmessage.php
        $href = "http://php.net/manual/en/"
              . strtolower($class) . '.'
              . strtolower($page) . '.php';
        
        return '<link xlink:href="' . $this->_escape($href) . '">'
             . $this->_escape($text . $atch)
             . '</link>';
    }
    
    /**
     * 
     * Builds a link for class API documentation pages.
     * 
     * @param string $spec The link specification.
     * 
     * @param string $text The displayed text for the link.
     * 
     * @param string $atch Additional non-linked text.
     * 
     * @return string The replacement text.
     * 
     */
    protected function _getClassPageLink($spec, $text, $atch)
    {
        $pos = strpos($spec, '::');
        if ($pos === false) {
            $class = $spec;
            $page  = null;
            if (! $text) {
                $text = $spec;
            }
        } else {
            $class = trim(substr($spec, 0, $pos));
            $page  = trim(substr($spec, $pos + 2));
            if (! $text) {
                $text = $page;
            }
        }
        
        // is it a recognized PHP class?
        if (in_array($class, $this->_php_classes)) {
            return $this->_getPhpClassLink($class, $page, $text, $atch);
        }
        
        // is it a package link?
        if ($class == 'Package') {
            return $this->_getPackageLink($page, $text, $atch);
        }
        
        // what kind of link to build?
        $is_property = false;
        if (! $page) {
            // no page specified
            $tmpl = $this->_config['overview'];
        } elseif (substr($page, 0, 1) == '$') {
            // $property
            $tmpl = $this->_config['property'];
            $page = substr($page, 1);
            $is_property = true;
        } elseif (substr($page, -2) == '()') {
            // method()
            $tmpl = $this->_config['method'];
            $page = substr($page, 0, -2);
        } elseif (strtoupper($page) === $page) {
            // CONSTANT
            $tmpl = $this->_config['constant'];
        } else {
            // other
            $tmpl = $this->_config['other'];
        }
        
        // interpolate values into link template placeholders
        $keys = array('{:class}', '{:page}');
        $vals = array($class, $page);
        $link = str_replace($keys, $vals, $tmpl);
        
        return '<link linkend="' .$this->_escape($link) . '">'
             . $this->_escape($text . $atch)
             . '</link>';
    }
    
    /**
     * 
     * Builds a link to a package page.
     * 
     * @param string $package The package name.
     * 
     * @param string $text The displayed text for the link.
     * 
     * @param string $atch Additional non-linked text.
     * 
     * @return string The replacement text.
     * 
     */
    protected function _getPackageLink($package, $text, $atch)
    {
        $tmpl = $this->_config['package'];
        $keys = array('{:package}');
        $vals = array($package);
        $link = str_replace($keys, $vals, $tmpl);
        
        if (! $text) {
            $text = $package;
        }
        
        return '<link linkend="' .$this->_escape($link) . '">'
             . $this->_escape($text . $atch)
             . '</link>';
    }
}
