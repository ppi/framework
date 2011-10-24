<h2><?php echo $this->escape(ucwords($this->controller)); ?></h2>
<h3><?php echo $this->getText('HEADING_BROWSE'); ?></h3>

<?php
    if (! $this->list) {
        echo $this->getText('ERR_NO_RECORDS');
    } else {
        $pager = $this->pager($this->list->getPagerInfo());
        echo $pager . "<br />";
        
        echo $this->partial('_list', $this->list);
        
        echo $pager . "<br />";
    }
?>

<?php
    $allowed = $this->user->access->isAllowed(
        $this->controller_class,
        'add'
    );
    
    if ($allowed) {
        $action = $this->action("/{$this->controller}/add", 'ACTION_ADD');
        echo "<p>$action</p>";
    }
?>
