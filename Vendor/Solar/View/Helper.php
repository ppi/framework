<?php
/**
 * 
 * Abstract Solar_View_Helper class.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper General-purpose view helpers.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Helper.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
abstract class Solar_View_Helper extends Solar_Base {
    
    /**
     * 
     * Reference to the parent Solar_View object.
     * 
     * @var Solar_View
     * 
     */
    protected $_view;
    
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
        if (empty($this->_config['_view']) ||
            ! $this->_config['_view'] instanceof Solar_View) {
            // we need the parent view object
            throw Solar::exception(
                get_class($this),
                'ERR_VIEW_NOT_SET',
                "Config key '_view' not set, or not Solar_View object"
            );
        }
        $this->_view = $this->_config['_view'];
        unset($this->_config['_view']);
    }
}
