<h2><?php echo $this->escape(ucwords($this->controller)); ?></h2>
<h3><?php echo $this->getText('HEADING_EDIT'); ?></h3>

<p>[ <?php echo $this->action(
    "/{$this->controller}/read/{$this->item->getPrimaryVal()}",
    'ACTION_READ');
?> ]</p>

<?php
    $process_group = array('save', 'cancel');
    
    $allowed = $this->user->access->isAllowed(
        $this->controller_class,
        'delete',
        $this->item
    );
    
    if ($allowed) {
        $process_group[] = 'delete';
    }
    
    echo $this->form()
              ->auto($this->form)
              ->addProcessGroup($process_group)
              ->fetch();
?>
