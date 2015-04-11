<?php

use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\FixerInterface;

/*
 * List of available fixers:
 *
 * "psr0" [PSR-0]
 * Classes must be in a path that matches their namespace, be at least one namespace deep, and the class name should
 * match the file name.
 *
 * "encoding" [PSR-1]
 * PHP code MUST use only UTF-8 without BOM (remove BOM).
 *
 * "short_tag" [PSR-1]
 * PHP code must use the long <?php ?> tags or the short-echo <?= ?> tags; it must not use the other tag variations.
 *
 * "braces" [PSR-2]
 * The body of each structure MUST be enclosed by braces. Braces should be properly placed. Body of braces should be
 * properly indented.
 *
 * "elseif" [PSR-2]
 * The keyword elseif should be used instead of else if so that all control keywords looks like single words.
 *
 * "eof_ending" [PSR-2]
 * A file must always end with an empty line feed.
 *
 * "function_call_space" [PSR-2]
 * When making a method or function call, there MUST NOT be a space between the method or function name and the opening
 * parenthesis.
 *
 * "function_declaration" [PSR-2]
 * Spaces should be properly placed in a function declaration.
 *
 * "indentation" [PSR-2]
 * Code MUST use an indent of 4 spaces, and MUST NOT use tabs for indenting.
 *
 * "line_after_namespace" [PSR-2]
 * There MUST be one blank line after the namespace declaration.
 *
 * "linefeed" [PSR-2]
 * All PHP files must use the Unix LF (linefeed) line ending.
 *
 * "lowercase_constants" [PSR-2]
 * The PHP constants true, false, and null MUST be in lower case.
 *
 * "lowercase_keywords" [PSR-2]
 * PHP keywords MUST be in lower case.
 *
 * "method_argument_space" [PSR-2]
 * In method arguments and method call, there MUST NOT be a space before each comma and there MUST be one space after
 * each comma.
 *
 * "multiple_use" [PSR-2]
 * There MUST be one use keyword per declaration.
 *
 * "parenthesis" [PSR-2]
 * There MUST NOT be a space after the opening parenthesis. There MUST NOT be a space before the closing parenthesis.
 *
 * "php_closing_tag" [PSR-2]
 * The closing ?> tag MUST be omitted from files containing only PHP.
 *
 * "single_line_after_imports" [PSR-2] Each namespace use MUST go on its own line and there MUST be one blank line after
 * the use statements block.
 *
 * "trailing_spaces" [PSR-2]
 * Remove trailing whitespace at the end of non-blank lines.
 *
 * "visibility" [PSR-2]
 * Visibility MUST be declared on all properties and methods; abstract and final MUST be declared before the visibility;
 * static MUST be declared after the visibility.
 *
 * "concat_without_spaces" [symfony]
 * Concatenation should be used without spaces.
 *
 * "double_arrow_multiline_whitespaces" [symfony]
 * Operator => should not be surrounded by multi-line whitespaces.
 *
 * "duplicate_semicolon" [symfony]
 * Remove duplicated semicolons.
 *
 * "empty_return" [symfony]
 * A return statement wishing to return nothing should be simply "return".
 *
 * "extra_empty_lines" [symfony]
 * Removes extra empty lines.
 *
 * "include" [symfony]
 * Include and file path should be divided with a single space. File path should not be placed under brackets.
 *
 * "join_function" [symfony]
 * Implode function should be used instead of join function.
 *
 * "multiline_array_trailing_comma" [symfony]
 * PHP multi-line arrays should have a trailing comma.
 *
 * "namespace_no_leading_whitespace" [symfony]
 * The namespace declaration line shouldn't contain leading whitespace.
 *
 * "new_with_braces" [symfony]
 * All instances created with new keyword must be followed by braces.
 *
 * "no_blank_lines_after_class_opening" [symfony]
 * There should be no empty lines after class opening brace.
 *
 * "no_empty_lines_after_phpdocs" [symfony]
 * There should not be blank lines between docblock and the documented element.
 *
 * "object_operator" [symfony]
 * There should not be space before or after object T_OBJECT_OPERATOR.
 *
 * "operators_spaces" [symfony]
 * Operators should be surrounded by at least one space.
 *
 * "phpdoc_indent" [symfony]
 * Docblocks should have the same indentation as the documented subject.
 *
 * "phpdoc_no_empty_return" [symfony]
 * @return void and @return null annotations should be omitted from phpdocs.
 *
 * "phpdoc_no_package" [symfony]
 * @package and @subpackage annotations should be omitted from phpdocs.
 *
 * "phpdoc_params" [symfony]
 * All items of the @param, @throws, @return, @var, and @type phpdoc tags must be aligned vertically.
 *
 * "phpdoc_separation" [symfony]
 * Annotations in phpdocs should be grouped together so that annotations of the same type immediately follow each other,
 * and annotations of a different type are separated by a single blank line.
 *
 * "phpdoc_short_description" [symfony]
 * Phpdocs short descriptions should end in either a full stop, exclamation mark, or question mark.
 *
 * "phpdoc_to_comment" [symfony]
 * Docblocks should only be used on structural elements.
 *
 * "phpdoc_trim" [symfony]
 * Phpdocs should start and end with content, excluding the very fist and last line of the docblocks.
 *
 * "phpdoc_type_to_var" [symfony]
 * @type should always be written as @var.
 *
 * "phpdoc_var_without_name" [symfony]
 * @var and @type annotations should not contain the variable name.
 *
 * "remove_leading_slash_use" [symfony]
 * Remove leading slashes in use clauses.
 *
 * "remove_lines_between_uses" [symfony]
 * Removes line breaks between use statements.
 *
 * "return" [symfony]
 * An empty line feed should precede a return statement.
 *
 * "single_array_no_trailing_comma" [symfony]
 * PHP single-line arrays should not have trailing comma.
 *
 * "single_blank_line_before_namespace" [symfony]
 * There should be exactly one blank line before a namespace declaration.
 *
 * "spaces_before_semicolon" [symfony]
 * Single-line whitespace before closing semicolon are prohibited.
 *
 * "spaces_cast" [symfony]
 * A single space should be between cast and variable.
 *
 * "standardize_not_equal" [symfony]
 * Replace all <> with !=.
 *
 * "ternary_spaces" [symfony]
 * Standardize spaces around ternary operator.
 *
 * "unused_use" [symfony]
 * Unused use statements must be removed.
 *
 * "whitespacy_lines" [symfony]
 * Remove trailing whitespace at the end of blank lines.
 *
 * "align_double_arrow" [contrib]
 * Align double arrow symbols in consecutive lines.
 *
 * "align_equals" [contrib]
 * Align equals symbols in consecutive lines.
 *
 * "concat_with_spaces" [contrib]
 * Concatenation should be used with at least one whitespace around.
 *
 * "ereg_to_preg" [contrib]
 * Replace deprecated ereg regular expression functions with preg. Warning! This could change code behavior.
 *
 * "header_comment" [contrib]
 * Add, replace or remove header comment.
 *
 * "multiline_spaces_before_semicolon" [contrib]
 * Multi-line whitespace before closing semicolon are prohibited.
 *
 * "no_blank_lines_before_namespace" [contrib]
 * There should be no blank lines before a namespace declaration.
 *
 * "ordered_use" [contrib]
 * Ordering use statements.
 *
 * "php4_constructor" [contrib]
 * Convert PHP4-style constructors to __construct. Warning! This could change code behavior.
 *
 * "phpdoc_order" [contrib]
 * Annotations in phpdocs should be ordered so that param annotations come first, then throws annotations, then
 * return annotations.
 *
 * "phpdoc_var_to_type" [contrib]
 * @var should always be written as @type.
 *
 * "short_array_syntax" [contrib]
 * PHP array's should use the PHP 5.4 short-syntax.
 *
 * "strict" [contrib]
 * Comparison should be strict. Warning! This could change code behavior.
 *
 * "strict_param" [contrib]
 * Functions should be used with $strict param. Warning! This could change code behavior.
 */

