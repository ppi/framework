<?php
/**
 * 
 * Locale file.  Returns the strings for a specific language.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: en_US.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 */
return array(
    'VALID'   => 'Welcome back!',
    'ANON'    => 'Not signed in.',
    'LOGOUT'  => 'Thank you for visiting.',
    'WRONG'   => 'Your username and password did not match.  Please try again.',
    'REPLAY'  => 'The time window for processing closed.  Please try again.',
    'EXPIRED' => 'Your session has expired.  Please sign in again.',
    'IDLED'   => 'Your session has been idle for too long.  Please sign in again.',
    'ERR_PHP_SESSION_IDLE' => 'The .ini value for session.gc_maxlifetime is shorter than the authentication idle time.',
    'ERR_PHP_SESSION_EXPIRE' => 'The .ini value for session.cookie_lifetime  is shorter than the authentication expire time.',
);
