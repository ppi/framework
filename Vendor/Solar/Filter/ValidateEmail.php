<?php
/**
 * 
 * Validates that a value is an email address.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateEmail.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
class Solar_Filter_ValidateEmail extends Solar_Filter_Abstract
{
    /**
     * 
     * The email validation regex.
     * 
     * @var string
     * 
     */
    protected $_expr = null;
    
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
        
        $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
        
        $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
        
        $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'
              . '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
        
        $quoted_pair = '\\x5c[\\x00-\\x7f]';
        
        $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
        
        $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
        
        $domain_ref = $atom;
        
        $sub_domain = "($domain_ref|$domain_literal)";
        
        $word = "($atom|$quoted_string)";
        
        $domain = "$sub_domain(\\x2e$sub_domain)+";
        
        $local_part = "$word(\\x2e$word)*";
        
        $this->_expr = "$local_part\\x40$domain";
    }
    
    /**
     * 
     * Validates that the value is an email address.
     * 
     * Taken directly from <http://www.iamcal.com/publish/articles/php/parsing_email/>.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateEmail($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return (bool) preg_match("!^{$this->_expr}$!D", $value);
    }
}