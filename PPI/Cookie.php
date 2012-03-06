<?php

/**
 * Cookie class
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppiframework.com
*/
class PPI_Cookie {

	/**
	 * As this is a static called class there is never any direct instantiation, so we
	 * have an init var/function to emulate that. This lets us know if init() as been called.
	 *
	 * @var boolean
	 */
	protected static $_initialized = false;

	/**
	* Holds default/pre-configured options for cookies
	*
	* @var array
	*/
    protected static $_options = array(
        'expire'    => 0,     // Relative time before the cookie expires, 0 for session cookie.
        'path'      => '/',   // Restrict the path that the cookie is available to
        'domain'    => null,  // Restrict the domain that the cookie is available to
        'secure'    => false, // Only transmit cookies over secure connections
        'httponly'  => false, // Only transmit cookies over HTTP, disabling Javascript access
        'salt'      => false, // Used to generate sha key that is prefixed to cookie
    );

	/**
	* Holds which all options have been overriden by setConfig
	* during bootstrap or other areas. This is to allow overriding of
	* configurations loaded from the config file.
	*
	* @var array|null
    */
	protected static $_overriddenKeys = null; // for future implementation


	/**
	 * As this is a static called class there is never any direct instantiation, so we
	 * have an init var/function to emulate that. This lets us know if init() as been called.
	 * @return void
	 */
	protected static function _init() {

		if(self::$_initialized === true) {
			return;
		}
		self::$_initialized = true;

		/**
		 * @todo Test this toArray empty stuff and maybe find something more optimized like skipping
		 * the toArray() step.
		 */
		// If PPI Framework being used, then let's try to fetch the system config
		if(class_exists('PPI_Config') && !empty(PPI_Helper::getConfig()->system->cookie->toArray())) {
			return; // Nothing to do here, accept default values
	    }
		// Fetch values from system config
		$aConfigValues = $oConfig->system->cookie->toArray();

		foreach($aConfigValues as $configKey => $configValue) {
			if(isset(self::$_options[$configKey])) {
				self::$_options[$configKey] = $configValue;
			}
		}
	}

    /**
    * Sets a cookie to be sent back to the browser. If no options passed, uses default or preconfigured options.
    *
    * @param mixed $name The name of the cookie to set
    * @param mixed $value The value of the cookie to set
    * @param string|int $expire A relative string(like '+5 minutes') or a unix timestamp
    * @param array $options Associative array of additional options. - path, domain, secure, httponly, salt.
    *   path     => Relative time before the cookie expires, 0 for session cookie.
    *   domain   => Restrict the path that the cookie is available to
    *   secure   => Only transmit cookies over secure connections
    *   httponly => Only transmit cookies over HTTP, disabling Javascript access
    *   salt     => If string: custom salt to be used
    *               If false : no salt protection
    *               If null  : system default(grab from config)
    * @return boolean
    */
    public static function set($name, $value, $expire = null, array $options = array()) {

        $this->_initialized === false ? self::_init() : null;

        $expire     = isset($expire) || $expire !== null ? $expire : self::$_options['expire'];
        $path       = isset($options['path'])     ? $options['path']     : self::$_options['path'];
        $domain     = isset($options['domain'])   ? $options['domain']   : self::$_options['domain'];
        $secure     = isset($options['secure'])   ? $options['secure']   : self::$_options['secure'];
        $httponly   = isset($options['httponly']) ? $options['httponly'] : self::$_options['httponly'];

        $salt = false;
        if(isset($options['salt'])) {

        	switch(gettype($options['salt'])) {

        		// Custom salt
        		case 'string':
					$salt = $options['salt'];
        			break;

        		// Salting on/off
        		case 'boolean':
        			$salt = $options['salt'];
        			break;

        		// System default
        		default:
        			$salt = self::$_options['salt'];
        			break;
        	}
        }

        // The only case we don't salt is if it is bool(false) so lets salt it !
        if($salt !== false) {
            $value = self::salt($name, $value, $salt) . '~' . $value;
        }

        // If an expire is set if it's a non-numeric value eg: "+1 month" then strtotime() it
        if($expire !== null && is_numeric($expire) === false) {
            $expire = strtotime($expire);
        }

        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
    * Gets the value of a cookie
    *
    * @param string $key cookie name
    * @param mixed $default default value to return
    * @param string|false|null $salt
    *   If string: custom salt to be used
    *   If false : no salt protection
    *   If null  : system default(grab from config)
    */
    public static function get($key, $default = null, $p_salt = null) {

    	$this->_initialized === false ? self::_init() : null;

        if(!isset($_COOKIE[$key])) {
            return $default;
        }

        $cookie = $_COOKIE[$key];
		$salt   = false;
        if($p_salt !== false) {

        	switch(gettype($p_salt)) {

        		// Custom salt
        		case 'string':
					$salt = $p_salt;
        			break;

        		// Salting on/off
        		case 'boolean':
        			$salt = $p_salt;
        			break;

        		// System default
        		default:
        			$salt = self::$_options['salt'];
        			break;
        	}

            // Salt expected, but not found!
            if( ($splitPos = strpos($cookie, '~')) === false) {
                self::delete($key); // Cookie manipulated in user space
                return $default;
            }

            // Seperate the salt and the value
            list($hash, $value) = explode('~', $cookie, 2);

            if($hash !== self::salt($key, $value, $salt)) {
                self::delete($key); // Cookie manipulated in user space
                return $default;
            }
            return $value;
        }
        return $cookie;
    }

	/**
	* Deletes a cookie. (by setting it to null and expiring it)
	*
	* @param string $name
	* @return boolean
	*/
	public static function delete($name) {
		unset($_COOKIE[$name]);
		return setcookie($name, null, -86400, self::$_options['path'], self::$_options['domain'], self::$_options['secure'], self::$_options['httponly']);
	}

    /**
     * Generates a salt string for a cookie based on the name and value.
     * $salt = PPI_Cookie::salt('theme', 'red');
     * @todo review the $salt param and its data types
     * @param   string $name  name of cookie
     * @param   string $value value of cookie
     * @param   string $salt  value of cookie
     * @return  string
     */
    public static function salt($name, $value, $salt = null) {

    	$this->_initialized === false ? self::_init() : null;

        if($salt !== false) {
            $salt = is_string(self::$_options['salt']) ? self::$_options['salt'] : '';
        }

        // Determine the user agent
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

        return sha1($agent . $name . $value . $salt);
    }
}