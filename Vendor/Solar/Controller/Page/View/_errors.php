<?php
/**
 * 
 * Partial template to show $this->errors as a list.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: _errors.php 3995 2009-09-08 18:49:24Z pmjones $
 * 
 */
?>
<div class="error">
    <?php if (! $this->errors): ?>
        <p><?php echo $this->getText('TEXT_NO_ERRORS'); ?></p>
    <?php else: ?>
        <ul>
            <?php
                foreach ((array) $this->errors as $err) {
                    echo "<li>";
                    if ($err instanceof Exception) {
                        echo "<pre>";
                        echo $this->escape($err->__toString());
                        echo "</pre>";
                    } else {
                        echo $this->getText($err);
                    }
                    echo "</li>\n";
                }
            ?>
        </ul>
    <?php endif; ?>
</div>
