<?php
/**
 * 
 * Acts as a central catalog for model instances; reduces the number of times
 * you instantiate model classes.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Jeff Moore <jeff@procata.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Catalog.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
class Solar_Sql_Model_Catalog extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config array classes Use these prefixes for the class stack that
     * loads model classes.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Model_Catalog = array(
        'classes' => null,
    );
    
    /**
     * 
     * Inflection dependency.
     * 
     * @var Solar_Inflect
     * 
     */
    protected $_inflect;
    
    /**
     * 
     * Class stack for finding models.
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_stack;
    
    /**
     * 
     * An array of instantiated model objects keyed by class name.
     * 
     * @var array
     * 
     */
    protected $_store = array();
    
    /**
     * 
     * A mapping of model names to model classes.
     * 
     * @var array
     * 
     */
    protected $_name_class = array();
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // inflection dependency
        $this->_inflect = Solar::dependency(
            'Solar_Inflect',
            'inflect'
        );
        
        // model stack
        $this->_setStack($this->_config['classes']);
    }
    
    /**
     * 
     * Magic get to make it look like model names are object properties.
     * 
     * @param string $key The model name to retrieve.
     * 
     * @return Solar_Sql_Model The model object.
     * 
     */
    public function __get($key)
    {
        return $this->getModel($key);
    }
    
    /**
     * 
     * Frees memory for all models in the catalog.
     * 
     * @return void
     * 
     */
    public function free()
    {
        foreach ($this->_store as $class => $model) {
            $model->free();
        }
    }
    
    /**
     * 
     * Gets the model class for a particular model name.
     * 
     * @param string $name The model name.
     * 
     * @return string The model class.
     * 
     */
    public function getClass($name)
    {
        $name = $this->_inflect->underToStudly($name);
        
        if (empty($this->_name_class[$name])) {
            $class = $this->_stack->load($name);
            $this->_name_class[$name] = $class;
        }
        
        return $this->_name_class[$name];
    }
    
    /**
     * 
     * Returns a stored model instance by name, creating it if needed.
     * 
     * @param string $name The model name.
     * 
     * @return Solar_Sql_Model A model instance.
     * 
     */
    public function getModel($name)
    {
        $class = $this->getClass($name);
        return $this->getModelByClass($class);
    }
    
    /**
     * 
     * Returns a stored model instance by class, creating it if needed.
     * 
     * @param string $class The model class.
     * 
     * @return Solar_Sql_Model A model instance.
     * 
     */
    public function getModelByClass($class)
    {
        if (empty($this->_store[$class])) {
            $this->_store[$class] = $this->_newModel($class);
        }
        
        return $this->_store[$class];
    }
    
    /**
     * 
     * Sets a model name to be a specific instance or class.
     * 
     * Generally, you only need this when you want to bring in a single model
     * from outside the expected stack.
     * 
     * @param string $name The model name to use.
     * 
     * @param string|Solar_Sql_Model $spec If a model object, use directly;
     * otherwise, assume it's a string class name and create a new model using
     * that.
     * 
     * @return void
     * 
     */
    public function setModel($name, $spec)
    {
        if (! empty($this->_name_class[$name])) {
            throw $this->_exception('ERR_NAME_EXISTS', array(
                'name' => $name,
            ));
        }
        
        // instance, or new model?
        if ($spec instanceof Solar_Sql_Model) {
            $model = $spec;
            $class = get_class($model);
        } else {
            $class = $spec;
            $model = $this->_newModel($class);
        }
        
        // retain the name-to-class mapping and the model itself
        $this->_name_class[$name] = $class;
        $this->_store[$class] = $model;
    }
    
    /**
     * 
     * Loads a model from the stack into the catalog by name, returning a 
     * true/false success indicator (instead of throwing an exception when
     * the class cannot be found).
     * 
     * @param string $name The model name to load from the stack into the
     * catalog.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function loadModel($name)
    {
        try {
            $class = $this->getClass($name);
        } catch (Solar_Class_Stack_Exception_ClassNotFound $e) {
            return false;
        }
        
        // retain the model internally
        $this->getModelByClass($class);
        
        // success
        return true;
    }
    
    /**
     * 
     * Returns a new model instance (not stored).
     * 
     * @param string $name The model name.
     * 
     * @return Solar_Sql_Model A model instance.
     * 
     */
    public function newModel($name)
    {
        $class = $this->getClass($name);
        return $this->_newModel($class);
    }
    
    /**
     * 
     * Returns information about the catalog as an array with keys for 'names'
     * (the model name-to-class mappings), 'store' (the classes actually
     * loaded up and retained), and 'stack' (the search stack for models).
     * 
     * @return array
     * 
     */
    public function getInfo()
    {
        return array(
            'names' => $this->_name_class,
            'store' => array_keys($this->_store),
            'stack' => $this->_stack->get(),
        );
    }
    
    /**
     * 
     * Sets the model stack.
     * 
     * @param array $classes An array of class prefixes to use for the model
     * stack.
     * 
     * @return void
     * 
     */
    protected function _setStack($classes)
    {
        if (! $classes) {
            // add per the vendor on this catalog and its inheritance
            $parents = Solar_Class::parents(get_class($this), true);
            array_shift($parents); // Solar_Base
            $old_vendor = false;
            foreach ($parents as $class) {
                $new_vendor = Solar_Class::vendor($class);
                if ($new_vendor != $old_vendor) {
                    $classes[] = "{$new_vendor}_Model";
                }
                $old_vendor = $new_vendor;
            }
        }
        
        // build the class stack
        $this->_stack = Solar::factory('Solar_Class_Stack');
        $this->_stack->add($classes);
    }
    
    /**
     * 
     * Returns a new model instance (not stored).
     * 
     * @param string $class The model class.
     * 
     * @return Solar_Sql_Model A model instance.
     * 
     */
    protected function _newModel($class)
    {
        // instantiate
        $model = Solar::factory($class, array(
            'catalog' => $this,
        ));
        
        // done!
        return $model;
    }
}