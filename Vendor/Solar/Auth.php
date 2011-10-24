<?php
/**
 * 
 * Factory class for authentication adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Auth User authentication systems.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Auth.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Auth extends Solar_Factory
{
    /**
     * 
     * The user is anonymous/unauthenticated (no attempt has been made to 
     * authenticate).
     * 
     * @const string
     * 
     */
    const ANON = 'ANON';
    
    /**
     * 
     * The max time for authentication has expired.
     * 
     * @const string
     * 
     */
    const EXPIRED = 'EXPIRED';
    
    /**
     * 
     * The authenticated user has been idle for too long.
     * 
     * @const string
     * 
     */
    const IDLED = 'IDLED';
    
    /**
     * 
     * The user is authenticated and has not timed out.
     * 
     * @const string
     * 
     */
    const VALID = 'VALID';
    
    /**
     * 
     * The user attempted authentication but failed.
     * 
     * @const string
     * 
     */
    const WRONG = 'WRONG';
    
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class, for example 'Solar_Auth_Adapter_File'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth = array(
        'adapter' => 'Solar_Auth_Adapter_None',
    );
}
