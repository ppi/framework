<?php
/**
 * 
 * Manages locale strings for all Solar classes.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Locale.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_Locale extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string code The default locale code to use.
     * 
     * @var array
     * 
     */
    protected $_Solar_Locale = array(
        'code' => 'en_US',
    );
    
    /**
     * 
     * Collected translation strings arranged by class and key.
     * 
     * @var array
     * 
     */
    public $trans = array();
    
    /**
     * 
     * The current locale code.
     * 
     * @var string
     * 
     */
    protected $_code = 'en_US';
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        $this->setCode($this->_config['code']);
    }
    
    /**
     * 
     * Sets the locale code and clears out previous translations.
     * 
     * @param string $code A locale code, for example, 'en_US'.
     * 
     * @return void
     * 
     */
    public function setCode($code)
    {
        // set the code
        $this->_code = $code;
        
        // reset the strings
        $this->trans = array();
    }
    
    /**
     * 
     * Returns the current locale code.
     * 
     * @return string The current locale code, for example, 'en_US'.
     * 
     */
    public function getCode()
    {
        return $this->_code;
    }
    
    /**
     * 
     * Returns ISO 3166 country code for current locale code.
     * 
     * This is basically just the last two uppercase letters
     * from the locale code.
     * 
     * @return string
     * 
     */
    public function getCountryCode()
    {
        return substr($this->_code, -2);
    }
    
    /**
     * 
     * Returns RFC 1766 (XHTML) language code for current locale code.
     * 
     * This is the same as the locale code, but using a dash instead of an
     * underscore as a separator.
     * 
     * @return string
     * 
     */
    public function getLanguageCode()
    {
        return str_replace('_', '-', $this->_code);
    }
    
    /**
     * 
     * Returns the translated locale string for a class and key.
     * 
     * Loads translations as needed.
     * 
     * You can also pass an array of replacement values.  If the `$replace`
     * array is sequential, this method will use it with vsprintf(); if the
     * array is associative, this method will replace "{:key}" with the array
     * value.
     * 
     * For example:
     * 
     * {{code: php
     *     
     *     $locale = Solar_Registry('locale');
     *     
     *     $page  = 2;
     *     $pages = 10;
     *     
     *     // given a class of 'Solar_Example' with a locale string
     *     // TEXT_PAGES => 'Page %d of %d', uses vsprintf() internally:
     *     $replace = array($page, $pages);
     *     echo $locale->fetch('Solar_Example', 'TEXT_PAGES', $pages, $replace);
     *     // echo "Page 2 of 10"
     *     
     *     // given a class of 'Solar_Example' with a locale string
     *     // TEXT_PAGES => 'Page {:page} of {:pages}', uses str_replace()
     *     // internally:
     *     $replace = array('page' => $page, 'pages' => $pages);
     *     echo $locale->fetch('Solar_Example', 'TEXT_PAGES', $pages, $replace);
     *     // echo "Page 2 of 10"
     * }}
     * 
     * @param string|object $spec The class name (or object) for the translation.
     * 
     * @param string $key The translation key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return string A translated locale string.
     * 
     * @see _trans()
     * 
     * @see Solar_Base::locale()
     * 
     */
    public function fetch($spec, $key, $num = 1, $replace = null)
    {
        // is the spec an object?
        if (is_object($spec)) {
            // yes, find its class
            $class = get_class($spec);
        } else {
            // no, assume the spec is a class name
            $class = (string) $spec;
        }
        
        // does the translation key exist for this class?
        // pre-empts the stack check.
        $string = $this->_trans($class, $key, $num, $replace);
        if ($string !== null) {
            return $string;
        }
        
        // find all parents of the class, including the class itself
        $parents = array_reverse(Solar_Class::parents($class, true));
        
        // add the vendor namespace to the stack for vendor-wide strings
        $vendor = Solar_Class::vendor($class);
        $parents[] = $vendor;
        
        // add Solar as the final fallback.
        if ($vendor != 'Solar') {
            $parents[] = 'Solar';
        }
        
        // go through all parents and find the first matching key
        foreach ($parents as $parent) {
            
            // do we need to load locale strings for the class?
            if (! array_key_exists($parent, $this->trans)) {
                $this->_load($parent);
            }
        
            // does the key exist for the parent?
            $string = $this->_trans($parent, $key, $num, $replace);
            if ($string !== null) {
                // save it for the class so we don't need to go through the
                // stack again, and then we're done.
                $this->trans[$class][$key] = $this->trans[$parent][$key];
                return $string;
            }
        }
        
        // never found a translation, return the requested key.
        return $key;
    }
    
    /**
     * 
     * Returns an existing class/key/num string from the translation array.
     * 
     * @param string $class The translation class.
     * 
     * @param string $key The translation key.
     * 
     * @param mixed $num Helps determine if we need a singular or plural
     * translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return string The translation string if it exists, or null if it
     * does not.
     * 
     */
    protected function _trans($class, $key, $num = 1, $replace = null)
    {
        if (! array_key_exists($class, $this->trans) ||
            ! array_key_exists($key, $this->trans[$class])) {
            // class or class-key does not exist
            return null;
        }
        
        // get the translation of the key and force to an array.
        $trans = (array) $this->trans[$class][$key];
        
        // find the number-appropriate version of the
        // translated key, if multiple values exist.
        if ($num != 1 && ! empty($trans[1])) {
            $string = $trans[1];
        } else {
            $string = $trans[0];
        }
        
        // do replacements?
        if ($replace) {
            // force to an array first
            $replace = (array) $replace;
            
            // by vsprintf(), or by str_replace?()
            $key = key($replace);
            if (is_int($key)) {
                // sequential array, use vsprintf()
                $string = vsprintf($string, $replace);
            } else {
                // associative array, use str_replace()
                foreach ($replace as $key => $val) {
                    $string = str_replace("{:$key}", (string) $val, $string);
                }
            }
        }
        
        // done!
        return $string;
    }
    
    /**
     * 
     * Loads the translation array for a given class.
     * 
     * @param string $class The class name to load translations for.
     * 
     * @return void
     * 
     */
    protected function _load($class)
    {
        // build the file name.  note that we use the fixdir()
        // method, which automatically replaces '/' with the
        // correct directory separator.
        $base = str_replace('_', '/', $class);
        $file = Solar_Dir::fix($base . '/Locale/')
              . $this->_code . '.php';
        
        // can we find the file?
        $target = Solar_File::exists($file);
        if ($target) {
            // put the locale values into the shared locale array
            $this->trans[$class] = (array) Solar_File::load($target);
        } else {
            // could not find file.
            // fail silently, as it's often the case that the
            // translation file simply doesn't exist.
            $this->trans[$class] = array();
        }
    }
}