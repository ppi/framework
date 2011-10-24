<?php
/**
 * 
 * Class for wrapping JSON encoding/decoding functionality.
 * 
 * Given that the json extension to PHP will be enabled by default in
 * PHP 5.2.0+, Solar_Json allows users to get a jump on JSON encoding and
 * decoding early if the native json_* functions are not present.
 * 
 * Solar_Json::encode and Solar_Json::decode functions are designed
 * to pass the same unit tests bundled with the native PHP json ext.
 * 
 * Based largely on the Services_JSON package by Michal Migurski, Matt Knapp
 * and Brett Stimmerman. See the original code at
 * <http://mike.teczno.com/JSON/JSON.phps>
 * 
 * @category Solar
 * 
 * @package Solar_Json JSON data formatting and checking.
 * 
 * @author Michal Migurski <mike-json@teczno.com>
 * 
 * @author Matt Knapp <mdknapp[at]gmail[dot]com>
 * 
 * @author Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Json.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Json extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config bool bypass_ext Flag to instruct Solar_Json to bypass
     *   native json extension, if installed.
     * 
     * @config bool bypass_mb Flag to instruct Solar_Json to bypass
     *   native mb_convert_encoding() function, if
     *   installed.
     * 
     * @config bool noerror Flag to instruct Solar_Json to return null
     *   for values it cannot encode rather than throwing
     *   an exceptions (PHP-only encoding) or PHP warnings
     *   (native json_encode() function).
     * 
     * @var array
     * 
     */
    protected $_Solar_Json = array(
        'bypass_ext' => false,
        'bypass_mb'  => false,
        'noerror'    => false
    );
    
    /**
     * 
     * Marker constants for use in _json_decode()
     * 
     * @constant
     * 
     */
    const SLICE  = 1;
    const IN_STR = 2;
    const IN_ARR = 3;
    const IN_OBJ = 4;
    const IN_CMT = 5;
    
    /**
     * 
     * Nest level counter for determining correct behavior of decoding string
     * representations of numbers and boolean values.
     * 
     * @var int
     */
    protected $_level;
    
    /**
     * 
     * Encodes the mixed $valueToEncode into JSON format.
     * 
     * @param mixed $valueToEncode Value to be encoded into JSON format
     * 
     * @param array $deQuote Array of keys whose values should **not** be
     * quoted in encoded string.
     * 
     * @return string JSON encoded value
     * 
     */
    public function encode($valueToEncode, $deQuote = array())
    {
        if (!$this->_config['bypass_ext'] && function_exists('json_encode')) {
            
            if ($this->_config['noerror']) {
                $old_errlevel = error_reporting(E_ERROR ^ E_WARNING);
            }
            
            $encoded = json_encode($valueToEncode);
            
            if ($this->_config['noerror']) {
                error_reporting($old_errlevel);
            }
        
        } else {
            
            // Fall back to PHP-only method
            $encoded = $this->_json_encode($valueToEncode);
        
        }
        
        // Sometimes you just don't want some values quoted
        if (!empty($deQuote)) {
            $encoded = $this->_deQuote($encoded, $deQuote);
        }
        
        return $encoded;
    
    }
    
    /**
     * 
     * Accepts a JSON-encoded string, and removes quotes around values of
     * keys specified in the $keys array.
     * 
     * Sometimes, such as when constructing behaviors on the fly for "onSuccess"
     * handlers to an Ajax request, the value needs to **not** have quotes around
     * it. This method will remove those quotes and perform stripslashes on any
     * escaped quotes within the quoted value.
     * 
     * @param string $encoded JSON-encoded string
     * 
     * @param array $keys Array of keys whose values should be de-quoted
     * 
     * @return string $encoded Cleaned string
     * 
     */
    protected function _deQuote($encoded, $keys)
    {
        foreach ($keys as $key) {
            $pattern = "/(\"".$key."\"\:)(\".*(?:[^\\\]\"))/U";
            $encoded = preg_replace_callback(
                $pattern,
                array($this, '_stripvalueslashes'),
                $encoded
            );
        }
        
        return $encoded;
    }
    
    /**
     * 
     * Method for use with preg_replace_callback in the _deQuote() method.
     * 
     * Returns \["keymatch":\]\[value\] where value has had its leading and
     * trailing double-quotes removed, and stripslashes() run on the rest of
     * the value.
     * 
     * @param array $matches Regexp matches
     * 
     * @return string replacement string
     * 
     */
    protected function _stripvalueslashes($matches)
    {
        return $matches[1].stripslashes(substr($matches[2], 1, -1));
    }
    
    /**
     * 
     * Decodes the $encodedValue string which is encoded in the JSON format.
     * 
     * For compatibility with the native json_decode() function, this static
     * method accepts the $encodedValue string and an optional boolean value
     * $asArray which indicates whether or not the decoded value should be
     * returned as an array. The default is false, meaning the default return
     * from this method is an object.
     * 
     * For compliance with the [JSON specification][], no attempt is made to 
     * decode strings that are obviously not an encoded arrays or objects. 
     * 
     * [JSON specification]: http://www.ietf.org/rfc/rfc4627.txt
     * 
     * @param string $encodedValue String encoded in JSON format
     * 
     * @param bool $asArray Optional argument to decode as an array.
     * Default false.
     * 
     * @return mixed decoded value
     * 
     */
    public function decode($encodedValue, $asArray = false)
    {
        $first_char = substr(ltrim($encodedValue), 0, 1);
        if ($first_char != '{' && $first_char != '[') {
            return null;
        }
        
        if (!$this->_config['bypass_ext'] && function_exists('json_decode')) {
            return json_decode($encodedValue, (bool) $asArray);
        }
        
        // Fall back to PHP-only method
        $this->_level = 0;
        $checker = Solar::factory('Solar_Json_Checker');
        if ($checker->isValid($encodedValue)) {
            return $this->_json_decode($encodedValue, (bool) $asArray);
        } else {
            return null;
        }
    }
    
    /**
     * 
     * Encodes the mixed $valueToEncode into the JSON format, without use of
     * native PHP json extension.
     * 
     * @param mixed $var Any number, boolean, string, array, or object
     * to be encoded. Strings are expected to be in ASCII or UTF-8 format.
     * 
     * @return mixed JSON string representation of input value
     * 
     */
    protected function _json_encode($var)
    {
        switch (gettype($var)) {
            case 'boolean':
                return $var ? 'true' : 'false';
            
            case 'NULL':
                return 'null';
            
            case 'integer':
                // BREAK WITH Services_JSON:
                // disabled for compatibility with ext/json. ext/json returns
                // a string for integers, so we will to.
                //return (int) $var;
                return (string) $var;
            
            case 'double':
            case 'float':
                // BREAK WITH Services_JSON:
                // disabled for compatibility with ext/json. ext/json returns
                // a string for floats and doubles, so we will to.
                //return (float) $var;
                return (string) $var;
            
            case 'string':
                // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
                $ascii = '';
                $strlen_var = strlen($var);
               
               /**
                * Iterate over every character in the string,
                * escaping with a slash or encoding to UTF-8 where necessary
                */
                for ($c = 0; $c < $strlen_var; ++$c) {
                    
                    $ord_var_c = ord($var{$c});
                    
                    switch (true) {
                        case $ord_var_c == 0x08:
                            $ascii .= '\b';
                            break;
                        case $ord_var_c == 0x09:
                            $ascii .= '\t';
                            break;
                        case $ord_var_c == 0x0A:
                            $ascii .= '\n';
                            break;
                        case $ord_var_c == 0x0C:
                            $ascii .= '\f';
                            break;
                        case $ord_var_c == 0x0D:
                            $ascii .= '\r';
                            break;
                        
                        case $ord_var_c == 0x22:
                        case $ord_var_c == 0x2F:
                        case $ord_var_c == 0x5C:
                            // double quote, slash, slosh
                            $ascii .= '\\'.$var{$c};
                            break;
                        
                        case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                            // characters U-00000000 - U-0000007F (same as ASCII)
                            $ascii .= $var{$c};
                            break;
                        
                        case (($ord_var_c & 0xE0) == 0xC0):
                            // characters U-00000080 - U-000007FF, mask 110XXXXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                            $c += 1;
                            $utf16 = $this->_utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                        
                        case (($ord_var_c & 0xF0) == 0xE0):
                            // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c + 1}),
                                         ord($var{$c + 2}));
                            $c += 2;
                            $utf16 = $this->_utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                        
                        case (($ord_var_c & 0xF8) == 0xF0):
                            // characters U-00010000 - U-001FFFFF, mask 11110XXX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c + 1}),
                                         ord($var{$c + 2}),
                                         ord($var{$c + 3}));
                            $c += 3;
                            $utf16 = $this->_utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                        
                        case (($ord_var_c & 0xFC) == 0xF8):
                            // characters U-00200000 - U-03FFFFFF, mask 111110XX
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c + 1}),
                                         ord($var{$c + 2}),
                                         ord($var{$c + 3}),
                                         ord($var{$c + 4}));
                            $c += 4;
                            $utf16 = $this->_utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                        
                        case (($ord_var_c & 0xFE) == 0xFC):
                            // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                            // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c,
                                         ord($var{$c + 1}),
                                         ord($var{$c + 2}),
                                         ord($var{$c + 3}),
                                         ord($var{$c + 4}),
                                         ord($var{$c + 5}));
                            $c += 5;
                            $utf16 = $this->_utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                    }
                }
                
                return '"'.$ascii.'"';
            
            case 'array':
               /**
                * 
                * As per JSON spec if any array key is not an integer
                * we must treat the the whole array as an object. We
                * also try to catch a sparsely populated associative
                * array with numeric keys here because some JS engines
                * will create an array with empty indexes up to
                * max_index which can cause memory issues and because
                * the keys, which may be relevant, will be remapped
                * otherwise.
                * 
                * As per the ECMA and JSON specification an object may
                * have any string as a property. Unfortunately due to
                * a hole in the ECMA specification if the key is a
                * ECMA reserved word or starts with a digit the
                * parameter is only accessible using ECMAScript's
                * bracket notation.
                * 
                */
                
                // treat as a JSON object
                if (is_array($var) && count($var) &&
                    (array_keys($var) !== range(0, sizeof($var) - 1))) {
                        $properties = array_map(array($this, '_name_value'),
                                            array_keys($var),
                                            array_values($var));
                    
                    return '{' . join(',', $properties) . '}';
                }
                
                // treat it like a regular array
                $elements = array_map(array($this, '_json_encode'), $var);
                
                return '[' . join(',', $elements) . ']';
            
            case 'object':
                $vars = get_object_vars($var);
                
                $properties = array_map(array($this, '_name_value'),
                                        array_keys($vars),
                                        array_values($vars));
                
                return '{' . join(',', $properties) . '}';
            
            default:
                
                if ($this->_config['noerror']) {
                    return 'null';
                }
                
                throw Solar::exception(
                    'Solar_Json',
                    'ERR_CANNOT_ENCODE',
                    gettype($var) . ' cannot be encoded as a JSON string',
                    array('var' => $var)
                );
        }
    }
    
    /**
     * 
     * Decodes a JSON string into appropriate variable.
     * 
     * Note: several changes were made in translating this method from
     * Services_JSON, particularly related to how strings are handled. According
     * to JSON_checker test suite from <http://www.json.org/JSON_checker/>,
     * a JSON payload should be an object or an array, not a string.
     * 
     * Therefore, returning bool(true) for 'true' is invalid JSON decoding
     * behavior, unless nested inside of an array or object.
     * 
     * Similarly, a string of '1' should return null, not int(1), unless
     * nested inside of an array or object.
     * 
     * @param string $str String encoded in JSON format
     * 
     * @param bool $asArray Optional argument to decode as an array.
     * 
     * @return mixed decoded value
     * 
     * @todo Rewrite this based off of method used in Solar_Json_Checker
     * 
     */
    protected function _json_decode($str, $asArray = false)
    {
        $str = $this->_reduce_string($str);
        
        switch (strtolower($str)) {
            case 'true':
                // JSON_checker test suite claims
                // "A JSON payload should be an object or array, not a string."
                // Thus, returning bool(true) is invalid parsing, unless
                // we're nested inside an array or object.
                if (in_array($this->_level, array(self::IN_ARR, self::IN_OBJ))) {
                    return true;
                } else {
                    return null;
                }
                break;
            
            case 'false':
                // JSON_checker test suite claims
                // "A JSON payload should be an object or array, not a string."
                // Thus, returning bool(false) is invalid parsing, unless
                // we're nested inside an array or object.
                if (in_array($this->_level, array(self::IN_ARR, self::IN_OBJ))) {
                    return false;
                } else {
                    return null;
                }
                break;
            
            case 'null':
                return null;
            
            default:
                $m = array();
                
                if (is_numeric($str) || ctype_digit($str) || ctype_xdigit($str)) {
                    // Return float or int, or null as appropriate
                    if (in_array($this->_level, array(self::IN_ARR, self::IN_OBJ))) {
                        return ((float) $str == (integer) $str)
                            ? (integer) $str
                            : (float) $str;
                    } else {
                        return null;
                    }
                    break;
                
                } elseif (preg_match('/^("|\').*(\1)$/s', $str, $m)
                            && $m[1] == $m[2]) {
                    // STRINGS RETURNED IN UTF-8 FORMAT
                    $delim = substr($str, 0, 1);
                    $chrs = substr($str, 1, -1);
                    $utf8 = '';
                    $strlen_chrs = strlen($chrs);
                    
                    for ($c = 0; $c < $strlen_chrs; ++$c) {
                        
                        $substr_chrs_c_2 = substr($chrs, $c, 2);
                        $ord_chrs_c = ord($chrs{$c});
                        
                        switch (true) {
                            case $substr_chrs_c_2 == '\b':
                                $utf8 .= chr(0x08);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\t':
                                $utf8 .= chr(0x09);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\n':
                                $utf8 .= chr(0x0A);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\f':
                                $utf8 .= chr(0x0C);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\r':
                                $utf8 .= chr(0x0D);
                                ++$c;
                                break;
                            
                            case $substr_chrs_c_2 == '\\"':
                            case $substr_chrs_c_2 == '\\\'':
                            case $substr_chrs_c_2 == '\\\\':
                            case $substr_chrs_c_2 == '\\/':
                                if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
                                   ($delim == "'" && $substr_chrs_c_2 != '\\"')) {
                                    $utf8 .= $chrs{++$c};
                                }
                                break;
                            
                            case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
                                // single, escaped unicode character
                                $utf16 = chr(hexdec(substr($chrs, ($c + 2), 2)))
                                       . chr(hexdec(substr($chrs, ($c + 4), 2)));
                                $utf8 .= $this->_utf162utf8($utf16);
                                $c += 5;
                                break;
                            
                            case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                                $utf8 .= $chrs{$c};
                                break;
                            
                            case ($ord_chrs_c & 0xE0) == 0xC0:
                                // characters U-00000080 - U-000007FF, mask 110XXXXX
                                //see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 2);
                                ++$c;
                                break;
                            
                            case ($ord_chrs_c & 0xF0) == 0xE0:
                                // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 3);
                                $c += 2;
                                break;
                            
                            case ($ord_chrs_c & 0xF8) == 0xF0:
                                // characters U-00010000 - U-001FFFFF, mask 11110XXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 4);
                                $c += 3;
                                break;
                            
                            case ($ord_chrs_c & 0xFC) == 0xF8:
                                // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 5);
                                $c += 4;
                                break;
                            
                            case ($ord_chrs_c & 0xFE) == 0xFC:
                                // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= substr($chrs, $c, 6);
                                $c += 5;
                                break;
                        
                        }
                    
                    }
                    
                    if (in_array($this->_level, array(self::IN_ARR, self::IN_OBJ))) {
                        return $utf8;
                    } else {
                        return null;
                    }
                
                } elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {
                    // array, or object notation
                    
                    if ($str{0} == '[') {
                        $stk = array(self::IN_ARR);
                        $this->_level = self::IN_ARR;
                        $arr = array();
                    } else {
                        if ($asArray) {
                            $stk = array(self::IN_OBJ);
                            $obj = array();
                        } else {
                            $stk = array(self::IN_OBJ);
                            $obj = new stdClass();
                        }
                        $this->_level = self::IN_OBJ;
                    }
                    
                    array_push($stk, array('what'  => self::SLICE,
                                           'where' => 0,
                                           'delim' => false));
                    
                    $chrs = substr($str, 1, -1);
                    $chrs = $this->_reduce_string($chrs);
                    
                    if ($chrs == '') {
                        if (reset($stk) == self::IN_ARR) {
                            return $arr;
                        
                        } else {
                            return $obj;
                        
                        }
                    }
                    
                    $strlen_chrs = strlen($chrs);
                    
                    for ($c = 0; $c <= $strlen_chrs; ++$c) {
                        
                        $top = end($stk);
                        $substr_chrs_c_2 = substr($chrs, $c, 2);
                        
                        if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == self::SLICE))) {
                            // found a comma that is not inside a string, array, etc.,
                            // OR we've reached the end of the character list
                            $slice = substr($chrs, $top['where'], ($c - $top['where']));
                            array_push($stk, array('what' => self::SLICE, 'where' => ($c + 1), 'delim' => false));
                            //print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                            
                            if (reset($stk) == self::IN_ARR) {
                                $this->_level = self::IN_ARR;
                                // we are in an array, so just push an element onto the stack
                                array_push($arr, $this->_json_decode($slice));
                            
                            } elseif (reset($stk) == self::IN_OBJ) {
                                $this->_level = self::IN_OBJ;
                                // we are in an object, so figure
                                // out the property name and set an
                                // element in an associative array,
                                // for now
                                $parts = array();
                                
                                if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    // "name":value pair
                                    $key = $this->_json_decode($parts[1]);
                                    $val = $this->_json_decode($parts[2]);
                                    
                                    if ($asArray) {
                                        $obj[$key] = $val;
                                    } else {
                                        $obj->$key = $val;
                                    }
                                } elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    // name:value pair, where name is unquoted
                                    $key = $parts[1];
                                    $val = $this->_json_decode($parts[2]);
                                    
                                    if ($asArray) {
                                        $obj[$key] = $val;
                                    } else {
                                        $obj->$key = $val;
                                    }
                                } elseif (preg_match('/^\s*(["\']["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    // "":value pair
                                    //$key = $this->_json_decode($parts[1]);
                                    // use string that matches ext/json
                                    $key = '_empty_';
                                    $val = $this->_json_decode($parts[2]);
                                    
                                    if ($asArray) {
                                        $obj[$key] = $val;
                                    } else {
                                        $obj->$key = $val;
                                    }
                                }
                            
                            }
                        
                        } elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != self::IN_STR)) {
                            // found a quote, and we are not inside a string
                            array_push($stk, array('what' => self::IN_STR, 'where' => $c, 'delim' => $chrs{$c}));
                            //print("Found start of string at {$c}\n");
                        
                        } elseif (($chrs{$c} == $top['delim']) &&
                                 ($top['what'] == self::IN_STR) &&
                                 ((strlen(substr($chrs, 0, $c)) - strlen(rtrim(substr($chrs, 0, $c), '\\'))) % 2 != 1)) {
                            // found a quote, we're in a string, and it's not escaped
                            // we know that it's not escaped becase there is _not_ an
                            // odd number of backslashes at the end of the string so far
                            array_pop($stk);
                            //print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");
                        
                        } elseif (($chrs{$c} == '[') &&
                                 in_array($top['what'], array(self::SLICE, self::IN_ARR, self::IN_OBJ))) {
                            // found a left-bracket, and we are in an array, object, or slice
                            array_push($stk, array('what' => self::IN_ARR, 'where' => $c, 'delim' => false));
                            //print("Found start of array at {$c}\n");
                        
                        } elseif (($chrs{$c} == ']') && ($top['what'] == self::IN_ARR)) {
                            // found a right-bracket, and we're in an array
                            $this->_level = null;
                            array_pop($stk);
                            //print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        
                        } elseif (($chrs{$c} == '{') &&
                                 in_array($top['what'], array(self::SLICE, self::IN_ARR, self::IN_OBJ))) {
                            // found a left-brace, and we are in an array, object, or slice
                            array_push($stk, array('what' => self::IN_OBJ, 'where' => $c, 'delim' => false));
                            //print("Found start of object at {$c}\n");
                        
                        } elseif (($chrs{$c} == '}') && ($top['what'] == self::IN_OBJ)) {
                            // found a right-brace, and we're in an object
                            $this->_level = null;
                            array_pop($stk);
                            //print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        
                        } elseif (($substr_chrs_c_2 == '/*') &&
                                 in_array($top['what'], array(self::SLICE, self::IN_ARR, self::IN_OBJ))) {
                            // found a comment start, and we are in an array, object, or slice
                            array_push($stk, array('what' => self::IN_CMT, 'where' => $c, 'delim' => false));
                            $c++;
                            //print("Found start of comment at {$c}\n");
                        
                        } elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == self::IN_CMT)) {
                            // found a comment end, and we're in one now
                            array_pop($stk);
                            $c++;
                            
                            for ($i = $top['where']; $i <= $c; ++$i)
                                $chrs = substr_replace($chrs, ' ', $i, 1);
                            
                            //print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        
                        }
                    
                    }
                    
                    if (reset($stk) == self::IN_ARR) {
                        return $arr;
                    
                    } elseif (reset($stk) == self::IN_OBJ) {
                        return $obj;
                    
                    }
                
                }
        }
    }
    
    /**
     * 
     * Array-walking method for use in generating JSON-formatted name-value
     * pairs in the form of '"name":value'.
     * 
     * @param string $name name of key to use
     * 
     * @param mixed $value element to be encoded
     * 
     * @return string JSON-formatted name-value pair
     * 
     */
    protected function _name_value($name, $value)
    {
        $encoded_value = $this->_json_encode($value);
        return $this->_json_encode(strval($name)) . ':' . $encoded_value;
    }
    
    /**
     * 
     * Convert a string from one UTF-16 char to one UTF-8 char.
     * 
     * Normally should be handled by mb_convert_encoding, but
     * provides a slower PHP-only method for installations
     * that lack the multibye string extension.
     * 
     * @param string $utf16 UTF-16 character
     * 
     * @return string UTF-8 character
     * 
     */
    protected function _utf162utf8($utf16)
    {
        // oh please oh please oh please oh please oh please
        if(!$this->_config['bypass_mb'] &&
            function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
        }
        
        $bytes = (ord($utf16{0}) << 8) | ord($utf16{1});
        
        switch (true) {
            case ((0x7F & $bytes) == $bytes):
                // this case should never be reached, because we are in ASCII range
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x7F & $bytes);
            
            case (0x07FF & $bytes) == $bytes:
                // return a 2-byte UTF-8 character
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xC0 | (($bytes >> 6) & 0x1F))
                     . chr(0x80 | ($bytes & 0x3F));
            
            case (0xFFFF & $bytes) == $bytes:
                // return a 3-byte UTF-8 character
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xE0 | (($bytes >> 12) & 0x0F))
                     . chr(0x80 | (($bytes >> 6) & 0x3F))
                     . chr(0x80 | ($bytes & 0x3F));
        }
        
        // ignoring UTF-32 for now, sorry
        return '';
    }
    
    /**
     * 
     * Convert a string from one UTF-8 char to one UTF-16 char.
     * 
     * Normally should be handled by mb_convert_encoding, but
     * provides a slower PHP-only method for installations
     * that lack the multibye string extension.
     * 
     * @param string $utf8 UTF-8 character
     * 
     * @return string UTF-16 character
     * 
     */
    protected function _utf82utf16($utf8)
    {
        // oh please oh please oh please oh please oh please
        if (!$this->_config['bypass_mb'] &&
            function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
        }
        
        switch (strlen($utf8)) {
            case 1:
                // this case should never be reached, because we are in ASCII range
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return $utf8;
            
            case 2:
                // return a UTF-16 character from a 2-byte UTF-8 char
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x07 & (ord($utf8{0}) >> 2))
                     . chr((0xC0 & (ord($utf8{0}) << 6))
                         | (0x3F & ord($utf8{1})));
            
            case 3:
                // return a UTF-16 character from a 3-byte UTF-8 char
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr((0xF0 & (ord($utf8{0}) << 4))
                         | (0x0F & (ord($utf8{1}) >> 2)))
                     . chr((0xC0 & (ord($utf8{1}) << 6))
                         | (0x7F & ord($utf8{2})));
        }
        
        // ignoring UTF-32 for now, sorry
        return '';
    }
    
    /**
     * 
     * Reduce a string by removing leading and trailing comments and whitespace.
     * 
     * @param string $str string value to strip of comments and whitespace
     * 
     * @return string string value stripped of comments and whitespace
     * 
     */
    protected function _reduce_string($str)
    {
        $str = preg_replace(array(
            
            // eliminate single line comments in '// ...' form
            '#^\s*//(.+)$#m',
            
            // eliminate multi-line comments in '/* ... */' form, at start of string
            '#^\s*/\*(.+)\*/#Us',
            
            // eliminate multi-line comments in '/* ... */' form, at end of string
            '#/\*(.+)\*/\s*$#Us'
        
        ), '', $str);
        
        // eliminate extraneous space
        return trim($str);
    }
}
