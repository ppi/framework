<?php
/**
 * 
 * Generates a anchor linking to the TypeKey login site.
 * 
 * Uses the same TypeKey token as Solar_Auth_Adapter_TypeKey
 * @category Solar
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: TypekeyLink.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_TypekeyLink extends Solar_View_Helper
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string token The TypeKey site identifier token string. If empty,
     *   the helper will use the value from the Solar config file under
     *   the $config['Solar_Auth_Adapter_Typekey']['token'] key.
     * 
     * @config string href The HREF of the TypeKey login service. Default is
     *   "https://www.typekey.com:443/t/typekey/login".
     * 
     * @config bool need_email Whether or not to get the TypeKey user's email address.
     *   Default false.
     * 
     * @config string process_key The process key used in URIs (indicating
     * login, logout, etc).
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_TypekeyLink = array(
        'token'      => null,
        'href'       => "https://www.typekey.com/t/typekey/login",
        'need_email' => false,
        'process_key' => 'process',
    );
    
    /**
     * 
     * Generates a link to the TypeKey login site.
     * 
     * @param string $text The text to display for the link.
     * 
     * @param array $attribs Attributes for the anchor.
     * 
     * @return string
     * 
     */
    public function typekeyLink($text = null, $attribs = null)
    {
        // get a URI processor; defaults to the current URI.
        $uri = Solar::factory('Solar_Uri');
        
        // do not retain the GET 'process' value on the current URI.
        // this prevents double-processing of actions submitted via GET.
        $key = $this->_config['process_key'];
        if (! empty($uri->query[$key])) {
            unset($uri->query[$key]);
        }
        
        // save the current URI as the return location after typekey.
        $return = $uri->get(true);
        
        // now reset the URI to point to the typekey service
        $uri->set($this->_config['href']);
        
        // add the typekey token
        if (empty($this->_config['token'])) {
            $uri->query['t'] = Solar_Config::get('Solar_Auth_Adapter_Typekey', 'token');
        } else {
            $uri->query['t'] = $this->_config['token'];
        }
        
        // convert need_email from true/false to 1/0 and add
        $uri->query['need_email'] = (int) $this->_config['need_email'];
        
        // add the return location
        $uri->query['_return'] = $return;
        
        if (empty($text)) {
            // Preserve behavior of returning only the link if no text is passed.
            return $uri->get(true);
        }
        
        // done!
        return $this->_view->anchor($uri->get(true), $text, $attribs);
    }
}
