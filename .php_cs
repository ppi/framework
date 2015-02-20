<?php

use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\FixerInterface;

$finder = DefaultFinder::create()
    ->notPath('src/Debug/ExceptionHandler.php')
    ->in(array('src', 'tests'));

$config = Config::create()
    ->level(FixerInterface::PSR2_LEVEL)
    ->fixers(
        array(
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
            'no_empty_lines_after_phpdocs',
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
        ))
    ->finder($finder)
    ->setUsingCache(true);

return $config;
