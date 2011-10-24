<?php
/**
 * 
 * Helper for a hidden anti-CSRF element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormCsrf.php 4519 2010-03-16 20:59:02Z pmjones $
 * 
 */
class Solar_View_Helper_FormCsrf extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Cross-site request forgery detector.
     * 
     * @var Solar_Csrf
     * 
     */
    protected $_csrf;
    
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
        $this->_csrf = Solar::factory('Solar_Csrf');
    }
    
    /**
     * 
     * Generates a hidden anti-CSRF element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formCsrf()
    {
        return $this->_view->formHidden(array(
            'name'  => $this->_csrf->getKey(),
            'value' => $this->_csrf->getToken(),
        ));
    }
}
