<?php
/**
 * 
 * Returns a URI object for the current action.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ActionUri.php 4499 2010-03-08 16:02:08Z pmjones $
 * 
 */
class Solar_View_Helper_ActionUri extends Solar_View_Helper
{
    /**
     * 
     * Internal URI object for cloning.
     * 
     * @var Solar_Uri_Action
     * 
     */
    protected $_uri = null;
    
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
        $this->_uri = Solar::factory('Solar_Uri_Action');
    }
    
    /**
     * 
     * Returns a URI object for the current action.
     * 
     * @param string $path An optional path to replace the current path.
     * 
     * @return Solar_Uri_Action
     * 
     */
    public function actionUri($path = null)
    {
        $uri = clone $this->_uri;
        if ($path !== null) {
            $uri->setPath($path);
        }
        return $uri;
    }
}
