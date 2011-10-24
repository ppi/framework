<?php
/**
 * 
 * Block class to form tables from Markdown syntax.
 * 
 * Syntax is ...
 * 
 *     |  Header 1  |  Header 2  |  Header N 
 *     | ---------- | ---------- | ----------
 *     | data cell  | data cell  | data cell 
 *     | data cell  | data cell  | data cell 
 *     | data cell  | data cell  | data cell 
 *     | data cell  | data cell  | data cell 
 * 
 * You can force columns alignment by putting a colon in the header-
 * underline row.
 * 
 *     | Left-Aligned |  No Align | Right-Aligned 
 *     | :----------- | --------- | -------------:
 *     | data cell    | data cell | data cell      
 *     | data cell    | data cell | data cell      
 *     | data cell    | data cell | data cell      
 *     | data cell    | data cell | data cell      
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Extra
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Table.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_Table extends Solar_Markdown_Plugin
{
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
     * Uses these chars for parsing.
     * 
     * @var string
     * 
     */
    protected $_chars = '|';
    
    /**
     * 
     * Transforms Markdown syntax to XHTML tables.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $less_than_tab = $this->_getTabWidth() - 1;
        
        // Find tables with leading pipe.
        //
        //    | Header 1 | Header 2
        //    | -------- | --------
        //    | Cell 1   | Cell 2
        //    | Cell 3   | Cell 4
        // 
        $text = preg_replace_callback('
            {
                (                               # optional caption
                    ^(.+)[ \t]*                 # $2: caption text
                    \n=+[ \t]*\n                # separator
                )?
                ^                               # Start of a line
                [ ]{0,'.$less_than_tab.'}       # Allowed whitespace.
                [|]                             # Optional leading pipe (present)
                (.+) \n                         # $3: Header row (at least one pipe)
                                    
                [ ]{0,'.$less_than_tab.'}       # Allowed whitespace.
                [|] ([ ]*[-:]+[-| :]*) \n       # $4: Header underline
                                    
                (                               # $5: Cells
                    (?:                     
                        [ ]*                    # Allowed whitespace.
                        [|] .* \n               # Row content.
                    )*                      
                )                           
                (?=\n|\Z)                       # Stop at final double newline.
            }xm',
            array($this, '_parsePipe'),
            $text
        );
    
        //
        // Find tables without leading pipe.
        //
        //    Header 1 | Header 2
        //    -------- | --------
        //    Cell 1   | Cell 2
        //    Cell 3   | Cell 4
        //
        $text = preg_replace_callback('
            {
                (                               # optional caption
                    ^(.+)[ \t]*                 # $2: caption text
                    \n=+[ \t]*\n                # separator
                )?
                ^                               # Start of a line
                [ ]{0,'.$less_than_tab.'}       # Allowed whitespace.
                (\S.*[|].*) \n                  # $3: Header row (at least one pipe)
                                        
                [ ]{0,'.$less_than_tab.'}       # Allowed whitespace.
                ([-:]+[ ]*[|][-| :]*) \n        # $4: Header underline
                                        
                (                               # $5: Cells
                    (?:                         
                        .* [|] .* \n            # Row content
                    )*                          
                )                               
                (?=\n|\Z)                       # Stop at final double newline.
            }xm',
            array($this, '_parsePlain'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for leading-pipe syntax.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parsePipe($matches)
    {
        // Remove leading pipe for each row.
        $matches[3]    = preg_replace('/^ *[|]/m', '', $matches[3]);
        return $this->_parsePlain($matches);
    }
    
    /**
     * 
     * Support callback for table conversion.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parsePlain($matches)
    {
        $caption    = $matches[2];
        $head       = $matches[3];
        $underline  = $matches[4];
        $content    = $matches[5];
        
        // Remove any tailing pipes for each line.
        $head       = preg_replace('/[|] *$/m', '', $head);
        $underline  = preg_replace('/[|] *$/m', '', $underline);
        $content    = preg_replace('/[|] *$/m', '', $content);
        
        // Reading alignment from header underline.
        $separators = preg_split('/ *[|] */', $underline);
        $attr = array();
        foreach ($separators as $n => $s) {
            if (preg_match('/^ *-+: *$/', $s)) {
                $attr[$n] = ' align="right"';
            } elseif (preg_match('/^ *:-+: *$/', $s)) {
                $attr[$n] = ' align="center"';
            } elseif (preg_match('/^ *:-+ *$/', $s)) {
                $attr[$n] = ' align="left"';
            } else {
                $attr[$n] = '';
            }
        }
        
        // handle all spans at once, not just code spans
        $head      = $this->_processSpans($head);
        $headers   = preg_split('/ *[|] */', $head);
        $col_count = count($headers);
        
        // begin the table
        if ($caption) {
            $text = "\n<table>\n"
                  . "    <caption>"
                  .  $this->_processSpans($caption)
                  .  "</caption>\n";
        } else {
            $text = "\n<informaltable>\n";
        }
        
        // Write column headers.
        $text .= "    <thead>\n";
        $text .= "        <tr>\n";
        foreach ($headers as $n => $header) {
            $text .= "            <th$attr[$n]>". trim($header) ."</th>\n";
        }
        $text .= "        </tr>\n";
        $text .= "    </thead>\n";
        
        // Split content by row.
        $rows = explode("\n", trim($content, "\n"));
    
        $text .= "    <tbody>\n";
        foreach ($rows as $row) {
            // handle all spans at once, not just code spans
            $row = $this->_processSpans($row);
        
            // Split row by cell.
            $row_cells = preg_split('/ *[|] */', $row, $col_count);
            $row_cells = array_pad($row_cells, $col_count, '');
        
            $text .= "        <tr>\n";
            foreach ($row_cells as $n => $cell) {
                $text .= "            <td$attr[$n]>". trim($cell) ."</td>\n";
            }
            $text .= "        </tr>\n";
        }
        $text .= "    </tbody>\n";
        
        if ($caption) {
            $text .= "</table>\n";
        } else {
            $text .= "</informaltable>\n";
        }
    
        return $this->_toHtmlToken($text) . "\n";
    }
}
