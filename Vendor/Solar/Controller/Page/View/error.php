<?php
/**
 * 
 * Template used for generic application errors.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: error.php 3995 2009-09-08 18:49:24Z pmjones $
 * 
 */

$this->head()->setTitle($this->getTextRaw('HEADING_APP_ERROR'));
?>
<h1><?php echo $this->getText('HEADING_APP_ERROR'); ?></h1>

<p><?php echo $this->getText('TEXT_APP_ERROR'); ?></p>

<?php include $this->template('_errors'); ?>
