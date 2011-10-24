<?php
/**
 * 
 * Span plugin to change `` `text` `` to `<literal>text</literal>`.
 * 
 * Backtick quotes are used for `<code></code>` spans.
 * 
 * You can use multiple backticks as the delimiters if you want to
 * include literal backticks in the code span. So, this input ...
 * 
 *     Just type ``foo `bar` baz`` at the prompt.
 * 
 * ... will translate to ...
 * 
 *     <para>Just type <literal>foo `bar` baz</literal> at the prompt.</para>
 * 
 * There's no arbitrary limit to the number of backticks you
 * can use as delimters. If you need three consecutive backticks
 * in your code, use four for delimiters, etc.
 * 
 * You can use spaces to get literal backticks at the edges ...
 * 
 *     type `` `bar` ``
 * 
 * ... which turns into ...
 * 
 *     type <literal>`bar`</literal>
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author John Gruber <http://daringfireball.net/projects/markdown/>
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Literal.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_Literal extends Solar_Markdown_Plugin_CodeSpan
{
    /**
     * 
     * Support callback for literal spans.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $c = $matches[2];
        $c = preg_replace('/^[ \t]*/', '', $c); // leading whitespace
        $c = preg_replace('/[ \t]*$/', '', $c); // trailing whitespace
        $c = $this->_escape($c);
        $c = "<literal>$c</literal>";
        return $this->_toHtmlToken($c);
    }
}
