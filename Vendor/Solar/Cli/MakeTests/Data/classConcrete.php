/**
 * 
 * Concrete class test.
 * 
 */
class Test_{:class} extends {:extends} {
    
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
     * Test -- Constructor.
     * 
     */
    public function test__construct()
    {
        $actual = Solar::factory('{:class}');
        $expect = '{:class}';
        $this->assertInstance($actual, $expect);
    }
}
