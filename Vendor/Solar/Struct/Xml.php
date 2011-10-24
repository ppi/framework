<?php
/**
 * 
 * A struct with some very minimal XML input/output functionality; attributes, 
 * namespaces, and prefixes are not supported.  Mostly used to convert arrays
 * and XML back and forth.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @author Jeff Moore <jeff@procata.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Xml.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
class Solar_Struct_Xml extends Solar_Struct
{
    /**
     * 
     * When escaping values, use this character set.
     * 
     * @var string
     * 
     */
    protected $_charset = 'UTF-8';
    
    /**
     * 
     * The name of the root node, if any.
     * 
     * @var string
     * 
     */
    protected $_root;
    
    /**
     * 
     * The hierarchical parent of this struct, if any.
     * 
     * @var Solar_Struct
     * 
     */
    protected $_parent;
    
    /**
     * 
     * Sets the hierarchical parent struct.
     * 
     * @param Solar_Struct $parent The hierarchical parent of this struct.
     * 
     * @return void
     * 
     */
    public function setParent(Solar_Struct $parent)
    {
        $this->_parent = $parent;
    }
    
    /**
     * 
     * Returns the hierarchical parent struct, if any.
     * 
     * @return Solar_Struct The hierarchical parent of this struct.
     * 
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * 
     * Frees memory used by this struct, especially references to parent
     * structs down the line.
     * 
     * @return void
     * 
     */
    public function free()
    {
        unset($this->_parent);
        parent::free();
    }
    
    /**
     * 
     * Returns the struct as a string of XML.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        if ($this->_root) {
            return $this->_toString(array(
                $this->_root => $this->_data
            ));
        } else {
            return $this->_toString($this->_data);
        }
    }
    
    /**
     * 
     * Marks the struct and its parents as dirty.
     * 
     * @return void
     * 
     */
    protected function _setIsDirty()
    {
        // set this struct as dirty
        $this->_is_dirty = true;
        
        // set the parent struct as dirty too
        if ($this->_parent) {
            $this->_parent->_setIsDirty();
        }
    }
    
    /**
     * 
     * Loads this object with XML data, replacing any existing XML in the
     * object.
     * 
     * Note that this behavior is **different** from the parent Solar_Struct,
     * where subsequent load() calls merge the data instead of overwriting.
     * 
     * @param string|array|Solar_Struct|SimpleXMLElement|DOMNode $spec Load 
     * with data from the specified source.
     * 
     * @return void
     * 
     */
    public function load($spec)
    {
        $this->_root = null;
        if (is_string($spec)) {
            $this->_loadString($spec);
        } elseif (is_array($spec)) {
            $this->_loadArray($spec);
        } elseif ($spec instanceof Solar_Struct) {
            $this->_loadStruct($spec);
        } elseif ($spec instanceof SimpleXMLElement) {
            $this->_loadSimpleXmlElement($spec);
        } elseif ($spec instanceof DOMNode) {
            $this->_loadDomNode($spec);
        } else {
            throw $this->_exception('ERR_CANNOT_LOAD');
        }
    }
    
    /**
     * 
     * Support method to load data from a string.
     * 
     * @param string $string The source data.
     * 
     * @return void
     * 
     * @see load()
     * 
     */
    protected function _loadString($string)
    {
        $elem = simplexml_load_string($string);
        $this->_loadSimpleXmlElement($elem);
    }
    
    /**
     * 
     * Support method to load data from an array.
     * 
     * @param array $array The source data.
     * 
     * @return void
     * 
     * @see load()
     * 
     */
    protected function _loadArray($array)
    {
        $string = $this->_toString($array);
        $this->_loadString($string);
    }
    
    /**
     * 
     * Support method to load data from a Solar_Struct.
     * 
     * @param Solar_Struct $struct The source data.
     * 
     * @return void
     * 
     * @see load()
     * 
     */
    protected function _loadStruct($struct)
    {
        $string = $this->_toString($struct);
        $this->_loadString($string);
    }
    
    /**
     * 
     * Support method to load data from a SimpleXMLElement.
     * 
     * @param SimpleXMLElement $elem The source data.
     * 
     * @return void
     * 
     * @see load()
     * 
     */
    protected function _loadSimpleXmlElement(SimpleXMLELement $elem)
    {
        $this->_root = $elem->getName();
        $result = $this->_convert($elem, $this->_root, true);
        if (is_array($result)) {
            $this->_data = $result;
        } else {
            $this->_data = array();
        }
    }
    
    /**
     * 
     * Support method to load data from a DOMNode.
     * 
     * @param DOMNode $data The source data.
     * 
     * @return void
     * 
     * @see load()
     * 
     */
    protected function _loadDomNode(DOMNode $data)
    {
        $elem = simplexml_import_dom($data);
        $this->_loadSimpleXmlElement($elem);
    }
    
    /**
     * 
     * Support method to recursively convert a SimpleXMLElement tree to an
     * array or Solar_Struct; **does not** retain XML attributes.
     * 
     * @param SimpleXMLElement $elem The SimpleXMLElement to work with.
     * 
     * @param string $parent The name of the parent element, if any.
     * 
     * @param bool $array If true, return the result as an array; otherwise,
     * return as a Solar_Struct_Xml object.
     * 
     * @return array|Solar_Xml_Struct The converted SimpleXMLElement tree.
     * 
     */
    protected function _convert($elem, $parent = null, $array = false)
    {
        $result = array();
        $exists = false;
        
        foreach ($elem->children() as $key => $val) {
            
            // recursively get child values
            $child = $this->_convert($val, $key);
            
            // check for this element in the array
            if (! $exists && in_array($key, array_keys($result))) {
                
                // if already present, create an indexed array
                $tmp = $result[$key];
                $result[$key] = array();
                
                // add original back to new array
                $result[$key][] = $tmp;
                
                // add new child element and set flag to skip the 
                // in_array/array_keys step
                $result[$key][] = $child;
                $exists = true;
                
            } elseif ($exists) {
                // add to existing element array
                $result[$key][] = $child;
            } else {
                // add a simple item to array
                $result[$key] = $child;
            }
        }
        
        // since this method is called recursively, we will eventually reach
        // a depth where we should return a string instead of an array.
        if (! $result) {
            return (string) $elem;
        }
        
        // done!
        if ($array) {
            return $result;
        } else {
            $struct = clone($this);
            $struct->_data = $result;
            $struct->_root = $parent;
            $struct->setParent($this);
            return $struct;
        }
    }
    
    /**
     * 
     * Support method to recursively convert an array to an XML string; 
     * escapes the array keys and values as it goes.
     * 
     * @param array $array The source data.
     * 
     * @param int $depth Indent the string output by this many levels.
     * 
     * @param string $group Use this as the surrounding group tag name for
     * output.
     * 
     * @return string The source data array converted to an XML string.
     * 
     */
    protected function _toString($array, $depth = null, $group = null)
    {
        $str = '';
        $pad = str_pad('', $depth * 4);
        foreach ($array as $key => $val) {
            
            // key-value pair
            if (is_scalar($val) || $val === null) {
                
                // escape the name
                $name = $this->_escape($key);
                
                // escape the data
                if (is_bool($val)) {
                    // want booleans to be zero or one
                    $data = (int) $val;
                } else {
                    $data = $this->_escape($val);
                }
                
                // build the string
                $str .= "$pad<$name>$data</$name>\n";
                continue;
            }
            
            // child is a sequential array
            if (is_int(key($val))) {
                // note the current key as parent group
                $str .= $this->_toString($val, $depth, $key);
                continue;
            }
            
            // child is an associative array ...
            if ($group) {
                // ... of a parent group
                $name = $this->_escape($group);
            } else {
                // ... of its own
                $name = $this->_escape($key);
            }
            
            $data = $this->_toString($val, $depth + 1);
            $str .= "$pad<$name>\n$data$pad</$name>\n";
        }
        
        return $str;
    }
    
    /**
     * 
     * Support method to escape values for XML.
     * 
     * @param string $val The value to escape.
     * 
     * @return string The escaped string.
     * 
     */
    protected function _escape($val)
    {
        return htmlspecialchars($val, ENT_QUOTES, $this->_charset);
    }
}
