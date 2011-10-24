<?php
/**
 * 
 * Stack for loading classes from user-defined hierarchies.
 * 
 * As you add classes to the stack, they are searched-for first when you 
 * call [[Solar_Class_Stack::load()]].
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Stack.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_Class_Stack extends Solar_Base
{
    /**
     * 
     * The class stack.
     * 
     * @var array
     * 
     */
    protected $_stack = array();
    
    /**
     * 
     * Gets a copy of the current stack.
     * 
     * @return array
     * 
     */
    public function get()
    {
        return $this->_stack;
    }
    
    /**
     * 
     * Adds one or more classes to the stack.
     * 
     * {{code: php
     *     
     *     // add by array
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add(array('Base1', 'Base2', 'Base3'));
     *     // $stack->get() reveals that the class search order will be
     *     // 'Base1_', 'Base2_', 'Base3_'.
     *     
     *     // add by string
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add('Base1, Base2, Base3');
     *     // $stack->get() reveals that the class search order will be
     *     // 'Base1_', 'Base2_', 'Base3_'.
     *     
     *     // add incrementally -- N.B. THIS IS A SPECIAL CASE
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add('Base1');
     *     $stack->add('Base2');
     *     $stack->add('Base3');
     *     // $stack->get() reveals that the directory search order will be
     *     // 'Base3_', 'Base2_', 'Base1_', because the later adds
     *     // override the newer ones.
     * }}
     * 
     * @param array|string $list The classes to add to the stack.
     * 
     * @return void
     * 
     */
    public function add($list)
    {
        if (is_string($list)) {
            $list = explode(',', $list);
        }
        
        if (is_array($list)) {
            $list = array_reverse($list);
        }
        
        foreach ((array) $list as $class) {
            $class = trim($class);
            if (! $class) {
                continue;
            }
            // trim all trailing _, then add just one _,
            // and add to the stack.
            $class = rtrim($class, '_') . '_';
            array_unshift($this->_stack, $class);
        }
    }
    
    /**
     * 
     * Given a class or object, add itself and its parents to the stack, 
     * optionally tracking cross-hierarchy shifts around a base name.
     * 
     * @param string|object $spec The class or object to find parents of.
     * 
     * @param string $base The infix base around which to track cross-
     * hierarchy shifts.
     * 
     * @return void
     * 
     */
    public function addByParents($spec, $base = null)
    {
        // get the list of parents; always skip Solar_Base
        $parents = Solar_Class::parents($spec, true);
        array_shift($parents);
        
        // if not tracking cross-hierarchy shifts, add parents as they are
        if (! $base) {
            $list = array_reverse($parents);
            return $this->add($list);
        }
        
        // track cross-hierarchy shifts in class names. any time we change
        // "*_Base" prefixes, insert "New_Prefix_Base" into the stack.
        $old = null;
        foreach ($parents as $class) {
            
            $pos = strpos($class, "_$base");
            $new = substr($class, 0, $pos);
            
            // check to see if we crossed vendors or hierarchies
            if ($new != $old) {
                $cross = "{$new}_{$base}";
                $this->add($cross);
            } else {
                $cross = null;
            }
            
            // prevent double-adds where the cross-hierarchy class name ends
            // up being the same as the current class name
            if ($cross != $class) {
                // not the same, add the current class name
                $this->add($class);
            }
            
            // retain old prefix for next loop
            $old = $new;
        }
    }
    
    /**
     * 
     * Given a class or object, add its vendor and its parent vendors to the 
     * stack; optionally, add a standard suffix base to the vendor name.
     * 
     * @param string|object $spec The class or object to find vendors of.
     * 
     * @param string $base The suffix base to append to each vendor name.
     * 
     * @return void
     * 
     */
    public function addByVendors($spec, $base = null)
    {
        // get the list of parents; retain Solar_Base
        $parents = Solar_Class::parents($spec, true);
        
        // if we have a suffix, put a separator on it
        if ($base) {
            $base = "_$base";
        }
        
        // look through vendor names
        $old = null;
        foreach ($parents as $class) {
            $new = Solar_Class::vendor($class);
            if ($new != $old) {
                // not the same, add the current vendor name and suffix
                $this->add("{$new}{$base}");
            }
            // retain old vendor for next loop
            $old = $new;
        }
    }
    
    /**
     * 
     * Clears the stack and adds one or more classes.
     * 
     * {{code: php
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add('Base1');
     *     $stack->add('Base2');
     *     $stack->add('Base3');
     *     
     *     // $stack->get() reveals that the directory search order is
     *     // 'Base3_', 'Base2_', 'Base1_'.
     *     
     *     $stack->set('Another_Base');
     *     
     *     // $stack->get() is now array('Another_Base_').
     * }}
     * 
     * @param array|string $list The classes to add to the stack
     * after clearing it.
     * 
     * @return void
     * 
     */
    public function set($list)
    {
        $this->_stack = array();
        return $this->add($list);
    }
    
    /**
     * 
     * Given a class or object, set the stack based on itself and its parents,
     * optionally tracking cross-hierarchy shifts around a base name.
     * 
     * @param string|object $spec The class or object to find parents of.
     * 
     * @param string $base The infix base around which to track cross-
     * hierarchy shifts.
     * 
     * @return void
     * 
     */
    public function setByParents($spec, $base = null)
    {
        $this->_stack = array();
        $this->addByParents($spec, $base);
    }
    
    /**
     * 
     * Given a class or object, set the stack based on its vendor and its
     * parent vendors; optionally, add a standard suffix base to the vendor
     * name.
     * 
     * @param string|object $spec The class or object to find vendors of.
     * 
     * @param string $base The suffix base to add to each vendor name.
     * 
     * @return void
     * 
     */
    public function setByVendors($spec, $base = null)
    {
        $this->_stack = array();
        $this->addByVendors($spec, $base);
    }
    
    /**
     * 
     * Loads a class using the class stack prefixes.
     * 
     * {{code: php
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add('Base1');
     *     $stack->add('Base2');
     *     $stack->add('Base3');
     *     
     *     $class = $stack->load('Name');
     *     // $class is now the first instance of '*_Name' found from the         
     *     // class stack, looking first for 'Base3_Name', then            
     *     // 'Base2_Name', then finally 'Base1_Name'.
     * }}
     * 
     * @param string $name The class to load using the class stack.
     * 
     * @param bool $throw Throw an exception if no matching class is found
     * in the stack (default true).  When false, returns boolean false if no
     * matching class is found.
     * 
     * @return string The full name of the loaded class.
     * 
     * @throws Solar_Exception_ClassNotFound
     * 
     */
    public function load($name, $throw = true)
    {
        // some preliminary checks for valid class names
        if (! $name || $name != trim($name) || ! ctype_alpha($name[0])) {
            if ($throw) {
                throw $this->_exception('ERR_CLASS_NOT_VALID', array(
                    'name'  => $name,
                    'stack' => $this->_stack,
                ));
            } else {
                return false;
            }
        }
        
        // make sure the name is upper-cased, then loop through the stack
        // to find it.
        $name = ucfirst($name);
        foreach ($this->_stack as $prefix) {
            
            // the full class name
            $class = "$prefix$name";
            
            // pre-empt searching.
            // don't use autoload.
            if (class_exists($class, false)) {
                return $class;
            }
            
            // the related file
            $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
            
            // does the file exist?
            if (! Solar_File::exists($file)) {
                continue;
            }
            
            // include it in a limited scope. we don't use Solar_File::load()
            // because we want to avoid exceptions.
            $this->_run($file);
            
            // did the class exist within the file?
            // don't use autoload.
            if (class_exists($class, false)) {
                // yes, we're done
                return $class;
            }
        }
        
        // failed to find the class in the stack
        if ($throw) {
            throw $this->_exception('ERR_CLASS_NOT_FOUND', array(
                'class' => $name,
                'stack' => $this->_stack,
            ));
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Loads the class file in a limited scope.
     * 
     * @param string The file to include.
     * 
     * @return void
     * 
     */
    protected function _run()
    {
        include func_get_arg(0);
    }
}
