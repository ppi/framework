<?php
/**
 * 
 * Staic methods to support text formatting on VT00 terminals.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Vt100.php 4622 2010-06-30 12:53:52Z pmjones $
 * 
 */
class Solar_Vt100 extends Solar_Base
{
    /**
     * 
     * Array of format conversions for use on a variety of pre-set console
     * style combinations.
     * 
     * Based on ANSI VT100 Color/Style Codes, according to the [VT100 User Guide][1]
     * and the [ANSI/VT100 Terminal Control reference][2]. Inspired by
     * [PEAR Console_Color][3].
     * 
     * [1]: http://vt100.net/docs/vt100-ug
     * [2]: http://www.termsys.demon.co.uk/vtansi.htm
     * [3]: http://pear.php.net/Console_Color
     * 
     * @var array
     * 
     */
    static protected $_format = array(
        
        // literal percent sign
        '%%'    => '%',             // percent-sign
        
        // color, normal weight
        '%k'    => "\033[30m",      // black
        '%r'    => "\033[31m",      // red
        '%g'    => "\033[32m",      // green
        '%y'    => "\033[33m",      // yellow
        '%b'    => "\033[34m",      // blue
        '%m'    => "\033[35m",      // magenta/purple
        '%p'    => "\033[35m",      // magenta/purple
        '%c'    => "\033[36m",      // cyan/light blue
        '%w'    => "\033[37m",      // white
        '%n'    => "\033[0m",       // reset to terminal default
        
        // color, bold
        '%K'    => "\033[30;1m",    // black, bold
        '%R'    => "\033[31;1m",    // red, bold
        '%G'    => "\033[32;1m",    // green, bold
        '%Y'    => "\033[33;1m",    // yellow, bold
        '%B'    => "\033[34;1m",    // blue, bold
        '%M'    => "\033[35;1m",    // magenta/purple, bold
        '%P'    => "\033[35;1m",    // magenta/purple, bold
        '%C'    => "\033[36;1m",    // cyan/light blue, bold
        '%W'    => "\033[37;1m",    // white, bold
        '%N'    => "\033[0;1m",     // terminal default, bold
        
        // background color
        '%0'    => "\033[40m",      // black background
        '%1'    => "\033[41m",      // red background
        '%2'    => "\033[42m",      // green background
        '%3'    => "\033[43m",      // yellow background
        '%4'    => "\033[44m",      // blue background
        '%5'    => "\033[45m",      // magenta/purple background
        '%6'    => "\033[46m",      // cyan/light blue background
        '%7'    => "\033[47m",      // white background
        
        // assorted style shortcuts
        '%F'    => "\033[5m",       // blink/flash
        '%_'    => "\033[5m",       // blink/flash
        '%U'    => "\033[4m",       // underline
        '%I'    => "\033[7m",       // reverse/inverse
        '%*'    => "\033[1m",       // bold
        '%d'    => "\033[2m",       // dim        
    );
    
    /**
     * 
     * Converts VT100 %-markup to control codes.
     * 
     * @param string $text The text to format.
     * 
     * @return string The formatted text.
     * 
     */
    static public function format($text)
    {
        return strtr($text, self::$_format);
    }
    
    /**
     * 
     * Converts VT100 %-markup to plain text.
     * 
     * @param string $text The text to strip %-markup from.
     * 
     * @return string The plain text.
     * 
     */
    static public function plain($text)
    {
        static $plain = null;
        if ($plain === null) {
            $plain = array();
            foreach (self::$_format as $key => $val) {
                $plain[$key] = '';
            }
            $plain['%%'] = '%';
        }
        
        return strtr($text, $plain);
    }
    
    /**
     * 
     * Writes text to a file handle, converting to control codes if the handle
     * is a posix TTY, or to plain text if not.
     * 
     * @param resource $handle The file handle.
     * 
     * @param string $text The text to write to the file handle, converting
     * %-markup if the handle is a posix TTY, or stripping markup if not.
     * 
     * @param string $append Append this text as-is when writing to the file
     * handle; generally useful for adding newlines.
     * 
     * @return void
     * 
     */
    static public function write($handle, $text, $append = null)
    {
        if (function_exists('posix_isatty') && posix_isatty($handle)) {
            // it's a tty, safe to use markup
            fwrite($handle, self::format($text) . $append);
        } else {
            // not posix or not a tty, use plain text
            fwrite($handle, self::plain($text) . $append);
        }
    }
    
    /**
     * 
     * Escapes ASCII control codes (0-31, 127) and %-signs.
     * 
     * Note that this will catch newlines and carriage returns as well.
     * 
     * @param string $text The text to escape.
     * 
     * @return string The escaped text.
     * 
     */
    static public function escape($text)
    {
        static $list;
        
        if (! $list) {
            
            $list = array(
                '%' => '%%',
            );
            
            for ($i = 0; $i < 32; $i ++) {
                $list[chr($i)] = "\\$i";
            }
            
            $list[chr(127)] = "\\127";
            
        }
        
        return strtr($text, $list);
    }
}