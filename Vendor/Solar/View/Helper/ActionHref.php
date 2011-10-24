<?php
/**
 * 
 * Helper to build an escaped href or src attribute value for an action URI.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ActionHref.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_ActionHref extends Solar_View_Helper
{
    /**
     * 
     * Internal URI object for creating links.
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
     * Returns an escaped href or src attribute value for an action URI.
     * 
     * @param Solar_Uri_Action|string $spec The href or src specification.
     * 
     * @return string
     * 
     */
    public function actionHref($spec = null)
    {
        if ($spec instanceof Solar_Uri_Action) {
            // already an action uri object
            $href = $spec->get();
        } elseif ($spec) {
            // build-and-fetch the string as an action spec
            $href = $this->_uri->quick($spec);
        } else {
            // empty spec, use current action
            $href = $this->_uri->get();
        }
        
        return $this->_view->escape($href);
    }
}