// Files and directories that will be scanned
$finder = DefaultFinder::create()
    ->exclude([
        'vendor',
    ])
    ->in([
        'src',
        'tests',
    ]);

// CS issues to fix
$config = Config::create()
    ->level(FixerInterface::PSR2_LEVEL)
    ->fixers(
        [
            'align_double_arrow',
            'align_equals',
            'concat_with_spaces',
            'double_arrow_multiline_whitespaces',
            'duplicate_semicolon',
            'empty_return',
            'extra_empty_lines',
            'include',
            'join_function',
            'multiline_array_trailing_comma',
            'multiline_spaces_before_semicolon',
            'namespace_no_leading_whitespace',
            'new_with_braces',
            'no_blank_lines_after_class_opening',
            'object_operator',
            'operators_spaces',
            'ordered_use',
            'phpdoc_indent',
            'phpdoc_no_empty_return',
            'phpdoc_no_package',
            'phpdoc_order',
            'phpdoc_params',
            'phpdoc_separation',
            'phpdoc_short_description',
            'phpdoc_to_comment',
            'phpdoc_trim',
            'phpdoc_type_to_var',
            'phpdoc_var_without_name',
            'remove_leading_slash_use',
            'remove_lines_between_uses',
            'return',
            'single_array_no_trailing_comma',
            'single_blank_line_before_namespace',
            'spaces_before_semicolon',
            'spaces_cast',
            'standardize_not_equal',
            'ternary_spaces',
            'trailing_spaces',
            'unused_use',
            'whitespacy_lines',
        ])
    ->finder($finder)
    ->setUsingCache(true);

return $config;
