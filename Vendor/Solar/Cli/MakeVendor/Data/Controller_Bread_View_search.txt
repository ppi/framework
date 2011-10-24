<h2><?php echo $this->escape(ucwords($this->controller)); ?></h2>
<h3><?php echo $this->getText('HEADING_SEARCH'); ?></h3>

<?php
    // show the search form
    echo $this->form()
              ->auto($this->form)
              ->addProcess('search')
              ->decorateAsPlain()
              ->fetch();
    
    // show the results list
    if (! $this->list) {
        echo $this->getText('ERR_NO_RECORDS');
    } else {
        $pager = $this->pager($this->list->getPagerInfo());
        echo $pager . "<br />";
        echo $this->partial('_list', $this->list);
        echo $pager . "<br />";
    }
?>
