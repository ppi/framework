<?php
/**
 * 
 * Factory to return an HTTP request adapter instance.
 * 
 * @category Solar
 * 
 * @package Solar_Http HTTP request adapters, and a generic HTTP response
 * class.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Request.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Http_Request extends Solar_Factory
{
    /**
     * HTTP method constants.
     */
    const METHOD_DELETE     = 'DELETE';
    const METHOD_GET        = 'GET';
    const METHOD_HEAD       = 'HEAD';
    const METHOD_OPTIONS    = 'OPTIONS';
    const METHOD_POST       = 'POST';
    const METHOD_PUT        = 'PUT';
    const METHOD_TRACE      = 'TRACE';
    
    /**
     * WebDAV method constants.
     */
    const METHOD_COPY       = 'COPY';
    const METHOD_LOCK       = 'LOCK';
    const METHOD_MKCOL      = 'MKCOL';
    const METHOD_MOVE       = 'MOVE';
    const METHOD_PROPFIND   = 'PROPFIND';
    const METHOD_PROPPATCH  = 'PROPPATCH';
    const METHOD_UNLOCK     = 'UNLOCK';
    
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class; for example, 'Solar_Http_Request_Adapter_Stream'
     *   (the default).  When the `curl` extension is loaded, the default is
     *   'Solar_Http_Request_Adapter_Curl'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Http_Request = array(
        'adapter' => 'Solar_Http_Request_Adapter_Stream',
    );
    

    /**
     * 
     * Sets the default adapter to 'Solar_Http_Request_Adapter_Curl' when the
     * curl extension is available.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        if (extension_loaded('curl')) {
            $this->_Solar_Http_Request['adapter'] = 'Solar_Http_Request_Adapter_Curl';
        }
    }

}
