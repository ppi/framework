/**
 * 
 * Abstract adapter class test.
 * 
 */
abstract class Test_{:class} extends {:extends} {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_{:class} = array(
    );
    
    /**
     * 
     * The adapter class to instantiate.
     * 
     * @var array
     * 
     */
    protected $_adapter_class;
    
    /**
     * 
     * The adapter instance.
     * 
     * @var array
     * 
     */
    protected $_adapter;
    
    /**
     * 
     * Sets $_adapter_class based on the test class name.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // Test_Vendor_Foo => Vendor_Foo
        $this->_adapter_class = substr(get_class($this), 5);
    }
    
    /**
     * 
     * Creates an adapter instance.
     * 
     * @return void
     * 
     */
    public function preTest()
    {
        parent::preTest();
        $this->_adapter = Solar::factory(
            $this->_adapter_class,
            $this->_config
        );
    }
    
    /**
     * 
     * Test -- Constructor.
     * 
     */
    public function test__construct()
    {
        $this->assertInstance($this->_adapter, $this->_adapter_class);
    }
}
