<?php
/**
 * 
 * Plugin-aware text-to-XHTML converter based on Markdown.
 * 
 * This package is ported from John Gruber's [Markdown][] script in Perl,
 * with many thanks to Michel Fortin for his [PHP Markdown][] port to
 * PHP 4.  Be sure to read up on [Markdown syntax][] as well.
 * 
 * Unlike Markdown and PHP Markdown, Solar_Markdown is plugin-aware.
 * Every processing rule is a separate class, and classes can be strung
 * together in a manner largely independent of each other (although the
 * order of processing still matters a great deal). The plugin
 * architecture is based on Paul's work from [Text_Wiki][].
 * 
 * While the Text_Wiki package is capable of converting to any rendering
 * format, Solar_Markdown only converts to XHTML. If you need to render
 * to something other than XHTML, you may wish to try a two-step output
 * process: from Markdown to XHTML, then from XHTML to your preferred
 * format.
 * 
 * [Markdown]:        http://daringfireball.net/projects/markdown/
 * [Markdown syntax]: http://daringfireball.net/projects/markdown/syntax/
 * [PHP Markdown]:    http://www.michelf.com/projects/php-markdown/
 * [Text_Wiki]:       http://pear.php.net/Text_Wiki
 * 
 * @category Solar
 * 
 * @package Solar_Markdown Plugin-based system to implement standard Markdown
 * syntax.
 * 
 * @author John Gruber <http://daringfireball.net/projects/markdown/>
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Markdown.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 * @todo How to configure plugins at the start?
 * 
 * @todo How to send a plugin object in the config?
 * 
 */
