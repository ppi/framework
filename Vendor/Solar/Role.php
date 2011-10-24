<?php
/**
 * 
 * Class for reading user roles and groups.
 * 
 * @category Solar
 * 
 * @package Solar_Role Mechanisms for reading user roles from various sources.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Role.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Role extends Solar_Factory
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class to use.
     * 
     * @var array
     * 
     */
    protected $_Solar_Role = array(
        'adapter' => 'Solar_Role_Adapter_None',
    );
}
