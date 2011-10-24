<h2><?php echo $this->escape(ucwords($this->controller)); ?></h2>
<h3><?php echo $this->getText('HEADING_DELETE'); ?></h3>

<?php echo $this->partial('_item', $this->item); ?>

<?php echo $this->form()
                ->addProcess('delete_confirm')
                ->fetch();
?>
