/**
 * 
 * Generic model application for {:model_name}.
 * 
 */
class {:class} extends {:extends}
{
    /**
     * 
     * The main model name.
     * 
     * @var string
     * 
     */
    public $model_name = '{:model_name}';
    
    /**
     * 
     * The record columns to show for the item.
     * 
     * @var array
     * 
     */
    public $item_cols = array();
    
    /**
     * 
     * The record columns to show for the list.
     * 
     * @var array
     * 
     */
    public $list_cols = array();
    
    /**
     * 
     * Use only these columns for the form in the given action, and when 
     * loading record data for that action.
     * 
     * When empty, uses all columns.
     * 
     * The format is `'action' => array('col', 'col', 'col' ...)`.
     * 
     * @var array
     * 
     */
    protected $_form_cols = array(
        'add'  => array(),
        'edit' => array(),
    );
    
    /**
     * 
     * The columns to use for searches.
     * 
     * @var array
     * 
     */
    protected $_search_cols = array();

}
