<?php
/**
 * 
 * Authentication adapter for TypeKey.
 * 
 * Requires that PHP have been compiled using "--enable-bcmath" or
 * '--with-gmp'.
 * 
 * Based largely on PEAR Auth_Typekey proposal by Daiji Hirata, in
 * particular the DSA signature verification methods.  See the original
 * code at <http://www.uva.ne.jp/Auth_TypeKey/Auth_TypeKey.phps>.
 * 
 * Developed for, and then donated by, Mashery.com <http://mashery.com>.
 * 
 * For more info on TypeKey, see ...
 * 
 * * <http://www.sixapart.com/typekey/api>
 * 
 * * <http://www.sixapart.com/movabletype/docs/tk-apps>
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Daiji Hirata <hirata@uva.ne.jp>
 * 
 * @author Paul M. Jones <pmjones@mashery.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Typekey.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 */
class Solar_Auth_Adapter_Typekey extends Solar_Auth_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string token The TypeKey "site token" id against which
     * authentication requests will be made.
     * 
     * @config int window The signature should have been generated
     * within this many seconds of "now". Default is 10 seconds, to
     * allow for long network latency periods.
     * 
     * @config dependency cache A Solar_Cache dependency for storing 
     * the TypeKey public key data.
     * 
     * @config string cache_key When using a cache, the entry key for
     * the TypeKey public key data.  Default 'typekey_pubkey'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter_Typekey = array(
        'token'     => null,
        'window'    => 10,
        'cache'     => null,
        'cache_key' => 'typekey_pubkey',
    );
    
    /**
     * 
     * A reconstructed "message" to be verified for validity.
     * 
     * @var string
     * 
     * @see Solar_Auth_Adapter_Typekey::isLoginValid()
     * 
     * @see Solar_Auth_Adapter_Typekey::_processLogin()
     * 
     */
    protected $_msg;
    
    /**
     * 
     * Public key as fetched from TypeKey server.
     * 
     * @var string
     * 
     * @see Solar_Auth_Adapter_Typekey::_fetchKeyData()
     * 
     */
    protected $_key;
    
    /**
     * 
     * DSA signature extracted from login attempt GET request vars.
     * 
     * @var string
     * 
     * @see Solar_Auth_Adapter_Typekey::isLoginValid()
     * 
     * @see Solar_Auth_Adapter_Typekey::_processLogin()
     * 
     */
    protected $_sig;
    
    /**
     * 
     * Use bcmath or gmp extension to verify signatures?
     * 
     * @var string
     * 
     */
    protected $_ext;
    
    /**
     * 
     * Cache for the TypeKey public key data.
     * 
     * @var Solar_Cache
     * 
     */
    protected $_cache;
    
    /**
     * 
     * Checks to make sure a GMP or BCMath extension is available.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        if (extension_loaded('gmp')) {
            $this->_ext = 'gmp';
        } elseif (extension_loaded('bcmath')) {
            $this->_ext = 'bc';
        } else {
            throw $this->_exception('ERR_EXTENSION_NOT_LOADED', array(
                'extension' => '(bcmath || gmp)'
            ));
        }
    }
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        if ($this->_config['cache']) {
            $this->_cache = Solar::dependency(
                'Solar_Cache',
                $this->_config['cache']
            );
        }
    }
    
    /**
     * 
     * Is the current page-load a login request?
     * 
     * We can tell because there will be certain GET params in place ...
     * 
     *     &ts=1149633028
     *     &email=user%40example.com
     *     &name=handle
     *     &nick=Moni%20Kerr
     *     &sig=PBG7mN48V9f83hOX5Ao+X9GbmUU=:maoKWgIZpcF1qVFUHf8GbFooAFc=
     * 
     * @return bool
     * 
     */
    public function isLoginRequest()
    {
        return ! empty($this->_request->get['email']) &&
               ! empty($this->_request->get['name']) &&
               ! empty($this->_request->get['nick']) &&
               ! empty($this->_request->get['ts']) &&
               ! empty($this->_request->get['sig']);
    }
    
    /**
     * 
     * Fetches the public key data from TypeKey.
     * 
     * If a cache object was injected at construction time, uses
     * a cached version of the public key instead of hitting the
     * TypeKey server.
     * 
     * The URI used is "http://www.typekey.com/extras/regkeys.txt".
     * 
     * @return array An array with keys 'p', 'q', 'g', and 'pub_key'
     * as extracted from the fetched key string.
     * 
     */
    protected function _fetchKeyData()
    {
        $cache_key = $this->_config['cache_key'];
        if ($this->_cache) {
            $info = $this->_cache->fetch($cache_key);
            if ($info) {
                // cache hit
                return $info;
            }
        }
        
        // cache miss, or no cache.  get from typekey.
        $src = file_get_contents('http://www.typekey.com/extras/regkeys.txt');
        $lines = explode(' ', trim($src));
        foreach ($lines as $line) {
            $val = explode('=', $line);
            $info[$val[0]] = $val[1];
        }
        
        // save in the cache?
        if ($this->_cache) {
            $this->_cache->save($cache_key, $info);
        }
        
        // done
        return $info;
    }
    
    /**
     * 
     * Processes login credentials using either GMP or bcmath functions.
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     */
    protected function _processLogin()
    {
        // get data from the request.
        $email = $this->_request->get('email');
        $name  = $this->_request->get('name');
        $nick  = $this->_request->get('nick');
        $ts    = $this->_request->get('ts');
        
        // are we in the allowed time window?
        if (time() - $ts > $this->_config['window']) {
            // possible replay attack.
            return 'REPLAY';
        }
        
        // get the signature values from the login. note that the sig
        // values need to have pluses converted to spaces because
        // urldecode() doesn't do that for us. thus, we have to re-
        // encode, the raw-decode it.
        $this->_sig = rawurldecode(
            urlencode($this->_request->get('sig'))
        );
        
        // re-create the message for signature comparison.
        // <email>::<name>::<nick>::<ts>
        $this->_msg = "$email::$name::$nick::$ts";
        
        // get the TypeKey public key data
        $this->_key = $this->_fetchKeyData();
        
        // what method for verification?
        $method = '_verify_' . $this->_ext;
        
        // verification routine
        if ($this->$method()) {
            return array(
                'handle'  => $name,  // username
                'email'   => $email, // email
                'moniker' => $nick,  // display name
            );
        } else {
            return false;
        }
    }
    
    /**
     * 
     * DSA verification using GMP.
     * 
     * Uses $this->_msg, $this->_key, and $this->_sig as the data
     * sources.
     * 
     * @return bool True if the message signature is verified using the
     * DSA public key.
     * 
     */
    protected function _verify_gmp()
    {
        $msg = $this->_msg;
        $key = $this->_key;
        $sig = $this->_sig;
        
        list($r_sig, $s_sig) = explode(":", $sig);
        $r_sig = base64_decode($r_sig);
        $s_sig = base64_decode($s_sig);
        
        foreach ($key as $i => $v) {
            $key[$i] = gmp_init($v);
        }
        
        $s1 = gmp_init($this->_gmp_bindec($r_sig));
        $s2 = gmp_init($this->_gmp_bindec($s_sig));
        
        $w = gmp_invert($s2, $key['q']);
        
        $hash_m = gmp_init('0x' . sha1($msg));
        
        $u1 = gmp_mod(gmp_mul($hash_m, $w), $key['q']);
        $u2 = gmp_mod(gmp_mul($s1, $w), $key['q']);
        
        $v = gmp_mod( 
                gmp_mod( 
                    gmp_mul(
                        gmp_powm($key['g'], $u1, $key['p']), 
                        gmp_powm($key['pub_key'], $u2, $key['p'])
                    ), 
                    $key['p']
                ), 
             $key['q']
        );
        
        if (gmp_cmp($v, $s1) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Converts a binary to a decimal value using GMP functions.
     * 
     * @param string $bin The original binary value string.
     * 
     * @return string Decimal value string converted from $bin.
     * 
     */
    protected function _gmp_bindec($bin) 
    {
        $dec = gmp_init(0);
        while (strlen($bin)) {
            $i = ord(substr($bin, 0, 1));
            $dec = gmp_add(gmp_mul($dec, 256), $i);
            $bin = substr($bin, 1);
        }
        return gmp_strval($dec);
    }
    
    /**
     * 
     * DSA verification using bcmath.
     * 
     * Uses $this->_msg, $this->_key, and $this->_sig as the data
     * sources.
     * 
     * @return bool True if the message signature is verified using the
     * DSA public key.
     * 
     */
    protected function _verify_bc()
    {
        $msg = $this->_msg;
        $key = $this->_key;
        $sig = $this->_sig;
        
        list($r_sig, $s_sig) = explode(':', $sig);
        
        $r_sig = base64_decode($r_sig);
        $s_sig = base64_decode($s_sig);
        
        $s1 = $this->_bc_bindec($r_sig);
        $s2 = $this->_bc_bindec($s_sig);
        
        $w = $this->_bc_invert($s2, $key['q']);
        $hash_m = $this->_bc_hexdec(sha1($msg));
        
        $u1 = bcmod(bcmul($hash_m, $w), $key['q']);
        $u2 = bcmod(bcmul($s1, $w), $key['q']);
        
        $v = bcmod(
                bcmod(
                    bcmul(
                        bcmod(bcpowmod($key['g'], $u1, $key['p']), $key['p']),
                        bcmod(bcpowmod($key['pub_key'], $u2, $key['p']), $key['p'])
                    ),
                    $key['p']
                ),
             $key['q']
        );
        
        return (bool) bccomp($v, $s1) == 0;
    }
    
    /**
     * 
     * Converts a hex value string to a decimal value string using
     * bcmath functions.
     * 
     * @param string $hex The original hex value string.
     * 
     * @return string Decimal string converted from $hex.
     * 
     */
    protected function _bc_hexdec($hex)
    {
        $dec = '0';
        while (strlen($hex)) {
            $i = hexdec(substr($hex, 0, 4));
            $dec = bcadd(bcmul($dec, 65536), $i);
            $hex = substr($hex, 4);
        }
        return $dec;
    }
    
    /**
     * 
     * Converts a binary value string to a decimal value string using
     * bcmath functions.
     * 
     * @param string $bin The original binary value string.
     * 
     * @return string Decimal value string converted from $bin.
     * 
     */
    protected function _bc_bindec($bin)
    {
        $dec = '0';
        while (strlen($bin)) {
            $i = ord(substr($bin, 0, 1));
            $dec = bcadd(bcmul($dec, 256), $i);
            $bin = substr($bin, 1);
        }
        return $dec;
    }
    
    /**
     * 
     * Inverts two values using bcmath functions.
     * 
     * @param string $x First value.
     * 
     * @param string $y Second value.
     * 
     * @return string The inverse of $x and $y.
     * 
     */
    protected function _bc_invert($x, $y) 
    {
        while (bccomp($x, 0)<0) { 
            $x = bcadd($x, $y);
        }
        $r = $this->_bc_exgcd($x, $y);
        if ($r[2] == 1) {
            $a = $r[0];
            while (bccomp($a, 0) < 0) {
                $a = bcadd($a, $y);
            }
            return $a;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Finds the extended greatest-common-denominator of two values
     * using bcmath functions.
     * 
     * @param string $x First value.
     * 
     * @param string $y Second value.
     * 
     * @return array Extended GCD of $x and $y.
     * 
     */
    protected function _bc_exgcd($x, $y) 
    {
        $a0 = 1; $a1 = 0;
        
        $b0 = 0; $b1 = 1;
        
        $c = 0;
        
        while ($y > 0) {
            $q = bcdiv($x, $y, 0);
            $r = bcmod($x, $y);
            
            $x = $y; $y = $r;
            
            $a2 = bcsub($a0, bcmul($q, $a1));
            $b2 = bcsub($b0, bcmul($q, $b1));
            
            $a0 = $a1; $a1 = $a2;
            
            $b0 = $b1; $b1 = $b2;
        }
        
        return array($a0, $b0, $x);
    }
}
