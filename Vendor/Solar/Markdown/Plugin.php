<?php
/**
 * 
 * Abstract class for Markdown plugins.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Plugin.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
abstract class Solar_Markdown_Plugin extends Solar_Base {
    
    /**
     * 
     * Default configuration values.
     * 
     * @config Solar_Markdown markdown The "parent" Markdown object.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown_Plugin = array(
        'markdown' => null,
    );
    
    /**
     * 
     * The "parent" Markdown object.
     * 
     * @var Solar_Markdown
     * 
     */
    protected $_markdown;
    
    /**
     * 
     * The characters this plugin uses for parsing, which should be
     * encoded by other other plugins.
     * 
     * @var string
     * 
     */
    protected $_chars = '';
    
    /**
     * 
     * The max depth for nested brackets.
     * 
     * @var int
     * 
     */
    protected $_nested_brackets_depth = 6;
    
    /**
     * 
     * The regular expression for nested brackets.
     * 
     * Built by the constructor  based on $_nested_brackets_depth.
     * 
     * @var string
     * 
     */
    protected $_nested_brackets = '';
    
    /**
     * 
     * Do **not** run this plugin during the "prepare" phase.
     * 
     * @var bool
     * 
     */
    protected $_is_prepare = false;
    
    /**
     * 
     * This is **not** a block plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_block = false;
    
    /**
     * 
     * This is **not** a span plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_span = false;
    
    /**
     * 
     * Do **not** run this plugin during the "cleanup" phase.
     * 
     * @var bool
     * 
     */
    protected $_is_cleanup = false;
    
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
        
        $this->_nested_brackets = 
            str_repeat('(?>[^\[\]]+|\[', $this->_nested_brackets_depth).
            str_repeat('\])*', $this->_nested_brackets_depth);
        
        if (! empty($this->_config['markdown'])) {
            $this->setMarkdown($this->_config['markdown']);
        }
    }
    
    /**
     * 
     * Sets the "parent" Markdown object.
     * 
     * @param Solar_Markdown $markdown The "parent" Markdown object.
     * 
     * @return void
     * 
     */
    public function setMarkdown($markdown)
    {
        $this->_markdown = $markdown;
    }
    
    /**
     * 
     * Run this plugin during the "prepare" phase?
     * 
     * @return bool
     * 
     */
    public function isPrepare()
    {
        return (bool) $this->_is_prepare;
    }
    
    /**
     * 
     * Is this a block-level plugin?
     * 
     * Reports the value of $this->_is_block.
     * 
     * @return bool
     * 
     */
    public function isBlock()
    {
        return (bool) $this->_is_block;
    }
    
    /**
     * 
     * Is this a span-level plugin?
     * 
     * Reports the value of $this->_is_span.
     * 
     * @return bool
     * 
     */
    public function isSpan()
    {
        return (bool) $this->_is_span;
    }
    
    /**
     * 
     * Run this plugin during the "cleanup" phase?
     * 
     * @return bool
     * 
     */
    public function isCleanup()
    {
        return (bool) $this->_is_cleanup;
    }
    
    /**
     * 
     * Get the list of characters this plugin uses for parsing.
     * 
     * @return string
     * 
     */
    public function getChars()
    {
        return $this->_chars;
    }
    
    /**
     * 
     * Resets this plugin to its original state (for multiple parsings).
     * 
     * @return void
     * 
     */
    public function reset()
    {
    }
    
    /**
     * 
     * Prepares the source text before any parsing occurs.
     * 
     * Returns the text as-is.
     * 
     * @param string $text The source text.
     * 
     * @return string $text The text after being filtered.
     * 
     */
    public function prepare($text)
    {
        return $text;
    }
    
    /**
     * 
     * Parses the source text and replaces with HTML or tokens.
     * 
     * Returns the text as-is.
     * 
     * @param string $text The source text.
     * 
     * @return string The text after parsing.
     * 
     */
    public function parse($text)
    {
        return $text;
    }
     
    /**
     * 
     * Cleans up the source text after all parsing occurs.
     * 
     * Returns the text as-is.
     * 
     * @param string $text The source text.
     * 
     * @return string $text The text after being filtered.
     * 
     */
    public function cleanup($text)
    {
        return $text;
    }
    
    /**
     * 
     * Removes one level of leading tabs or space from a text block.
     * 
     * For example, if a block of text is indented by 3 tabs, it will be
     * returned as indented with only 2 tabs.
     * 
     * @param string $text A block of text.
     * 
     * @return string The same text out-dented by one level of tabs
     * or spaces.
     * 
     */
    protected function _outdent($text)
    {
        $tab_width = $this->_getTabWidth();
        return preg_replace(
            "/^(\\t|[ ]{1,$tab_width})/m",
            "",
            $text
        );
    }
    
    /**
     * 
     * Escapes HTML in source text.
     * 
     * @param string $text Source text.
     * 
     * @param int $quotes A PHP entities constant, default ENT_COMPAT.
     * 
     * @return string The escaped text.
     * 
     */
    protected function _escape($text, $quotes = ENT_COMPAT)
    {
        return $this->_markdown->escape($text, $quotes);
    }
    
    /**
     * 
     * Encodes special Markdown characters to they are not parsed by
     * plugins.
     * 
     * @param string $text Source text.
     * 
     * @param bool $only_backslash Only encode backslashed characters.
     * 
     * @return string The encoded text.
     * 
     */
    protected function _encode($text, $only_backslash = false)
    {
        return $this->_markdown->encode($text, $only_backslash);
    }
    
    /**
     * 
     * Processes the text using block-type plugins.
     * 
     * Good for finding blocks within blocks.
     * 
     * @param string $text Source text.
     * 
     * @return string The source text after block parsing.
     * 
     */
    protected function _processBlocks($text)
    {
        return $this->_markdown->processBlocks($text);
    }
    
    /**
     * 
     * Processes the text using span-type plugins.
     * 
     * @param string $text Source text.
     * 
     * @return string The source text after span parsing.
     * 
     */
    protected function _processSpans($text)
    {
        return $this->_markdown->processSpans($text);
    }
    
    
    /**
     * 
     * Returns the number of spaces per tab.
     * 
     * @return int
     * 
     */
    protected function _getTabWidth()
    {
        return $this->_markdown->getTabWidth();
    }
    
    /**
     * 
     * Converts a piece of text to a delimited HTML token.
     * 
     * @param string $text The text to save as an HTML token.
     * 
     * @return string A delimited HTML token.
     * 
     */
    protected function _toHtmlToken($text)
    {
        return $this->_markdown->toHtmlToken($text);
    }
    
    /**
     * 
     * Is a piece of text a delimited HTML token?
     * 
     * @param string $text The text to check as an HTML token.
     * 
     * @return bool True if a token, false if not.
     * 
     */
    protected function _isHtmlToken($text)
    {
        return $this->_markdown->isHtmlToken($text);
    }
    
    /**
     * 
     * Converts all delimited HTML tokens back into HTML.
     * 
     * @param string $text The text to un-tokenize.
     * 
     * @return string The text with HTML tokens replaced.
     * 
     */
    protected function _unHtmlToken($text)
    {
        return $this->_markdown->unHtmlToken($text);
    }
}
