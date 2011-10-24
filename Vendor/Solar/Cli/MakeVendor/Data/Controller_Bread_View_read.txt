<h2><?php echo $this->escape(ucwords($this->controller)); ?></h2>
<h3><?php echo $this->getText('HEADING_READ'); ?></h3>

<p>[ <?php echo $this->action("/{$this->controller}", 'ACTION_BROWSE');?> ]</p>

<?php echo $this->partial('_item', $this->item); ?>

<?php
    $allowed = $this->user->access->isAllowed(
        $this->controller_class,
        'edit',
        $this->item
    );
    
    if ($allowed) {
        $action = $this->action(
            "/{$this->controller}/edit/{$this->item->getPrimaryVal()}",
            'ACTION_EDIT'
        );
        echo "<p>$action</p>";
    }
?>
