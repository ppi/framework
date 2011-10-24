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
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: MethodSynopsis.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_MethodSynopsis extends Solar_Markdown_Wiki_MethodSynopsis
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
    protected $_Solar_Markdown_Apidoc_MethodSynopsis = array(
        'synopsis'      => "<methodsynopsis>\n    %access\n    %return\n    %method %params\n    %throws\n</methodsynopsis>",
        'access'        => '<modifier>%access</modifier>',
        'return'        => '<type>%return</type>',
        'method'        => '<methodname>%method</methodname>',
        'param'         => "\n        <methodparam><type>%type</type> <parameter>%name</parameter></methodparam>",
        'param_default' => "\n        <methodparam><type>%type</type> <parameter>%name</parameter> <initializer>%default</initializer></methodparam>",
        'param_void'    => "<void />",
        'throws'        => "\n    <exceptionname>%type</exceptionname>",
        'list_sep'      => '',
    );
}
