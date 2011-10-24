<?php
/**
 * 
 * Locale file.  Returns the strings for a specific language.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: en_US.php 4432 2010-02-25 14:27:20Z pmjones $
 * 
 */
return array(
    
    // general purpose
    'ERR_QUERY_FAILED'          => 'Query failed: ({:pdo_code}) {:pdo_text}',
    'ERR_NO_COLS_FOUND'         => 'No columns found for table {:table} in schema {:schema}.',
    'ERR_NOT_ENOUGH_VALUES'     => 'Not enough data values for placeholders in "{:text}".',
    'ERR_PREPARE_FAILED'        => 'Could not prepare statement: ({:pdo_code}) {:pdo_text}',
    
    // portable column creation
    'ERR_COL_TYPE'   => 'Column "{:col}" type "{:type}"  is not a portable column type.',
    'ERR_COL_SIZE'   => 'Column "{:col}" size of "{:size}" not valid.',
    'ERR_COL_SCOPE'  => 'Column "{:col}" scope of "{:scope}" not valid for size "{:size}".',
    
    // table creation
    'ERR_TABLE_NOT_CREATED'     => 'Table {:table} not created: {:error}',
    
    // identifiers
    'ERR_IDENTIFIER_CHARS' => '{:type} name {:name} has length {:len} for part {:part}; min={:min}, max={:max}.',
    'ERR_IDENTIFIER_LENGTH' => '{:type} name {:name} part {:part} should match regex {:regex}.',
    'ERR_IDENTIFIER_UNDERSCORES' => '{:type} name {:name} has two or more underscores in a row.',
);