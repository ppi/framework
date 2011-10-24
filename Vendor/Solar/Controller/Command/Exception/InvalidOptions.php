<?php
/**
 * 
 * Command-controller exception for when there is one or more invalid option.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: InvalidOptions.php 4376 2010-02-11 23:13:07Z pmjones $
 * 
 */
class Solar_Controller_Command_Exception_InvalidOptions extends Solar_Controller_Command_Exception
{
    /**
     * 
     * Get a message listing the invalid options.
     * 
     * @return string
     * 
     */
    public function getMessageInvalid()
    {
        $text = parent::getMessage();
        $info = $this->getInfo();
        $invalid = $info['invalid'];
        $options = $info['options'];
        
        foreach ($invalid as $name => $list) {
            
            $opt   = $options[$name];
            $long  = ($opt['long'])  ? "--{$opt['long']}" : '';
            $short = ($opt['short']) ? "-{$opt['short']}" : '';
            
            if ($long && $short) {
                $label = "$long | $short";
            } else {
                $label = $long . $short;
            }
                   
            foreach ($list as $value) {
                $text .= PHP_EOL . "$label: $value";
            }
        }
        return $text;
    }
}