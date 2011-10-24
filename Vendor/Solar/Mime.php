<?php
/**
 * 
 * MIME utility methods for mail messages and HTTP requests.
 * 
 * @category Solar
 * 
 * @package Solar_Mime Generic MIME support methods.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Mime.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Mime
{
    /**
     * 
     * Creates a header label/value line after sanitizing, encoding, and
     * wrapping.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     * @param string $charset The character set to note when encoding.
     * 
     * @param string $crlf The CRLF line-ending string to use when wrapping.
     * 
     * @param string $len The line-length to wrap at.
     * 
     * @return string The sanitized, encoded, and wrapped header-line.  Note
     * that there is no terminating linefeed.
     * 
     */
    static public function headerLine($label, $value, $charset = 'utf-8',
        $crlf = "\r\n", $len = 75)
    {
        $label = self::headerLabel($label);
        $value = self::headerValue($label, $value, $charset, $crlf, $len);
        return "$label: $value";
    }
    
    /**
     * 
     * Sanitizes header labels by removing all characters besides [a-zA-z0-9_-].
     * 
     * Underscores are converted to dashes, and word case is normalized.
     * 
     * Converts "foo \r bar_ baz-dib \n 9" to "Foobar-Baz-Dib9".
     * 
     * @param string $label The header label to sanitize.
     * 
     * @return string The sanitized header label.
     * 
     */
    static public function headerLabel($label)
    {
        $label = preg_replace('/[^a-zA-Z0-9_-]/', '', $label);
        $label = ucwords(strtolower(str_replace(array('-', '_'), ' ', $label)));
        $label = str_replace(' ', '-', $label);
        return $label;
    }
    
    /**
     * 
     * Sanitizes a header value, then encodes and wraps as per RFC 2047.
     * 
     * Copied, with modifications, from the "mime.php" class in the
     * [PEAR Mail_Mime](http://pear.php.net/Mail_Mime) package (v 1.62).
     * Takes the added step of "un-wrapping" the value (newline-and-space) and
     * then removing all control characters (including newlines) before encoding
     * and re-wrapping.
     * 
     * @author Richard Heyes  <richard@phpguru.org>
     * 
     * @author Tomas V.V. Cox <cox@idecnet.com>
     * 
     * @author Cipriano Groenendal <cipri@php.net>
     * 
     * @author Sean Coates <sean@php.net>
     * 
     * @param string $label The *sanitized* header label; needed for line
     * length calculations.
     * 
     * @param string $value The header value to encode.
     * 
     * @param string $charset The character set to note when encoding.
     * 
     * @param string $crlf The CRLF line-ending string to use when wrapping.
     * 
     * @param string $len The line-length to wrap at.
     * 
     * @return string The encoded header value.
     * 
     */
    static public function headerValue($label, $value, $charset = 'utf-8',
        $crlf = "\r\n", $len = 75)
    {
        // remove all instances of newline-with-space to unwrap lines
        $value = preg_replace('/(\r\n|\r|\n)([ \t]+)/m', '', $value);
        
        // remove all control chars from the unwrapped line, including newlines.
        $value = preg_replace('/[\x00-\x1F]/', '', $value);
        
        // also remove urlencode() equivalents.
        $value = preg_replace('/%[0-1][0-9A-Fa-f]/', '', $value);
        
        // now do the encoding
        $hdr_vals  = preg_split("/(\s)/", $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $value_out = "";
        $previous  = "";
        foreach ($hdr_vals as $hdr_val) {
            
            if (trim($hdr_val) == '') {
                // whitespace needs to be handled with another string, or it
                // won't show between encoded strings. Prepend this to the next
                // item.
                $previous .= $hdr_val;
                continue;
            } else {
                $hdr_val = $previous . $hdr_val;
                $previous = "";
            }
            
            // any non-ascii characters?
            if (preg_match('/[\x80-\xFF]{1}/', $hdr_val)){
                
                // Check if there is a double quote at beginning or end of the string to 
                // prevent that an open or closing quote gets ignored because its encapsuled
                // by an encoding prefix or suffix. 
                // 
                // Remove the double quote and set the specific prefix or suffix variable
                // so later we can concat the encoded string and the double quotes back 
                // together to get the intended string.
                $quotePrefix = $quoteSuffix = '';
                if ($hdr_val{0} == '"') {
                    $hdr_val = substr($hdr_val, 1);
                    $quotePrefix = '"';
                }
                
                if ($hdr_val{strlen($hdr_val)-1} == '"') {
                    $hdr_val = substr($hdr_val, 0, -1);
                    $quoteSuffix = '"';
                }
                
                // This header contains non ASCII chars and should be encoded
                // using quoted-printable. Dynamically determine the maximum
                // length of the strings.
                $prefix = '=?' . $charset . '?Q?';
                $suffix = '?=';
                
                // The -2 is here so the later regexp doesn't break any of
                // the translated chars. The -2 on the first line-regexp is
                // to compensate for the ": " between the header-name and the
                // header value.
                $maxLength        = $len - strlen($prefix . $suffix) - 2;
                $maxLength1stLine = $maxLength - strlen($label) - 2;
                
                // Replace all special characters used by the encoder.
                $search  = array("=",   "_",   "?",   " ");
                $replace = array("=3D", "=5F", "=3F", "_");
                $hdr_val = str_replace($search, $replace, $hdr_val);
                
                // Replace all extended characters (\x80-xFF) with their
                // ASCII values.
                $hdr_val = preg_replace_callback(
                    '/([\x80-\xFF])/',
                    array('Solar_Mime', '_qpReplace'),
                    $hdr_val
                );
                
                // This regexp will break QP-encoded text at every $maxLength
                // but will not break any encoded letters.
                $reg1st = "|(.{0,$maxLength1stLine})[^\=]|";
                $reg2nd = "|(.{0,$maxLength})[^\=]|";
                
                // Concat the double quotes if existant and encoded string together
                $hdr_val = $quotePrefix . $hdr_val . $quoteSuffix;
                
                // Begin with the regexp for the first line.
                $reg = $reg1st;
                
                // Prevent lines that are just way too short.
                if ($maxLength1stLine > 1){
                    $reg = $reg2nd;
                }
                
                $output = "";
                while ($hdr_val) {
                    // Split translated string at every $maxLength.
                    // Make sure not to break any translated chars.
                    $found = preg_match($reg, $hdr_val, $matches);
                    
                    // After this first line, we need to use a different
                    // regexp for the first line.
                    $reg = $reg2nd;
                    
                    // Save the found part and encapsulate it in the
                    // prefix & suffix. Then remove the part from the
                    // $hdr_val variable.
                    if ($found){
                        $part    = $matches[0];
                        $hdr_val = substr($hdr_val, strlen($matches[0]));
                    }else{
                        $part    = $hdr_val;
                        $hdr_val = "";
                    }
                    
                    // RFC 2047 specifies that any split header should be
                    // separated by a CRLF SPACE. 
                    if ($output){
                        $output .= "$crlf ";
                    }
                    
                    $output .= $prefix . $part . $suffix;
                }
                
                $hdr_val = $output;
            }
            
            $value_out .= $hdr_val;
        }
        
        return $value_out;
    }
    
    /**
     * 
     * Callback from the headerValue() method.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The value of $matches[1] with non-ASCII characters
     * encoded for quoted-printable.
     * 
     */
    static protected function _qpReplace($matches)
    {
        return '=' . strtoupper(dechex(ord($matches[1])));
    }
    
    /**
     * 
     * Applies "quoted-printable" encoding to text.
     * 
     * Code taken from <http://us3.php.net/manual/en/ref.stream.php/70826>.
     * 
     * @param string $text The text to encode.
     * 
     * @param string $crlf The line-ending to use; default "\r\n".
     * 
     * @param int $len Break lines at this length; default 75.
     * 
     * @return string The encoded text.
     * 
     */
    static public function encodeQp($text, $crlf = "\r\n", $len = 75)
    {
        // open a "temp" stream pointer
        $fp = fopen('php://temp/', 'r+');
        
        // make sure we break at $len lines with $crlf line-endings
        $params = array('line-length' => $len, 'line-break-chars' => $crlf);
        stream_filter_append(
            $fp,
            'convert.quoted-printable-encode',
            STREAM_FILTER_READ,
            $params
        );
        
        // put the text into the stream
        fputs($fp, $text);
        
        // rewind the pointer and retrieve the the content, which will
        // encode as it reads
        rewind($fp);
        $output = stream_get_contents($fp);
        
        // close the stream and return
        fclose($fp);
        return $output;
    }
    
    /**
     * 
     * Applies "base64" encoding to text.
     * 
     * @param string $text The text to encode.
     * 
     * @param string $crlf The line-ending to use; default "\r\n".
     * 
     * @param int $len Break lines at this length; default 75.
     * 
     * @return string The encoded text.
     * 
     */
    static public function encodeBase64($text, $crlf = "\r\n", $len = 75)
    {
        return rtrim(chunk_split(base64_encode($text), $len, $crlf));
    }
    
    /**
     * 
     * Applies the requested encoding to a text string.
     * 
     * @param string $type The type of encoding to use; '7bit', '8bit',
     * 'base64', or 'quoted-printable'.
     * 
     * @param string $text The text to encode.
     * 
     * @param string $crlf The line-ending to use; default "\r\n".
     * 
     * @return string The encoded text.
     * 
     */
    static public function encode($type, $text, $crlf)
    {
        switch (strtolower($type)) {
        case 'base64':
            return self::encodeBase64($text, $crlf);
            break;
        
        case 'quoted-printable':
            return self::encodeQp($text, $crlf);
            break;
        
        case '7bit':
        case '8bit':
            return $text;
            break;
        
        default:
            throw Solar::exception(
                'Solar_Mime',
                'ERR_UNKNOWN_TYPE'
            );
            break;
        }
    }
}
