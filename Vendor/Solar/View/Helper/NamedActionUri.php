<?php
/**
 * 
 * Helper to build an Solar_Action_Uri for a named action from the rewrite
 * rules using data interpolation.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: NamedActionUri.php 4515 2010-03-15 16:42:04Z pmjones $
 * 
 */
class Solar_View_Helper_NamedActionUri extends Solar_View_Helper
{
    /**
     * 
     * The registered rewrite object.
     * 
     * @var Solar_Uri_Rewrite
     * 
     */
    protected $_rewrite;
    
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
        $this->_rewrite = Solar_Registry::get('rewrite');
    }
    
    /**
     * 
     * Returns an escaped href or src attribute value for a named action
     * from the rewrite rules, using data interpolation.
     * 
     * @param string $name The named action from the rewrite rules.
     * 
     * @param array $data Data to interpolate into the token placeholders.
     * 
     * @return string
     * 
     */
    public function namedActionUri($name, $data = null)
    {
        $href = $this->_rewrite->getPath($name, $data);
        return $this->_view->actionUri($href);
    }
}
