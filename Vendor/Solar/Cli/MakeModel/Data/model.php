/**
 * 
 * Model class.
 * 
 */
class {:class} extends {:extends}
{
    /**
     * 
     * Establish state of this object prior to _setup().
     * 
     * @return void
     * 
     */
    protected function _preSetup()
    {
        // chain to parent
        parent::_preSetup();
        
        // use metadata generated from make-model
        $metadata          = Solar::factory('{:class}_Metadata');
        $this->_table_name = $metadata->table_name;
        $this->_table_cols = $metadata->table_cols;
        $this->_index_info = $metadata->index_info;
    }
    
    /**
     * 
     * Model-specific setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // chain to parent
        parent::_setup();
    }
}
