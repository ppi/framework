<?php
/**
 * 
 * Block plugin to for method synopsis markup.
 * 
 *     {{method: methodName
 *        @access level
 *        @param  type
 *        @param  type, name,
 *        @param  type, name, default
 *        @return type
 *        @throws type
 *        @throws type
 *     }}
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: MethodSynopsis.php 4536 2010-04-23 16:54:38Z pmjones $
 * 
 */
class Solar_Markdown_Wiki_MethodSynopsis extends Solar_Markdown_Plugin
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string synopsis The "main" format string.
     * 
     * @config string access The format string for access type.
     * 
     * @config string return The format string for return type.
     * 
     * @config string method The format string for the method name.
     * 
     * @config string param The format string for required params.
     * 
     * @config string param_default The format string for params with a default value.
     * 
     * @config string param_void The format string for a method with no params.
     * 
     * @config string throws The format string for throws.
     * 
     * @config string list_sep The list separator for params and throws.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown_Wiki_MethodSynopsis = array(
        'synopsis'      => "<div class=\"method-synopsis\">\n    %access\n    %return\n    %method (%params\n    )%throws\n</div>",
        'access'        => '<span class="access">%access</span>',
        'return'        => '<span class="return">%return</span>',
        'method'        => '<span class="method">%method</span>',
        'param'         => "\n        <span class=\"param\"><span class=\"type\">%type</span> <span class=\"name\">$%name</span>",
        'param_default' => "\n        <span class=\"param-default\"><span class=\"type\">%type</span> <span class=\"name\">$%name</span> default <span class=\"default\">%default</span>",
        'param_void'    => "\n",
        'throws'        => "\n    <span class=\"throws\">throws <span class=\"type\">%type</span></span>",
        'list_sep'      => ', ',
    );
    
    /**
     * 
     * This is a block plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_block = true;
    
    /**
     * 
     * These should be encoded as special Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_chars = '{}:';
    
    /**
     * 
     * Converts method synopsis to XHTML markup.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $regex = '!^ {{method:\s*(\S*?)\n+(.*?)\n}}\n!msx';
        return preg_replace_callback(
            $regex,
            array($this, '_parse'),
            $text
        );
    }
    
    /**
     * 
     * Support callback for method synopses.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $method = $matches[1];
        $access = '';
        $return = '';
        $params = array();
        $throws = array();
        
        // split apart the content lines and loop through them
        $lines = explode("\n", $matches[2]);
        foreach ($lines as $line) {
            
            // skip blank lines
            $line = trim($line);
            if (! $line) {
                continue;
            }
            
            // find the first ' ' on the line; the left part is the 
            // type, the right part is the value. skip lines without
            // a ':' on them.
            $pos = strpos($line, ' ');
            if ($pos === false) {
                continue;
            }
            
            // $type is the line type: name, access, return, param, throws
            // 012345678901234
            // name: something
            $type = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos+1));
            
            switch($type) {
            
            case '@access':
                $access = $val;
                break;
                
            case '@param':
                $tmp = explode(',', $val);
                $k = count($tmp);
                if ($k == 1) {
                    $params[] = array(
                        'type'    => trim($tmp[0]),
                        'name'    => null,
                        'default' => null,
                    );
                } elseif ($k == 2) {
                    $params[] = array(
                        'type'    => trim($tmp[0]),
                        'name'    => trim($tmp[1]),
                        'default' => null,
                    );
                } else {
                    $params[] = array(
                        'type'    => trim($tmp[0]),
                        'name'    => trim($tmp[1]),
                        'default' => trim($tmp[2]),
                    );
                }
                break;
            
            case '@return':
                $return = $val;
                break;
            
            case '@throws':
                $throws[] = $val;
                break;
            }
        }
        
        // access, return, method
        $html['%access'] = str_replace('%access', $this->_escape($access), $this->_config['access']);
        $html['%return'] = str_replace('%return', $this->_escape($return), $this->_config['return']);
        $html['%method'] = str_replace('%method', $this->_escape($method), $this->_config['method']);
        
        // params
        if (! $params) {
            $html['%params'] = $this->_config['param_void'];
        } else {
            $list = array();
            foreach ($params as $key => $val) {
            
                // is there a default value?
                if (! is_null($val['default'])) {
                    $item = $this->_config['param_default'];
                } else {
                    $item = $this->_config['param'];
                }
                
                // take the leading $ off of the name
                $val['name'] = substr($val['name'], 1);
                
                // add the param elements
                $item = str_replace('%type',    $this->_escape($val['type']),    $item);
                $item = str_replace('%name',    $this->_escape($val['name']),    $item);
                $item = str_replace('%default', $this->_escape($val['default']), $item);
                $list[] = $item;
            }
            $html['%params'] = implode($this->_config['list_sep'], $list);
        }
        
        // throws
        $list = array();
        foreach ($throws as $type) {
            $item = $this->_config['throws'];
            $item = str_replace('%type', $this->_escape($type), $item);
            $list[] = $item;
        }
        
        // insert throws into output
        $html['%throws'] = implode($this->_config['list_sep'], $list);
        
        // build the whole thing
        $html = str_replace(
            array_keys($html),
            array_values($html),
            $this->_config['synopsis']
        );
        
        // return the output as a token
        return $this->_toHtmlToken($html) . "\n";
    }
}
