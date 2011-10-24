<?php
/**
 * 
 * Parses a single PHPDoc comment block into summary, narrative, and
 * technical portions.
 * 
 * http://java.sun.com/j2se/javadoc/writingdoccomments/index.html#format
 * 
 * Supported technical tags are ...
 * 
 * For classes ...
 * 
 *     @category name                   # category for the package
 *     @package name                    # class package name
 *     @subpackage name                 # class subpackage name
 *     @copyright info                  # class copyright information
 *     @license uri name text           # licensing information
 *     @version info                    # version information
 * 
 * For properties ...
 * 
 *     @var type [summary]              # class property
 * 
 * For methods ...
 * 
 *     @exception class [summary]       # alias to @throws
 *     @param type [$name] [summary]    # method parameter
 *     @return type [summary]           # method return
 *     @throws class [summary]          # exceptions thrown by the method
 *     @staticvar type $name summary    # use of a static variable within a method
 * 
 * General-purpose ...
 * 
 *     @see name                        # "see also" this element name
 *     @todo summary                    # todo item
 *     @ignore                          # ignore this element
 *     @author name <email> summ        # author name, email, and summary
 *     @deprecated                      # notes the element is deprecated
 *     @deprec                          # alias to @deprecated
 *     @link uri text                   # link to an external URI
 *     @since info                      # element has been available since this time
 *     @example file                    # path to an external example file
 * 
 * Not supported ...
 * 
 *     @global type $globalvar          # description of global variable usage in a function
 *     @name procpagealias              #
 *     @name $globalvaralias            #
 *     @magic                           # phpdoc.de compatibility
 *     @internal                        # private information for advanced developers only
 *     {@code}                          # inline tags
 *     {@docRoot}                       # 
 *     {@inheritDoc}                    # 
 *     {@link}                          # 
 *     {@linkplain}                     # 
 *     {@literal}                       # 
 *     {@value}                         # 
 * 
 * @category Solar
 * 
 * @package Solar_Docs
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Phpdoc.php 4381 2010-02-14 16:17:22Z pmjones $
 * 
 */
class Solar_Docs_Phpdoc extends Solar_Base
{
    /**
     * 
     * Where the technical information from block tags is stored.
     * 
     * @var array
     * 
     */
    protected $_info = array();
    
    /**
     * 
     * Returns docblock comment parsed into summary, narrative, and
     * technical information portions.
     * 
     * @param string $block The docblock comment text.
     * 
     * @return array An array with keys 'summ', 'narr', and 'tech' 
     * corresponding to the summary, narrative, and technical portions
     * of the docblock.
     * 
     */
    public function parse($block)
    {
        // clear out prior info
        $this->_info = array();
        
        // fix line-endings from windows
        $block = str_replace("\r\n", "\n", $block);
        
        // fix line-endings from mac os 9 and previous
        $block = str_replace("\r", "\n", $block);
        
        // remove the leading comment indicator (slash-star-star)
        $block = preg_replace('/^\s*\/\*\*\s*$/m', '', $block);
        
        // remove the trailing comment indicator (star-slash)
        $block = preg_replace('/^\s*\*\/\s*$/m', '', $block);
        
        // remove the star (and optionally one space) leading each line
        $block = preg_replace('/^\s*\*( )?/m', '', $block);
        
        // wrap with exactly one beginning and ending newline
        $block = "\n" . trim($block) . "\n";
        
        // find narrative and technical portions
        $pos = strpos($block, "\n@");
        if ($pos === false) {
            // apparently no technical section
            $narr = $block;
            $tech = '';
        } else {
            // there appears to be a technical section
            $narr = trim(substr($block, 0, $pos));
            $tech = trim(substr($block, $pos));
        }
        
        // load the formal technical info array
        $this->_loadInfo($tech);
        
        // now take the summary line off the narrative.
        // look for the first sentence-punctuation followed by whitespace,
        // or the first double-newline.
        $punct_ws  = "\n\s*(\n|$)";
        $double_nl = "[\.\?\!](\s|\n|$)";
        preg_match("/^.*(($punct_ws)|($double_nl))/AUms", $narr, $matches);
        if (! empty($matches[0])) {
            $summ = $matches[0];
            $narr = substr($narr, strlen($matches[0]));
        } else {
            $summ = $narr;
            $narr = '';
        }
        
        $summ = str_replace("\n", " ", $summ);
        
        // return the summary, narrative, and technical portions
        return array(
            'summ' => trim($summ),
            'narr' => trim($narr),
            'tech' => $this->_info,
        );
        
    }
    
