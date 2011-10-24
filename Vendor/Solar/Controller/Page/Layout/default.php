<?php
/**
 * 
 * Generic page layout.
 * 
 * @category Solar
 * 
 * @package Solar_Controller_Page
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: default.php 3995 2009-09-08 18:49:24Z pmjones $
 * 
 */
?>
<html>
    
    <head>
        <?php echo $this->head()->fetch(); ?>
    </head>
    
    <body>
        <?php echo $this->layout_content; ?>
    </body>
    
</html>