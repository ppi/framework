<?php
/**
 * 
 * Rewrites URI action paths, and creates URI paths from the rewrite rules.
 * 
 * @category Solar
 * 
 * @package Solar_Uri Representation and manipulation of URIs (generic, 
 * action, and public).
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Rewrite.php 4506 2010-03-08 22:37:19Z pmjones $
 * 
 */
class Solar_Uri_Rewrite extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config array rewrite An array of rewrite rules.
     * 
     * @config array replace An array of regex replacement tokens.
     * 
     * @var array
     * 
     */
    protected $_Solar_Uri_Rewrite = array(
        'rewrite' => array(),
        'replace' => array(),
    );
    
    /**
     * 
     * The collection of rewrite rules.
     * 
     * @var array
     * 
     */
    protected $_rewrite = array();
    
    /**
     * 
     * The collection of replacement regex tokens.
     * 
     * @var array
     * 
     */
    protected $_replace = array(
        '{:action}'     => '([a-z-]+)',
        '{:alpha}'      => '([a-zA-Z]+)',
        '{:alnum}'      => '([a-zA-Z0-9]+)',
        '{:controller}' => '([a-z-]+)',
        '{:digit}'      => '([0-9]+)',
        '{:param}'      => '([^/]+)',
        '{:params}'     => '(.*)',
        '{:slug}'       => '([a-zA-Z0-9-]+)',
        '{:word}'       => '([a-zA-Z0-9_]+)',
    );
    
    /**
     * 
     * The default rewrite rule array.
     * 
     * The `pattern` key is the incoming URI regex pattern to match against.
     * 
     * The `rewrite` key is the canonical "controller/action/param" format
     * the incoming URI should be rewritten to.
     * 
     * The `replace` key is an array of custom replacement regex tokens
     * for this particular rewrite rule.
     * 
     * The `default` key is an array of default values for tokens when data
     * for interpolation is missing.
     * 
     * @var array
     * 
     */
    protected $_default = array(
        'pattern' => null,
        'rewrite' => null,
        'replace' => array(),
        'default' => array(),
    );
    
    /**
     * 
     * An explanation of the last match() attempt.
     * 
     * @var string
     * 
     */
    protected $_explain;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        if ($this->_config['rewrite']) {
            $this->_rewrite = $this->_config['rewrite'];
        }
        
        if ($this->_config['replace']) {
            $this->_replace = $this->_config['replace'] + $this->_replace;
        }
    }
    
    /**
     * 
     * Sets one rewrite rule; adds it if it is not there, or changes it if it
     * is.
     * 
     * {{code: php
     *     // shorthand form is 'pattern' and 'rewrite'.
     *     $rewrite->setRule('blog/{:id}/edit', 'blog/edit/$1')
     *     
     *     // longhand form is 'rule-name', and an array of information.
     *     // this is what lets you generate named actions using getPath().
     *     $rewrite->setRule('blog-edit', array(
     *         // the pattern to match
     *         'pattern' => 'blog/{:id}/edit',
     *         // rewrite to this
     *         'rewrite' => 'blog/edit/$1',
     *         // custom replacement tokens just for this rule
     *         'replace' => array(
     *             '{:id}' => '(\d*)
     *         ),
     *     ));
     * }}
     * 
     * @param string $key A shorthand pattern, or a longhand rule name.
     * 
     * @param mixed $val A shorthand rewrite string, or a longhand array
     * of information.
     * 
     * @return void
     * 
     */
    public function setRule($key, $val)
    {
        $this->_rewrite[$key] = $val;
    }
    
    /**
     * 
     * Gets a rewrite rule by its key; the key may be a pattern, or a named
     * rule.
     * 
     * @param string $key The rule key.
     * 
     * @return mixed The shorthand rewrite string, or the longhand information
     * array.
     * 
     */
    public function getRule($key)
    {
        if (! empty($this->_rewrite[$key])) {
            return $this->_rewrite[$key];
        }
    }
    
    /**
     * 
     * Gets all the rewrite rules.
     * 
     * @return array
     * 
     */
    public function getRules()
    {
        return $this->_rewrite;
    }
    
    /**
     * 
     * Reset all the rewrite rules.
     * 
     * @param array $list Use these rules instead of the existing ones.
     * 
     * @return void
     * 
     */
    public function resetRules($list = null)
    {
        $this->_rewrite = (array) $list;
    }
    
    /**
     * 
     * Merges a new list of rules with the existing ones; the new ones
     * will replace any existing rules with the same keys.
     * 
     * @param array $list Merge these rules with the existing ones.
     * 
     * @return void
     * 
     */
    public function mergeRules($list)
    {
        $this->_rewrite = (array) $list + $this->_rewrite;
    }
    
    /**
     * 
     * Sets one replacement regex token.
     * 
     * @param string $key The token name.
     * 
     * @param string $val The regex replacement.
     * 
     * @return void
     * 
     */
    public function setReplacement($key, $val)
    {
        $this->_replace[$key] = $val;
    }
    
    /**
     * 
     * Gets the replacement regex for a token.
     * 
     * @param string $key The token name.
     * 
     * @return string The replacement regex.
     * 
     */
    public function getReplacement($key)
    {
        if (! empty($this->_replace[$key])) {
            return $this->_replace[$key];
        }
    }
    
    /**
     * 
     * Gets all replacement regex tokens.
     * 
     * @return array The replacement regex tokens.
     * 
     */
    public function getReplacements()
    {
        return $this->_replace;
    }
    
    /**
     * 
     * Reset all replacement regex tokens.
     * 
     * @param array $list Use these tokens instead of the existing ones.
     * 
     * @return void
     * 
     */
    public function resetReplacements($list = null)
    {
        $this->_replace = (array) $list;
    }
    
    /**
     * 
     * Merges a new list of replacement regex tokens with the existing ones; 
     * the new ones will replace any existing replacements with the same keys.
     * 
     * @param array $list Merge these tokens with the existing ones.
     * 
     * @return void
     * 
     */
    public function mergeReplacements($list = null)
    {
        $this->_replace = (array) $list + $this->_replace;
    }
    
    /**
     * 
     * Given a URI path, matches it against a rewrite rule and returns the
     * rewritten path.
     * 
     * @param string|Solar_Uri $spec The original URI path.
     * 
     * @return string The rewritten path.
     * 
     */
    public function match($spec)
    {
        $this->_explain = null;
        
        // pre-empt if no rules
        if (! $this->_rewrite) {
            $this->_explain = 'no rules';
            return;
        }
        
        // convert spec to a path
        if ($spec instanceof Solar_Uri_Action) {
            $oldpath = trim($spec->getFrontPath(), '/');
        } elseif ($spec instanceof Solar_Uri) {
            $oldpath = trim($spec->getPath());
        } else {
            $oldpath = $spec;
        }
        
        // go through each of the rules to find a match
        foreach ($this->_rewrite as $name => $info) {
            
            if (! is_array($info)) {
                // shorthand format
                $info = array(
                    'pattern' => $name,
                    'rewrite' => $info,
                    'replace' => array(),
                    'default' => array(),
                );
            } else {
                // longhand format
                $info += $this->_default;
            }
            
            // consolidate replacements
            $replace = $info['replace'] + $this->_replace;
            
            // convert replacements in the pattern
            $pattern = str_replace(
                array_keys($replace),
                array_values($replace),
                $info['pattern']
            );
            
            // trim slashes and wrap as a full regex
            $pattern = '#^' . trim($pattern, '/') . '$#';
            
            // is it a match?
            if (preg_match($pattern, $oldpath)) {
                // rewrite to new path and trim slashes
                $rewrite = trim($info['rewrite'], '/');
                $newpath = preg_replace($pattern, $rewrite, $oldpath);
                $this->_explain = "matched rule '$name'";
                return trim($newpath, '/');
            }
        }
        
        $this->_explain = 'no matches';
    }
    
    /**
     * 
     * Explains the result of the last match() attempt.
     * 
     * @return string
     * 
     */
    public function explain()
    {
        return $this->_explain;
    }
    
    /**
     * 
     * Look up a named rewrite rule, replace the regex token placeholders
     * in the pattern with data values, and return the resulting path.
     * 
     * @param string $name The rewrite rule name.
     * 
     * @param array $data Key-value pairs to use as replacements for the
     * regex token placeholders in the pattern.
     * 
     * @return string The named rewrite rule with data in it.
     * 
     */
    public function getPath($name, $data = null)
    {
        // is there a rule with this name?
        if (empty($this->_rewrite[$name])) {
            return false;
        }
        
        // look up the rule and get its info
        $info = $this->_rewrite[$name];
        if (! is_array($info)) {
            // not a named rule
            return false;
        }
        
        // the path with tokens still in it
        $path = $info['pattern'];
        
        // if there's no data, there's nothing to replace with
        if (! $data) {
            return $path;
        }
        
        // find all the tokens in the pattern
        preg_match_all('/\{:(.*?)\}/', $path, $matches);
        
        // if there are no tokens, there's nothing to replace
        if (empty($matches[1])) {
            return $path;
        }
        
        // interpolate data over the tokens
        foreach ($matches[1] as $key) {
            $token = "{:$key}";
            if (isset($data[$key])) {
                // use data value
                $path = str_replace($token, $data[$key], $path);
            } elseif (array_key_exists($token, $info['default'])) {
                // use default value
                $path = str_replace($token, $info['default'][$token], $path);
            }
        }
        
        // done!
        return $path;
    }
}
