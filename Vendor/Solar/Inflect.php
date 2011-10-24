<?php
/**
 * 
 * Applies inflections to words: singular, plural, camel, underscore, etc.
 * 
 * @category Solar
 * 
 * @package Solar_Inflect Word-inflection tools.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Inflect.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Inflect extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config array identical Words that do not change from singular to plural.
     * 
     * @config array irregular Irregular singular-to-plural inflections.
     * 
     * @config array to_singular Rules for preg_replace() to convert plurals to singulars.
     * 
     * @config array to_plural Rules for preg_replace() to convert singulars to plurals.
     * 
     * @var array
     * 
     */
    protected $_Solar_Inflect = array(
        'identical'   => array(),
        'irregular'   => array(),
        'to_singular' => array(),
        'to_plural'   => array(),
    );
    
    /**
     * 
     * A list of words that are the same in singular and plural.
     * 
     * This list is adapted from Ruby on Rails ActiveSupport inflections.
     * 
     * @var array
     * 
     */
    protected $_identical = array(
        'equipment',
        'fish',
        'information',
        'money',
        'rice',
        'series',
        'sheep',
        'species',
    );
    
    /**
     * 
     * Irregular singular-to-plural conversions.
     * 
     * Array format is "singular" => "plural" and are literal text, not
     * regular expressions.
     * 
     * This list is adapted from Ruby on Rails ActiveSupport inflections.
     * 
     * @var array
     * 
     */
    protected $_irregular = array(
        'child'  => 'children',
        'man'    => 'men',
        'move'   => 'moves',
        'person' => 'people',
        'sex'    => 'sexes',
    );
    
    /**
     * 
     * Regex rules for converting plural to singular.
     * 
     * Array format is "pattern" => "replacement" for [[php::preg_replace() | ]].
     * 
     * All patterns are treated as '/pattern$/i'.
     * 
     * This list is adapted from Ruby on Rails ActiveSupport inflections.
     * 
     * @var array
     * 
     */
    protected $_to_singular = array(
        's'                    => '',
        '(n)ews'               => '$1ews',
        '([ti])a'              => '$1um',
        '((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses' => '$1$2sis',
        '(^analy)ses'          => '$1sis',
        '([^f])ves'            => '$1fe',
        '(hive)s'              => '$1',
        '(tive)s'              => '$1',
        '([lr])ves'            => '$1f',
        '([^aeiouy]|qu)ies'    => '$1y',
        '(s)eries'             => '$1eries',
        '(m)ovies'             => '$1ovie',
        '(x|ch|ss|sh)es'       => '$1',
        '([m|l])ice'           => '$1ouse',
        '(bus)es'              => '$1',
        '(o)es'                => '$1',
        '(shoe)s'              => '$1',
        '(cris|ax|test)es'     => '$1is',
        '(octop|vir)i'         => '$1us',
        '(alias|status)es'     => '$1',
        '^(ox)en'              => '$1',
        '(vert|ind)ices'       => '$1ex',
        '(matr)ices'           => '$1ix',
        '(quiz)zes'            => '$1',
    );
    
    /**
     * 
     * Regex rules for converting singular to plural.
     * 
     * Array format is "pattern" => "replacement" for [[php::preg_replace() | ]].
     * 
     * All patterns are treated as '/pattern$/i'.
     * 
     * This list is taken from Ruby on Rails ActiveSupport inflections.
     * 
     * @var array
     * 
     */
    protected $_to_plural = array(
        ''                     => 's',
        's'                    => 's',
        '(ax|test)is'          => '$1es',
        '(octop|vir)us'        => '$1i',
        '(alias|status)'       => '$1es',
        '(bu)s'                => '$1ses',
        '(buffal|tomat)o'      => '$1oes',
        '([ti])um'             => '$1a',
        'sis'                  => 'ses',
        '(?:([^f])fe|([lr])f)' => '$1$2ves',
        '(hive)'               => '$1s',
        '([^aeiouy]|qu)y'      => '$1ies',
        '(x|ch|ss|sh)'         => '$1es',
        '(matr|vert|ind)ix|ex' => '$1ices',
        '([m|l])ouse'          => '$1ice',
        '^(ox)'                => '$1en',
        '(quiz)'               => '$1zes',
    );
    
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
        
        // append to the default arrays from configs
        $list = array('identical', 'irregular', 'to_singular', 'to_plural');
        foreach ($list as $key) {
            if ($this->_config[$key]) {
                $var = "_$key";
                $this->$var = array_merge(
                    $this->$var,
                    (array) $this->_config[$key]
                );
            }
        }
        
        // reverse rules so they are processed in LIFO order
        $this->_to_plural   = array_reverse($this->_to_plural);
        $this->_to_singular = array_reverse($this->_to_singular);
    }
    
    /**
     * 
     * Returns a singular word as a plural.
     *
     * @param string $str A singular word.
     * 
     * @return string The plural form of the word.
     * 
     */
    public function toPlural($str)
    {
        $key = strtolower($str);
        
        // look for words that are the same either way
        if (in_array($key, $this->_identical)) {
            return $str;
        }
        
        // look for irregular words
        foreach ($this->_irregular as $key => $val) {
            $find = "/(.*)$key\$/i";
            $repl = "\$1$val";
            if (preg_match($find, $str)) {
                return preg_replace($find, $repl, $str);
            }
        }
        
        // apply normal rules
        foreach($this->_to_plural as $find => $repl) {
            $find = '/' . $find . '$/i';
            if (preg_match($find, $str)) {
                return preg_replace($find, $repl, $str);
            }
        }
        
        // couldn't find a plural form
        return $str;
    }
    
    /**
     * 
     * Returns a plural word as a singular.
     *
     * @param string $str A plural word.
     * 
     * @return string The singular form of the word.
     * 
     */
    public function toSingular($str)
    {
        $key = strtolower($str);
        
        // look for words that are the same either way
        if (in_array($key, $this->_identical)) {
            return $str;
        }
        
        // look for irregular words
        // note that we flip singulars and plurals
        $list = array_flip($this->_irregular);
        foreach ($list as $key => $val) {
            $find = "/(.*)$key\$/i";
            $repl = "\$1$val";
            if (preg_match($find, $str)) {
                return preg_replace($find, $repl, $str);
            }
        }
        
        // apply normal rules
        foreach($this->_to_singular as $find => $repl) {
            $find = '/' . $find . '$/i';
            if (preg_match($find, $str)) {
                return preg_replace($find, $repl, $str);
            }
        }
        
        // couldn't find a singular form
        return $str;
    }
    
    /**
     * 
     * Returns any string, converted to using dashes with only lowercase 
     * alphanumerics.
     * 
     * @param string $str The string to convert.
     * 
     * @return string The converted string.
     * 
     */
    public function toDashes($str)
    {
        $str = preg_replace('/[^a-z0-9 _-]/i', '', $str);
        $str = $this->camelToDashes($str);
        $str = preg_replace('/[ _-]+/', '-', $str);
        return $str;
    }
    
    /**
     * 
     * Returns "foo_bar_baz" as "fooBarBaz".
     * 
     * @param string $str The underscore word.
     * 
     * @return string The word in camel-caps.
     * 
     */
    public function underToCamel($str)
    {
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ', '', $str);
        $str[0] = strtolower($str[0]);
        return $str;
    }
    
    /**
     * 
     * Returns "foo-bar-baz" as "fooBarBaz".
     * 
     * @param string $str The dashed word.
     * 
     * @return string The word in camel-caps.
     * 
     */
    public function dashesToCamel($str)
    {
        $str = ucwords(str_replace('-', ' ', $str));
        $str = str_replace(' ', '', $str);
        $str[0] = strtolower($str[0]);
        return $str;
    }
    
    /**
     * 
     * Returns "foo_bar_baz" as "FooBarBaz".
     * 
     * @param string $str The underscore word.
     * 
     * @return string The word in studly-caps.
     * 
     */
    public function underToStudly($str)
    {
        $str = $this->underToCamel($str);
        $str[0] = strtoupper($str[0]);
        return $str;
    }
    
    /**
     * 
     * Returns "foo-bar-baz" as "FooBarBaz".
     * 
     * @param string $str The dashed word.
     * 
     * @return string The word in studly-caps.
     * 
     */
    public function dashesToStudly($str)
    {
        $str = $this->dashesToCamel($str);
        $str[0] = strtoupper($str[0]);
        return $str;
    }
    
    /**
     * 
     * Returns "camelCapsWord" and "CamelCapsWord" as "Camel_Caps_Word".
     * 
     * @param string $str The camel-caps word.
     * 
     * @return string The word with underscores in place of camel caps.
     * 
     */
    public function camelToUnder($str)
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
        $str = str_replace(' ', '_', ucwords($str));
        return $str;
    }
    
    /**
     * 
     * Returns "camelCapsWord" and "CamelCapsWord" as "camel-caps-word".
     * 
     * @param string $str The camel-caps word.
     * 
     * @return string The word with dashes in place of camel caps.
     * 
     */
    public function camelToDashes($str)
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
        $str = str_replace(' ', '-', ucwords($str));
        return strtolower($str);
    }
    
    /**
     * 
     * Returns "Class_Name" as "Class/Name.php".
     * 
     * @param string $str The class name.
     * 
     * @return string The class as a file name.
     * 
     */
    public function classToFile($str)
    {
        return str_replace('_', '/', $str) . '.php';
    }
}