    /**
     * 
     * Gets the technical information from a docblock comment.
     * 
     * @param string $tech The technical portion of a docblock.
     * 
     * @return array An array of technical information.
     * 
     */
    protected function _loadInfo($tech)
    {
        $tech = "\n" . trim($tech) . "\n";
        
        // split into elements at each "\n@"
        $split = preg_split(
            '/\n\@/m',
            $tech,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        
        // process each element
        foreach ($split as $line) {
            $line = trim($line);
            $found = preg_match('/(\w+)(?:\s+)?(.*)/ms', $line, $matches);
            if (! $found) {
                continue;
            }
            
            $func = "parse" . ucfirst($matches[1]);
            $line = str_replace("\n", ' ', $matches[2]);
            if (is_callable(array($this, $func))) {
                $this->$func($line);
            }
        }
    }
    
    /**
     * 
     * Parses one or more @param lines into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseParam($line)
    {
        // string|array $varname Summary or description.
        // string|array $varname
        // string|array Summary or description.
        preg_match('/(\S+)?((\s+\&?\$)(\S+))?((\s+)(.*))?/', $line, $matches);
        
        if (! $matches) {
            return;
        }
        
        // do we have a params array?
        if (empty($this->_info['param'])) {
            $this->_info['param'] = array();
        }
        
        // variable type
        $type = $matches[1];
        
        // if no variable name, name for the param count
        if (empty($matches[4])) {
            $name = count($this->_info['param']);
        } else {
            $name = $matches[4];
        }
        
        // always need a summary element
        if (empty($matches[7])) {
            $summ = '';
        } else {
            $summ = $matches[7];
        }
        
        // save the param
        $this->_info['param'][$name] = array(
            'type' => $type,
            'summ' => $summ,
        );
    }
    
    /**
     * 
     * Parses one @return line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseReturn($line)
    {
        $parts = $this->_2part($line);
        if ($parts) {
            $this->_info['return'] = array(
                'type' => $parts[0],
                'summ' => $parts[1],
            );
        }
    }
    
    /**
     * 
     * Parses one or more @todo lines into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseTodo($line)
    {
        // no parsing needed
        $line = trim($line);
        if ($line) {
            $this->_info['todo'][] = $line;
        }
    }
    
    /**
     * 
     * Parses one or more @see lines into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseSee($line)
    {
        $line = trim($line);
        if ($line) {
            $this->_info['see'][] = $line;
        }
    }
    
    /**
     * 
     * Parses one @var line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseVar($line)
    {
        // @var (single)
        // string|array summary
        $parts = $this->_2part($line);
        if ($parts) {
            $this->_info['var'] = array(
                'type' => $parts[0],
                'summ' => $parts[1],
            );
        }
    }
    
    /**
     * 
     * Parses one or more @throws lines into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseThrows($line)
    {
        $parts = $this->_2part($line);
        if ($parts) {
            $this->_info['throws'][] = array(
                'type' => $parts[0],
                'summ' => $parts[1],
            );
        }
    }
    
    /**
     * 
     * Parses one or more @exception lines into $this->_info; alias for @throws.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseException($line)
    {
        return $this->parseThrows($line);
    }
    
    /**
     * 
     * Parses one @category line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseCategory($line)
    {
        $this->_info['category'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses one @package line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parsePackage($line)
    {
        $parts = $this->_2part($line);
        if ($parts) {
            $this->_info['package'] = array(
                'name' => $parts[0],
                'summ' => $parts[1],
            );
        }
    }
    
    /**
     * 
     * Parses one @subpackage line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseSubpackage($line)
    {
        $this->_info['subpackage'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses one @ignore line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseIgnore($line)
    {
        $this->_info['ignore'] = true;
    }
    
    /**
     * 
     * Parses one or more @author lines into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     * @todo Do specialized parsing, looking for <email@example.com> in the
     * middle of the line.
     * 
     */
    public function parseAuthor($line)
    {
        // no parsing needed
        $line = trim($line);
        if ($line) {
            $this->_info['author'][] = $line;
        }
    }
    
    /**
     * 
     * Parses one @copyright line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseCopyright($line)
    {
        // no parsing needed
        $line = trim($line);
        if ($line) {
            $this->_info['copyright'][] = $line;
        }
    }
    
    /**
     * 
     * Parses one @deprecated line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseDeprecated($line)
    {
        $this->_info['deprecated'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses one @deprec line into $this->_info; alias for @deprecated.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseDeprec($line)
    {
        return $this->parseDeprecated($line);
    }
    
    /**
     * 
     * Parses one @license line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseLicense($line)
    {
        $parts = $this->_3part($line);
        if ($parts) {
            $this->_info['license'] = array(
                'uri'  => $parts[0],
                'name' => $parts[1],
                'text' => $parts[2],
            );
        }
    }
    
    /**
     * 
     * Parses one or more @link lines into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseLink($line)
    {
        $parts = $this->_2part($line);
        if ($parts) {
            $this->_info['link'][] = array(
                'uri'  => $parts[0],
                'text' => $parts[1],
            );
        }
    }
    
    /**
     * 
     * Parses one @since line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseSince($line)
    {
        $this->_info['since'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses one @version line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseVersion($line)
    {
        $this->_info['version'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses one @example line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseExample($line)
    {
        $this->_info['example'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses one or more @staticvar lines into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     * @todo Use @param parsing algorithm.
     * 
     */
    public function parseStaticvar($line)
    {
        $parts = $this->_3part($line);
        if ($parts) {
            $this->_info['staticvar'][] = array(
                'type' => $parts[0],
                'name' => $parts[1],
                'summ' => $parts[2],
            );
        }
    }
    
    /**
     * 
     * Parses one or more @config lines into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     * @todo Use @param parsing algorithm.
     * 
     */
    public function parseConfig($line)
    {
        $parts = $this->_3part($line);
        if ($parts) {
            $name = $parts[1];
            $this->_info['key'][$name] = array(
                'type' => $parts[0],
                'name' => $parts[1],
                'summ' => $parts[2],
            );
        }
    }
    
    /**
     * 
     * Parses a one-part block line; strips everything after the first space.
     * 
     * @param string $line The block line.
     * 
     * @return string
     * 
     */
    protected function _1part($line)
    {
        return preg_replace('/^(\S+)(\s.*)/', '$1', trim($line));
    }
    
    /**
     * 
     * Parses a two-part block line; part 2 is optional.
     * 
     * @param string $line The block line.
     * 
     * @return array An array of the parts.
     * 
     */
    protected function _2part($line)
    {
        preg_match('/(\S+)((\s+)(.*))?/', $line, $matches);
        if (empty($matches)) {
            return array();
        }
        if (empty($matches[4])) {
            $matches[4] = '';
        }
        return array(
            $matches[1],
            $matches[4],
        );
    }
    
    /**
     * 
     * Parses a three-part block line; parts 2 and 3 are optional.
     * 
     * @param string $line The block line.
     * 
     * @return array An array of the parts.
     * 
     */
    protected function _3part($line)
    {
        preg_match('/([\S]+)((\s+)(\S+))?((\s+)(.*))?/', $line, $matches);
        if (empty($matches)) {
            return array();
        }
        if (empty($matches[4])) {
            $matches[4] = '';
        }
        if (empty($matches[7])) {
            $matches[7] = '';
        }
        return array(
            $matches[1],
            $matches[4],
            $matches[7],
        );
    }
}