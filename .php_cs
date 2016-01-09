<?php

use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\FixerInterface;

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
            'array_element_no_space_before_comma',
            'array_element_white_space_after_comma',
            'blankline_after_open_tag',
            'concat_with_spaces',
            'double_arrow_multiline_whitespaces',
            'duplicate_semicolon',
            'empty_return',
            'extra_empty_lines',
            'include',
            'join_function',
            'list_commas',
            'method_argument_default_value',
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
            'phpdoc_scalar',
            'phpdoc_separation',
            'phpdoc_short_description',
            'phpdoc_to_comment',
            'phpdoc_trim',
            'phpdoc_type_to_var',
            'phpdoc_types',
            'phpdoc_var_without_name',
            'print_to_echo',
            'remove_leading_slash_use',
            'remove_lines_between_uses',
            'return',
            'self_accessor',
            // 'short_array_syntax',
            'short_bool_cast',
            'single_array_no_trailing_comma',
            'single_blank_line_before_namespace',
            // 'single_quote',
            'spaces_before_semicolon',
            'spaces_cast',
            'standardize_not_equal',
            'ternary_spaces',
            'trim_array_spaces',
            'trailing_spaces',
            'unneeded_control_parentheses',
            'unused_use',
            'whitespacy_lines',
        ]
    )
    ->setUsingLinter(true)
    ->setUsingCache(true)
    ->finder($finder);

return $config;
