<?php
/**
 * 
 * Pre-filters source text in the preparation phase.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 * @author John Gruber <http://daringfireball.net/projects/markdown/>
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Prefilter.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_Prefilter extends Solar_Markdown_Plugin
{
    /**
     * 
     * Run this plugin during the "prepare" phase.
     * 
     * @var bool
     * 
     */
    protected $_is_prepare = true;
    
    /**
     * 
     * Pre-filters source text in the preparation phase.
     * 
     * Converts DOS and Mac OS pre-X line endings to Unix line endings,
     * adds 2 newlines to the end of the source, and converts all
     * whitespace-only lines to simple newlines.  Also converts tabs
     * to spaces intelligently, keeping tab columns lined up.
     * 
     * @param string $text The source text.
     * 
     * @return string $text The text after being filtered.
     * 
     */
    public function prepare($text)
    {
        // Standardize DOS and Mac OS 9 line endings
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        
        // Make sure $text ends with a couple of newlines:
        $text .= "\n\n";
        
        // Convert tabs to spaces in a surprisingly nice-looking way.
        $text = $this->_tabsToSpaces($text);
        
        // Convert lines consisting only of spaces and tabs to simple
        // newlines.
        //
        // This makes subsequent regexen easier to write, because we can
        // match consecutive blank lines with /\n+/ instead of something
        // contorted like /[ \t]*\n+/ .
        $text = preg_replace('/^[ \t]+$/m', '', $text);
        
        // done
        return $text;
    }
    
    /**
     * 
     * Replaces tabs with the appropriate number of spaces.
     * 
     * <http://www.mail-archive.com/macperl-anyperl@perl.org/msg00144.html>
     * 
     * > It will take into account the length of the string before the tab
     * > starting from the start of the string, from the previous newline, or
     * > from the last replaced tab; and pad with 1 to 4 spaces so the string
     * > length becomes the next multiple of 4.
     * 
     * @param string $text A block of text with tabs.
     * 
     * @return string The same block of text, with tabs converted to 
     * spaces so that columns still line up.
     * 
     */
    protected function _tabsToSpaces($text)
    {
        // For each line we separate the line in blocks delemited by
        // tab characters. Then we reconstruct every line by adding the 
        // appropriate number of space between each blocks.
        $lines = explode("\n", $text);
        $text = "";
        $tab_width = $this->_getTabWidth();
        
        foreach ($lines as $line) {
            // Split in blocks.
            $blocks = explode("\t", $line);
            // Add each blocks to the line.
            $line = $blocks[0];
            unset($blocks[0]); # Do not add first block twice.
            foreach ($blocks as $block) {
                // Calculate width, insert spaces, insert block.
                $amount = $tab_width - strlen($line) % $tab_width;
                $line .= str_repeat(" ", $amount) . $block;
            }
            $text .= "$line\n";
        }
        return $text;
    }
}
