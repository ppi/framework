<?php
/**
 * 
 * Retrieves and validates command-line options and parameter values.
 * 
 * @category Solar
 * 
 * @package Solar_Getopt Command-line option parsing.
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Getopt.php 4384 2010-02-14 16:56:47Z pmjones $
 * 
 */
class Solar_Getopt extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string filter_class The data-filter class to use when 
     * validating and sanitizing parameter values.
     * 
     * @config bool strict In strict mode, throw an exception when an
     * unknown option is passed into getopt.
     * 
     * @var array
     * 
     */
    protected $_Solar_Getopt = array(
        'filter_class' => 'Solar_Filter',
        'strict'       => true,
    );
    
    /**
     * 
     * The array of acceptable options.
     * 
     * The `$options` array contains all options accepted by the
     * application, including their types, default values, descriptions,
     * requirements, and validation callbacks.
     * 
     * In general, you should not try to set $options yourself;
     * instead, use [[Solar_Getopt::setOption()]] and/or
     * [[Solar_Getopt::setOptions()]].
     * 
     * @var array
     * 
     */
    public $options = array();
    
    /**
     * 
     * Default option settings.
     * 
     * `long`
     * : (string) The long-form of the option name (e.g., "--foo-bar" would
     *   be "foo-bar").
     * 
     * `short`
     * : (string) The short-form of the option, if any (e.g., "-f" would be
     *   "f").
     * 
     * `descr`
     * : (string) A description of the option (used in "help" output).
     * 
     * `param`
     * : (string) When the option is present, does it take a parameter?  If so,
     *   the param can be "r[eq[uired]]" every time, or be "[o[pt[ional]]". If empty, no
     *   parameter for the option will be recognized (the option's value will be
     *   boolean true when the option is present).  Default is null; 
     *   recognizes `o`', `opt`, `optional`, `r`, `req`, and `required`.
     * 
     * `value`
     * : (mixed) The default value for the option parameter, if any.  This way,
     *   options not specified in the arguments can have a default value.
     * 
     * `require`
     * : (bool) At validation time, the option must have a non-blank value
     *   of some sort.
     * 
     * `filters`
     * : (array) An array of filters to apply to the parameter value.  This can
     *   be a single filter (`array('validateInt')`), or a series of filters
     *   (`array('validateInt', array('validateRange', -10, +10)`).
     * 
     * @var array
     * 
     */
    protected $_default = array(
        'long'    => null,
        'short'   => null,
        'param'   => null,
        'value'   => null,
        'descr'   => null,
        'require' => false,
        'filters' => array(),
    );
    
    /**
     * 
     * The arguments passed in from the command line.
     * 
     * @var array
     * 
     * @see populate()
     * 
     */
    protected $_argv;
    
    /**
     * 
     * List of names for invalid option values, and error messages.
     * 
     * @var array
     * 
     */
    protected $_invalid = array();
    
    /**
     * 
     * Option values parsed from the arguments, as well as remaining (numeric)
     * arguments.
     * 
     * @var array
     * 
     */
    protected $_values;
    
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
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
        
        // set up the data-filter class
        $this->_filter = Solar::factory($this->_config['filter_class']);
    }
    
    // -----------------------------------------------------------------
    //
    // Option-management methods
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Sets one option for recognition.
     * 
     * @param string $name The option name to set or add; overrides
     * $info['short'] if 1 character long, otherwise overrides $info['long'].
     * 
     * @param array $info Option information using the same keys
     * as [[Solar_Getopt::$_default]].
     * 
     * @return void
     * 
     */
    public function setOption($name, $info)
    {
        // prepare the option info
        $info = array_merge($this->_default, $info);
        
        // override the short- or long-form of the option
        if (strlen($name) == 1) {
            $info['short'] = $name;
        } else {
            // convert underscores to dashes for the *cli*
            $info['long'] = str_replace('_', '-', $name);
        }
        
        // normalize the "param" setting
        $param = strtolower($info['param']);
        if ($param == 'r' || substr($param, 0, 3) == 'req') {
            $info['param'] = 'required';
        } elseif ($param == 'o' || substr($param, 0, 3) == 'opt') {
            $info['param'] = 'optional';
        } else {
            $info['param'] = null;
        }
        
        // convert dashes to underscores for the *key*
        $name = str_replace('-', '_', $name);
        
        // forcibly cast each of the keys in the options array
        $this->options[$name] = array(
            'long'    => $info['long'],
            'short'   => substr($info['short'], 0, 1),
            'param'   => $info['param'],
            'value'   => $info['value'],
            'descr'   => $info['descr'],
            'require' => (bool) $info['require'],
            'filters' => array(),
            'present' => false, // present in the cli command?
        );
        
        // retain and fix any filters for the option value
        if ($info['filters']) {
            
            // make sure filters are an array
            settype($info['filters'], 'array');
            
            // make sure that strings are converted to arrays so that
            // validate() works properly.
            foreach ($info['filters'] as $key => $list) {
                if (is_string($list)) {
                    $info['filters'][$key] = array($list);
                }
            }
        }
    }
    
    /**
     * 
     * Sets multiple acceptable options. Appends if they do not exist.
     * 
     * @param array $list Argument information as array(name => info), where
     * each info value is an array like Solar_Getopt::$_default.
     * 
     * @return void
     * 
     */
    public function setOptions($list)
    {
        if (! empty($list)) {
            foreach ($list as $name => $info) {
                $this->setOption($name, $info);
            }
        }
    }
    
    /**
     * 
     * Populates the options with values from $argv.
     * 
     * For a given option on the command line, these values will result:
     * 
     * `--foo-bar`
     * : `'foo_bar' => true`
     * 
     * `--foo-bar=baz`
     * : `'foo_bar' => 'baz'`
     * 
     * `--foo-bar="baz dib zim"`
     * : `'foo_bar' => 'baz dib zim'`
     * 
     * `-s`
     * : `'s' => true`
     * 
     * `-s dib`
     * : `'s' => 'dib'`
     * 
     * `-s "dib zim gir"`
     * : `'s' => 'dib zim gir'`
     * 
     * Short-option clusters are parsed as well, so that `-fbz` will result
     * in `array('f' => true, 'b' => true, 'z' => true)`.  Note that you 
     * cannot pass parameters to an option in a cluster.
     * 
     * If an option is not defined, an exception will be thrown.
     * 
     * Options values are stored under the option key name, not the short-
     * or long-format version of the option. For example, an option named
     * 'foo-bar' with a short-form of 'f' will be stored under 'foo-bar'.
     * This helps deconflict between long- and short-form aliases.
     * 
     * @param array $argv The argument values passed on the command line.  If
     * empty, will use $_SERVER['argv'] after shifting off its first element.
     * 
     * @return void
     * 
     */
    public function populate($argv = null)
    {
        // get the command-line arguments
        if ($argv === null) {
            $argv = $this->_request->argv();
            array_shift($argv);
        } else {
            $argv = (array) $argv;
        }
        
        // hold onto the argv source
        $this->_argv = $argv;
        
        // reset values to defaults
        $this->_values = array();
        foreach ($this->options as $name => $info) {
            $this->_values[$name] = $info['value'];
        }
        
        // flag to say when we've reached the end of options
        $done = false;
        
        // shift each element from the top of the $argv source
        while (true) {
            
            // get the next argument
            $arg = array_shift($this->_argv);
            if ($arg === null) {
                // no more args, we're done
                break;
            }
            
            // after a plain double-dash, all values are numeric (not options)
            if ($arg == '--') {
                $done = true;
                continue;
            }
            
            // if we're reached the end of options, just add to the numeric
            // values.
            if ($done) {
                $this->_values[] = $arg;
                continue;
            }
            
            // long, short, or numeric?
            if (substr($arg, 0, 2) == '--') {
                // long
                $this->_values = array_merge(
                    $this->_values,
                    (array) $this->_parseLong($arg)
                );
            } elseif (substr($arg, 0, 1) == '-') {
                // short
                $this->_values = array_merge(
                    $this->_values,
                    (array) $this->_parseShort($arg)
                );
            } else {
                // numeric
                $this->_values[] = $arg;
            }
        }
    }
    
    /**
     * 
     * Applies validation and sanitizing filters to the option values.
     * 
     * @return bool True if all values are valid, false if not.
     * 
     */
    public function validate()
    {
        // reset previous invalidations
        $this->_invalid = array();
        
        // reset the filter chain so we can rebuild it
        $this->_filter->resetChain();
        
        // build the filter chain and requires
        foreach ($this->options as $name => $info) {
            if ($info['present'] && $info['param'] == 'required') {
                $info['filters'][] = 'validateNotBlank';
            }
            $this->_filter->addChainFilters($name, $info['filters']);
            $this->_filter->setChainRequire($name, $info['require']);
        }
        
        // apply the filter chain to the option values
        $status = $this->_filter->applyChain($this->_values);
        
        // retain any invalidation messages
        $invalid = $this->_filter->getChainInvalid();
        foreach ((array) $invalid as $key => $val) {
            $this->_invalid[$key] = $val;
        }
        
        // done
        return $status;
    }
    
    /**
     * 
     * Returns a list of invalid options and their error messages (if any).
     * 
     * @return array
     * 
     */
    public function getInvalid()
    {
        return $this->_invalid;
    }
    
    /**
     * 
     * Returns the populated option values.
     * 
     * @return array
     * 
     */
    public function values()
    {
        return $this->_values;
    }
    
    /**
     * 
     * Parse a long-form option.
     * 
     * @param string $arg The $argv element, e.g. "--foo" or "--bar=baz".
     * 
     * @return array An associative array where the key is the option name and
     * the value is the option value.
     * 
     */
    protected function _parseLong($arg)
    {
        // strip the leading "--"
        $arg = substr($arg, 2);
        
        // find the first = sign
        $eqpos = strpos($arg, '=');
        
        // get the key for name lookup
        if ($eqpos === false) {
            $key = $arg;
            $value = null;
        } else {
            $key = substr($arg, 0, $eqpos);
            $value = substr($arg, $eqpos+1);
        }
        
        // is this a recognized option?
        $name = $this->_getOptionName('long', $key);
        if (! $name) {
            return;
        }
        
        // the option is present
        $this->options[$name]['present'] = true;
        
        // was a value specified with equals?
        if ($eqpos !== false) {
            // parse the value for the option param
            return $this->_parseParam($name, $value);
        }
        
        // value was not specified with equals;
        // is a param needed at all?
        $info = $this->options[$name];
        if (! $info['param']) {
            // defined as not-needing a param, treat as a flag.
            return array($name => true);
        }
        
        // the option was defined as needing a param (required or optional),
        // but there was no equals-sign.  this means we need to look at the
        // next element for a possible param value.
        // 
        // get the next element from $argv to see if it's a param.
        $value = array_shift($this->_argv);
        
        // make sure the element not an option itself.
        if (substr($value, 0, 1) == '-') {
            
            // the next element is an option, not a param.
            // this means no param is present.
            // put the element back into $argv.
            array_unshift($this->_argv, $value);
            
            // was the missing param required?
            if ($info['param'] == 'required') {
                // required but not present
                return array($name => null);
            } else {
                // optional but not present, treat as a flag
                return array($name => true);
            }
        }
        
        // parse the parameter for a required or optional value
        return $this->_parseParam($name, $value);
    }
    
    /**
     * 
     * Parse the parameter value for a named option.
     * 
     * @param string $name The option name.
     * 
     * @param string $value The parameter.
     * 
     * @return array An associative array where the option name is the key,
     * and the parsed parameter is the value.
     * 
     */
    protected function _parseParam($name, $value)
    {
        // get info about the option
        $info = $this->options[$name];
        
        // is the value blank?
        if (trim($value) == '') {
            // value is blank. was it required for the option?
            if ($info['param'] == 'required') {
                // required but blank.
                return array($name => null);
            } else {
                // optional but blank, treat as a flag.
                return array($name => true);
            }
        }
        
        // param was present and not blank.
        return array($name => $value);
    }
    
    /**
     * 
     * Parse a short-form option (or cluster of options).
     * 
     * @param string $arg The $argv element, e.g. "-f" or "-fbz".
     * 
     * @param bool $cluster This option is part of a cluster.
     * 
     * @return array An associative array where the key is the option name and
     * the value is the option value.
     * 
     */
    protected function _parseShort($arg, $cluster = false)
    {
        // strip the leading "-"
        $arg = substr($arg, 1);
        
        // re-process as a cluster?
        if (strlen($arg) > 1) {
            $data = array();
            foreach (str_split($arg) as $key) {
                $data = array_merge(
                    $data,
                    (array) $this->_parseShort("-$key", true)
                );
            }
            return $data;
        }
        
        // is the option defined?
        $name = $this->_getOptionName('short', $arg);
        if (! $name) {
            // not defined
            return;
        } else {
            // keep the option info
            $info = $this->options[$name];
        }
        
        // the option is present
        $this->options[$name]['present'] = true;
        
        // are we processing as part of a cluster?
        if ($cluster) {
            // is a param required for the option?
            if ($info['param'] == 'required') {
                // can't get params when in a cluster.
                return array($name => null);
            } else {
                // param was optional or not needed, treat as a flag.
                return array($name => true);
            }
        }
        
        // not processing as part of a cluster.
        // does the option need a param?
        if (! $info['param']) {
            // defined as not-needing a param, treat as a flag.
            return array($name => true);
        }
        
        // the option was defined as needing a param (required or optional).
        // get the next element from $argv to see if it's a param.
        $value = array_shift($this->_argv);
        
        // make sure the element not an option itself.
        if (substr($value, 0, 1) == '-') {
            
            // the next element is an option, not a param.
            // this means no param is present.
            // put the element back into $argv.
            array_unshift($this->_argv, $value);
            
            // was the missing param required?
            if ($info['param'] == 'required') {
                // required but not present
                return array($name => null);
            } else {
                // optional but not present, treat as a flag
                return array($name => true);
            }
        }
        
        // parse the parameter for a required or optional value
        return $this->_parseParam($name, $value);
    }
    
    /**
     * 
     * Gets an option name from its short or long format.
     * 
     * @param string $type Look in the 'long' or 'short' key for option names.
     * 
     * @param string $spec The long or short format of the option name.
     * 
     * @return string
     * 
     */
    protected function _getOptionName($type, $spec)
    {
        foreach ($this->options as $name => $info) {
            if ($info[$type] == $spec) {
                return $name;
            }
        }
        
        // if not in strict mode, we can let this go
        if (! $this->_config['strict']) {
            return;
        }
        
        // not found, blow up
        if ($type == 'short') {
            $spec = "-$spec";
        } else {
            $spec = "--$spec";
        }
        
        throw $this->_exception('ERR_UNKNOWN_OPTION', array(
            'type' => $type,
            'name' => $spec,
            'options' => $this->options,
        ));
    }
}