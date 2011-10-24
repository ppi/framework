<?php
/**
 * 
 * Factory class for reading access privileges.
 * 
 * @category Solar
 * 
 * @package Solar_Access User- and role-based access (authorization) control 
 * by controller and action.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Access.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Access extends Solar_Factory
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class, for example 'Solar_Access_Adapter_Open'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Access = array(
        'adapter' => 'Solar_Access_Adapter_Open',
    );
}
