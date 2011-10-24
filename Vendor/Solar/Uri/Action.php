<?php
/**
 * 
 * Manipulates and generates action URI strings.
 * 
 * This class is functionally identical to Solar_Uri, except that it
 * automatically adds a prefix to the "path" portion of all URIs.  This
 * makes it easy to work with front-controller and page-controller URIs.
 * 
 * Use the Solar_Uri_Action::$_config key for 'path' to specify
 * the path prefix leading to the front controller, if any.
 * 
 * @category Solar
 * 
 * @package Solar_Uri
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Action.php 4379 2010-02-12 14:06:42Z pmjones $
 * 
 */
class Solar_Uri_Action extends Solar_Uri
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string path A path prefix specifically for actions.  If Apache has used
     *   `SetEnv SOLAR_URI_ACTION_PATH /`, then that is the default value for
     *   this item; otherwise, the default value is "/index.php".
     * 
     * @var array
     * 
     */
    protected $_Solar_Uri_Action = array(
        'path' => '/index.php',
    );
    
    /**
     * 
     * Checks the server variables to see if we have a SOLAR_URI_ACTION_PATH
     * value set from Apache; also pre-sets $this->_request.
     * 
     * In a standard solar system, when mod_rewrite is turned on, it
     * may "SetEnv SOLAR_URI_ACTION_PATH /" as a hint for the default
     * action path. This lets you go from no-rewriting to rewriting in
     * one easy step, rather than having to remember to change the action
     * path in the Solar.config.php file as well.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        $this->_request = Solar_Registry::get('request');
        $this->_Solar_Uri_Action['path'] = $this->_request->server(
            'SOLAR_URI_ACTION_PATH',
            '/index.php'
        );
    }
    
    /**
     * 
     * Returns a path suitable for the front controller to parse (i.e., 
     * without the prefix for subdirectory-based installations).
     * 
     * @return string
     * 
     */
    public function getFrontPath()
    {
        // we use trim() instead of empty() on string elements
        // to allow for string-zero values.
        return (empty($this->path)         ? '' : $this->_pathEncode($this->path))
             . (trim($this->format) === '' ? '' : '.' . urlencode($this->format));
    }
}
