<?php
/**
 * 
 * Helper for locale strings (no escaping is applied).
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: GetTextRaw.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_GetTextRaw extends Solar_View_Helper
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string class The class for locale translations.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_GetTextRaw = array(
        'class' => 'Solar',
    );
    
    /**
     * 
     * The locale class-space key to use.
     * 
     * @var string
     * 
     */
    public $_class;
    
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
        $this->_class = $this->_config['class'];
    }
    
    /**
     * 
     * Returns a localized string WITH NO ESCAPING.
     * 
     * @param string $key The locale key to look up from the class.
     * 
     * @param int|float $num A number to help determine if the
     * translation should return singluar or plural.
     * 
     * @param array $replace If an array, will call vsprintf() on the
     * localized string using the replacements in the array.
     * 
     * @return string The translated locale string.
     * 
     */
    public function getTextRaw($key, $num = 1, $replace = null)
    {
        static $locale;
        if (! $locale) {
            $locale = Solar_Registry::get('locale');
        }
        return $locale->fetch($this->_class, $key, $num, $replace);
    }
    
    /**
     * 
     * Sets the class used for translations.
     * 
     * You can use this method in a view like so:
     * 
     * {{code: php
     *     $this->getHelper('getTextRaw')->setClass('Some_Class');
     * }}
     * 
     * @param string $class The class used for translations.
     * 
     * @return void
     * 
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }
}
