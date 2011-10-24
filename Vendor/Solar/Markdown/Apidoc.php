<?php
/**
 * 
 * Markdown engine rules for wiki markup.
 * 
 * This class implements a plugin set for the Markdown-Extra syntax;
 * be sure to visit the [Markdown-Extra][] site for syntax examples.
 * 
 * [Markdown-Extra]: http://www.michelf.com/projects/php-markdown/extra/
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc Plugin-based system to implement a 
 * Solar-specific wiki form of the Markdown syntax.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Apidoc.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 * @todo Implement the markdown-in-html portion of Markdown-Extra.
 * 
 */
class Solar_Markdown_Apidoc extends Solar_Markdown
{
    /**
     * 
     * Default configuration values.
     * 
     * This sets the plugins and their processing order for the engine.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown_Apidoc = array(
        'plugins' => array(
            
            // highest-priority prepare and cleanup
            'Solar_Markdown_Plugin_Prefilter',
            
            // for Markdown images and links
            'Solar_Markdown_Plugin_StripLinkDefs',
            
            // blocks
            'Solar_Markdown_Apidoc_MethodSynopsis',
            'Solar_Markdown_Apidoc_Table',
            'Solar_Markdown_Apidoc_Section',
            'Solar_Markdown_Apidoc_List',
            'Solar_Markdown_Apidoc_VariableList',
            'Solar_Markdown_Apidoc_ProgramListing',
            'Solar_Markdown_Apidoc_Screen',
            'Solar_Markdown_Plugin_BlockQuote', // should add Wiki_BlockQuote with "cite/attribution"
            'Solar_Markdown_Apidoc_Paragraph',
            
            // spans
            'Solar_Markdown_Apidoc_Literal',
            'Solar_Markdown_Apidoc_ClassPage',
            'Solar_Markdown_Apidoc_Link',
            'Solar_Markdown_Apidoc_Uri',
            'Solar_Markdown_Plugin_Encode',
            'Solar_Markdown_Apidoc_EmStrong',
            'Solar_Markdown_Wiki_Escape',
        ),
    );
}