class Solar_Markdown extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config int tab_width Number of spaces per tab.  Default 4.
     * 
     * @config bool|array tidy If empty/false/null, do not use Tidy to post-process
     * the transformed output.  If true or a non-empty array, is a set of
     * config options to pass to Tidy when rendering output.
     * See also <http://php.net/tidy>.  Default false.
     * 
     * @config array plugins An array of plugins for the parser to use, in order.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown = array(
        
        'tab_width' => 4,
        
        'tidy' => false,
        
        'plugins' => array(
            // pre-processing on the source as a whole
            'Solar_Markdown_Plugin_Prefilter',
            'Solar_Markdown_Plugin_StripLinkDefs',
            
            // blocks
            'Solar_Markdown_Plugin_Header',
            'Solar_Markdown_Plugin_HorizRule',
            'Solar_Markdown_Plugin_List',
            'Solar_Markdown_Plugin_CodeBlock',
            'Solar_Markdown_Plugin_BlockQuote',
            'Solar_Markdown_Plugin_Html',
            'Solar_Markdown_Plugin_Paragraph',
            
            // spans
            'Solar_Markdown_Plugin_CodeSpan',
            'Solar_Markdown_Plugin_Image',
            'Solar_Markdown_Plugin_Link',
            'Solar_Markdown_Plugin_Uri',
            'Solar_Markdown_Plugin_Encode',
            'Solar_Markdown_Plugin_AmpsAngles',
            'Solar_Markdown_Plugin_EmStrong',
            'Solar_Markdown_Plugin_Break',
        ),
    );
    
    /**
     * 
     * Left-delimiter for HTML tokens.
     * 
     * @var string
     * 
     */
    protected $_html_ldelim = "\x0E";
    
    /**
     * 
     * Right-delimiter for HTML tokens.
     * 
     * @var string
     * 
     */
    protected $_html_rdelim = "\x0F";
    
    /**
     * 
     * Left-delimiter for encoded Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_char_ldelim = "\x02";
    
    /**
     * 
     * Right-delimiter for encoded Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_char_rdelim = "\x03";
    
    /**
     * 
     * Array of HTML blocks represented by delimited token numbers.
     * 
     * Format is token => html.
     * 
     * @var array
     * 
     */
    protected $_html = array();
    
    /**
     * 
     * Running count of $this->_html elements so we don't have to 
     * count($this->_html) each time we add an HTML token.
     * 
     * @var int
     * 
     */
    protected $_count = 0;
    
    /**
     * 
     * List of defined link references keyed on the link name.
     * 
     * Format is "link-name" => array('href' => ..., 'title' => ...).
     * 
     * Generally populated via the StripLinkDefs plugin.
     * 
     * @var array
     * 
     */
    protected $_link = array();
    
    /**
     * 
     * Escape table for special Markdown characters.
     * 
     * Format is "$char" => "\x1B$char\x1B".
     * 
     * @var array
     * 
     */
    protected $_esc = array();
    
    /**
     * 
     * Escape table for backslashed special Markdown characters.
     * 
     * Format is "\\$char" => "\x1B$char\x1B".
     * 
     * Note that the backslash escape table and the normal escape table
     * map to identical escape sequences.
     * 
     * @var array
     * 
     */
    protected $_bs_esc = array();
    
    /**
     * 
     * Array of all plugin objects.
     * 
     * Format is class name => object instance.
     * 
     * @var array
     * 
     */
    protected $_plugin = array();
    
    /**
     * 
     * Array of all block-type plugin class names.
     * 
     * Each plugin reports if it is a span or a block.
     * 
     * @var array
     * 
     */
    protected $_block_class = array();
    
    /**
     * 
     * Array of all span-type plugin class names.
     * 
     * Each plugin reports if it is a span or a block.
     * 
     * @var array
     * 
     */
    protected $_span_class = array();
    
    /**
     * 
     * Array of all plugin classes that need to prepare the text before
     * processing.
     * 
     * @var array
     * 
     */
    protected $_prepare_class = array();
    
    /**
     * 
     * Array of all plugin classes that need to clean up the text after
     * processing.
     * 
     * @var array
     * 
     */
    protected $_cleanup_class = array();
    
    /**
     * 
     * Special characters that should be encoded by Markdown.
     * 
     * This list will grow as plugins are added; they each report their
     * own list of special characters to be encoded.
     * 
     * @var array
     * 
     */
    protected $_chars = '.{}\\';
        
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * Loads the plugins, builds a list of characters to encode, and
     * builds the list of block-type and span-type plugin classes.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // use tidy?
        if ($this->_config['tidy'] && ! extension_loaded('tidy')) {
            // tidy requested but not loaded
            throw $this->_exception('ERR_EXTENSION_NOT_LOADED', array(
                'extension' => 'tidy',
            ));
        }
        
        // load each plugin object
        foreach ($this->_config['plugins'] as $spec) {
            
            if (is_object($spec)) {
                $class = get_class($spec);
                $plugin = $spec;
            } else {
                $class = $spec;
                $plugin = Solar::factory($class);
            }
            
            // save the plugin
            $this->_plugin[$class] = $plugin;
            $plugin->setMarkdown($this);
            
            // does it need to prepare the text?
            if ($plugin->isPrepare()) {
                $this->_prepare_class[] = $class;
            }
            
            // is it a block plugin?
            if ($plugin->isBlock()) {
                $this->_block_class[] = $class;
            }
            
            // is it a span plugin?
            if ($plugin->isSpan()) {
                $this->_span_class[] = $class;
            }
            
            // does it need to clean up the text?
            if ($plugin->isCleanup()) {
                $this->_cleanup_class[] = $class;
            }
            
            // find out what characters this plugin thinks should be
            // encoded as "special" Markdown characters.
            $this->_chars .= $plugin->getChars();
        }
        
        // build the character escape tables
        $k = strlen($this->_chars);
        for ($i = 0; $i < $k; ++ $i) {
            $char = $this->_chars[$i];
            $delim = $this->_char_ldelim . $i. $this->_char_rdelim;
            $this->_esc[$char] = $delim;
            $this->_bs_esc["\\$char"] = $delim;
            
        }
    }
    
    /**
     * 
     * Returns an internal Markdown plugin object for direct manipulation
     * and inspection.
     * 
     * @param string $class The plugin class name.
     * 
     * @return object The requested Markdown plugins.
     * 
     */
    public function getPlugin($class)
    {
        return $this->_plugin[$class];
    }
    
    /**
     * 
     * One-step transformation of source text using plugins.
     * 
     * Calls reset(), prepare(), processBlocks(), cleanup(), and
     * render() using the source text.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed text after all processing and
     * rendering.
     * 
     */
    public function transform($text)
    {
        $this->reset();
        $text = $this->prepare($text);
        $text = $this->processBlocks($text);
        $text = $this->cleanup($text);
        return $this->render($text);
    }
    
    /**
     * 
     * Resets Markdown and all its plugins for a new transformation.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        // reset from previous transformations
        $this->_count = 0;
        $this->_html = array();
        $this->_link = array();
        
        // reset the plugins
        foreach ($this->_plugin as $plugin) {
            $plugin->reset();
        }
    }
    
    /**
     * 
     * Prepares the text for processing by running the prepare() 
     * method of each plugin, in order, on the source text.
     * 
     * Also calls the plugin's reset() method, and resets internal
     * counters and arrays.  This is to support work with multiple
     * separate text sources.
     * 
     * @param string $text The source text.
     * 
     * @return string The source text after processing.
     * 
     */
    public function prepare($text)
    {
        // let each plugin prepare the source text for parsing
        foreach ($this->_prepare_class as $class) {
            $text = $this->_plugin[$class]->prepare($text);
        }
        return $text;
    }
    
    /**
     * 
     * Runs the source text through all block-type plugins.
     * 
     * @param string $text The source text.
     * 
     * @return string The source text after processing.
     * 
     */
    public function processBlocks($text)
    {
        foreach ($this->_block_class as $class) {
            $text = $this->_plugin[$class]->parse($text);
        }
        return $text;
    }
    
    /**
     * 
     * Runs the processed text through each plugin's cleanup() method.
     * 
     * This is so that the plugins can "clean up" after all main
     * has been completed.
     * 
     * @param string $text The processed text.
     * 
     * @return string The processed text after cleanup.
     * 
     */
    public function cleanup($text)
    {
        // let each plugin clean up the rendered source
        foreach ($this->_cleanup_class as $class) {
            $text = $this->_plugin[$class]->cleanup($text);
        }
        return $text;
    }
    
    /**
     * 
     * Returns a final rendering of the processed text.
     * 
     * This replaces any remaining HTML tokens, un-encodes special
     * Markdown characters, and optionally runs the text through
     * [Tidy][].
     * 
     * [Tidy]: http://php.net/tidy
     * 
     * @param string $text The processed and cleaned text.
     * 
     * @return string The final rendering of the text.
     * 
     */
    public function render($text)
    {
        // replace any remaining HTML tokens
        $text = $this->unHtmlToken($text);
        
        // replace all special chars in the text.
        $text = $this->unEncode($text);
        
        if (! $this->_config['tidy']) {
            // tidy explicitly disabled
            return $text;
        }
        
        // tidy up the text
        $tidy = new tidy;
        $opts = (array) $this->_config['tidy'];
        $tidy->parseString($text, $opts, 'utf8');
        $tidy->cleanRepair();
        
        // get only the body portion
        $body = trim(tidy_get_body($tidy)->value);
        
        // remove <body> and </body>
        return substr($body, 6, -7);
    }
    
    // -----------------------------------------------------------------
    // 
    // General support methods for plugins.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Runs the source text through all span-type plugins.
     * 
     * Generally this is called by block-type plugins, not by the
     * Markdown engine directly.
     * 
     * @param string $text The source text.
     * 
     * @return string The source text after processing.
     * 
     */
    public function processSpans($text)
    {
        foreach ($this->_span_class as $class) {
            $text = $this->_plugin[$class]->parse($text);
        }
        return $text;
    }
    
    /**
     * 
     * Returns the number of spaces per tab.
     * 
     * @return int
     * 
     */
    public function getTabWidth()
    {
        return (int) $this->_config['tab_width'];
    }
    
    // -----------------------------------------------------------------
    // 
    // HTML token processing support methods for plugins.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Saves a pieces of text as HTML and returns a delimited token.
     * 
     * @param string $text The text to retain as HTML.
     * 
     * @return An HTML token.
     * 
     */
    public function toHtmlToken($text)
    {
        $key = $this->_html_ldelim
             . $this->_count
             . $this->_html_rdelim;
        
        $this->_html[$this->_count ++] = $text;
        return $key;
    }
    
    /**
     * 
     * Is a piece of text a delimited HTML token?
     * 
     * @param string $text The text to check.
     * 
     * @return bool True if a token, false if not.
     * 
     */
    public function isHtmlToken($text)
    {
        return preg_match(
            "/^{$this->_html_ldelim}.*?{$this->_html_rdelim}$/",
            $text
        );
    }
    
    /**
     * 
     * Replaces all HTML tokens in source text with saved HTML.
     * 
     * @param string $text The text to do replacements in.
     * 
     * @return string The source text with HTML in place of tokens.
     * 
     */
    public function unHtmlToken($text)
    {
        $regex = "/{$this->_html_ldelim}(.*?){$this->_html_rdelim}/";
        
        while (preg_match_all($regex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $val) {
                $text = str_replace(
                    $val[0],
                    $this->_html[$val[1]],
                    $text
                );
            }
        }
        
        return $text;
    }
    
    // -----------------------------------------------------------------
    // 
    // Escaping for HTML and encoding for special Markdown characters.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Escapes HTML in source text.
     * 
     * Uses htmlspecialchars() with UTF-8.
     * 
     * @param string $text Source text.
     * 
     * @param int $quotes How to escape quotes; default ENT_COMPAT.
     * 
     * @return string The escaped text.
     * 
     */
    public function escape($text, $quotes = ENT_COMPAT)
    {
        return htmlspecialchars($text, $quotes, 'UTF-8');
    }
    
    /**
     * 
     * Encodes special Markdown characters so they are not recognized
     * when parsing.
     * 
     * @param string $text The source text.
     * 
     * @param bool $only_backslash Only encode backslashed characters.
     * 
     * @return string The encoded text.
     * 
     */
    public function encode($text, $only_backslash = false)
    {
        $list = $this->_explodeTags($text);
        
        // reset text and rebuild from the list
        $text = '';
        
        if ($only_backslash) {
            foreach ($list as $item) {
                $text .= $this->_encode($item[1]);
            }
        } else {
            foreach ($list as $item) {
                if ($item[0] == 'tag') {
                    $text .= $this->_encode($item[1], true);
                } else {
                    $text .= $this->_encode($item[1]);
                }
            }
        }
        
        return $text;
    }
    
    /**
     * 
     * Un-encodes special Markdown characters.
     * 
     * @param string $text The text with encocded characters.
     * 
     * @return string The un-encoded text.
     * 
     */
    public function unEncode($text)
    {
        // because the bs_esc table uses the same values (just
        // different keys), this will catch both regular and
        // backslashes chars.
        $chars = array_flip($this->_esc);
        $text = str_replace(
            array_keys($chars),
            array_values($chars),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support method for encode().
     * 
     * @param string $text The source text.
     * 
     * @param bool $in_tag Escaping inside a tag?
     * 
     * @return string The encoded text.
     * 
     */
    protected function _encode($text, $in_tag = false)
    {
        if ($in_tag) {
            // inside a tag
            $chars = $this->_esc;
        } else {
            // outside a tag, or between tags
            $chars = $this->_bs_esc;
        }
        
        return str_replace(
            array_keys($chars),
            array_values($chars),
            $text
        );
    }
    
    /**
     * 
     * Explodes source text into tags and text
     * 
     * Regular expression derived from the _tokenize() subroutine in 
     * Brad Choate's [MTRegex][] plugin.
     * 
     * [MTRegex]: http://www.bradchoate.com/past/mtregex.php
     * 
     * From the original notes ...
     * 
     * > Returns an array of the tokens comprising the input string.
     * > Each token is either a tag (possibly with nested, tags
     * > contained therein, such as <a href="<MTFoo>">, or a run of
     * > text between tags. Each element of the array is a
     * > two-element array; the first is either 'tag' or 'text'; the
     * > second is the actual value.
     * 
     * @param string $str A string of HTML.
     * 
     * @return array The string exploded into tag and non-tag portions.
     * 
     */
    protected function _explodeTags($str)
    {
        $index = 0;
        $list = array();
        
        $match = '(?s:<!(?:--.*?--\s*)+>)|'.    # comment
                 '(?s:<\?.*?\?>)|'.             # processing instruction
                                                # regular tags
                 '(?:<[/!$]?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)'; 
                 
        $parts = preg_split("{($match)}", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        foreach ($parts as $part) {
            if (++$index % 2 && $part != '') {
                $list[] = array('text', $part);
            } else {
                $list[] = array('tag', $part);
            }
        }
        
        return $list;
    }
    
    // -----------------------------------------------------------------
    // 
    // Support methods for plugins, related to named link references.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Sets the value of a named link reference.
     * 
     * @param string $name The link reference name.
     * 
     * @param string $href The URI this link points to.
     * 
     * @param string $title Alternate title for the link.
     * 
     * @return void
     * 
     */
    public function setLink($name, $href, $title = null)
    {
        $this->_link[$name] = array(
            'href'  => $href,
            'title' => $title,
        );
    }
    
    /**
     * 
     * Returns the href and title of a named link reference.
     * 
     * @param string $name The link reference name.
     * 
     * @return bool|array If the link reference exists, returns an 
     * array with keys 'href' and 'title'.  If not, returns false.
     * 
     */
    public function getLink($name)
    {
        if (! empty($this->_link[$name])) {
            return $this->_link[$name];
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Returns an array of all defined link references.
     * 
     * @return array All links keyed by name, where each element is an
     * array with keys 'href' and 'title'.
     * 
     */
    public function getLinks()
    {
        return $this->_link;
    }
}
