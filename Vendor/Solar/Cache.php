<?php
/**
 * 
 * Factory class for cache adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Cache Caching systems.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Cache.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Cache extends Solar_Factory
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class for the factory, default 
     * 'Solar_Cache_Adapter_File'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache = array(
        'adapter' => 'Solar_Cache_Adapter_File',
    );
}
