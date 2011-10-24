<?php
/**
 * 
 * Factory class for mail transport adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Transport.php 3850 2009-06-24 20:18:27Z pmjones $
 * 
 */
class Solar_Mail_Transport extends Solar_Factory
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The class to factory.  Default is
     * 'Solar_Mail_Transport_Adapter_Phpmail'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Mail_Transport = array(
        'adapter' => 'Solar_Mail_Transport_Adapter_Phpmail',
    );
}