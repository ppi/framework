<?php
/**
 * 
 * Front-controller class to find and invoke a page-controller.
 * 
 * An example bootstrap "index.php" for your web root using the front
 * controller ...
 * 
 * {{code: php
 *     require 'Solar.php';
 *     Solar::start();
 *     $front = Solar::factory('Solar_Controller_Front');
 *     $front->display();
 *     Solar::stop();
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Front.php 4502 2010-03-08 16:20:08Z pmjones $
 * 
 */
class Solar_Controller_Front extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config array classes Base class names for page controllers.
     * 
     * @config array disable A list of app names that should be disallowed
     * and treated as "not found" if a URI maps to them.
     * 
     * @config string default The default page-name.
     * 
     * @config array routing Key-value pairs explicitly mapping a page-name to
     * a controller class (static mapping).
     * 
     * @config array rewrite Rewrite URIs according to these rules (dynamic
     * mapping).
     * 
     * @config array replace Replacement strings for rewrite rules.
     * 
     * @config bool explain Dump an explanation of the routing path when a
     * page is not found.
     * 
     * @var array
     * 
     */
    protected $_Solar_Controller_Front = array(
        'classes' => array('Solar_App'),
        'disable' => array(),
        'default' => null,
        'routing' => array(),
        'rewrite' => array(),
        'replace' => array(),
        'explain' => false,
    );
    
    /**
     * 
     * The default page name when none is specified.
     * 
     * @var array
     * 
     */
    protected $_default;
    
    /**
     * 
     * A list of page-controller names that should be disallowed; will be
     * treated as actions on the default controller instead.
     * 
     * @var array
     * 
     */
    protected $_disable = array();
    
    /**
     * 
     * The registered Solar_Uri_Rewrite object.
     * 
     * @var Solar_Uri_Rewrite
     * 
     * @see _rewrite()
     * 
     */
    protected $_rewrite = array();
    
    /**
     * 
     * Explicit page-name to controller class mappings.
     * 
     * @var array
     * 
     */
    protected $_routing = array();
    
    /**
     * 
     * The page-name key matched to the routing map, if any.
     * 
     * @var array
     * 
     * @see _getPageClass()
     * 
     */
    protected $_routing_key;
    
    /**
     * 
     * Stack of page-controller class prefixes.
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_stack;
    
    /**
     * 
     * An array of explanations of the front-controller processing track.
     * 
     * @var array
     * 
     */
    protected $_explain;
    
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
        
        // set string var from config
        if ($this->_config['default']) {
            $this->_default = (string) $this->_config['default'];
        }
        
        // merge array vars from config
        $vars = array('disable', 'routing');
        foreach ($vars as $key) {
            if ($this->_config[$key]) {
                $var = "_$key";
                $this->$var = array_merge(
                    $this->$var,
                    (array) $this->_config[$key]
                );
            }
        }
        
        // set up a class stack for finding apps
        $this->_stack = Solar::factory('Solar_Class_Stack');
        $this->_stack->add($this->_config['classes']);
        
        // retain the registered rewriter
        $this->_rewrite = Solar_Registry::get('rewrite');
        
        // merge our rewrite rules
        if ($this->_config['rewrite']) {
            $this->_rewrite->mergeRules($this->_config['rewrite']);
        }
        
        // merge our rewrite replacement tokens
        if ($this->_config['replace']) {
            $this->_rewrite->mergeReplacements($this->_config['replace']);
        }
        
        // extended setup
        $this->_setup();
    }
    
    /**
     * 
     * Sets up the environment for all pages.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    /**
     * 
     * Fetches the output of a page/action/info specification URI.
     * 
     * @param Solar_Uri_Action|string $spec An action URI for the front
     * controller.  E.g., 'bookmarks/user/pmjones/php+blog?page=2'. When
     * empty (the default) uses the current URI.
     * 
     * @return string The output of the page action.
     * 
     */
    public function fetch($spec = null)
    {
        if ($spec instanceof Solar_Uri_Action) {
            // a uri object was passed directly
            $uri = $spec;
        } else {
            // spec is a uri string; if empty, uses the current uri
            $uri = Solar::factory('Solar_Uri_Action', array(
                'uri' => (string) $spec,
            ));
        }
        
        $this->_explain['fetch_uri'] = $uri->getFrontPath();
        
        // rewrite the uri path
        $this->_rewrite($uri);
        
        // get the page and class routing
        list($page, $class) = $this->_routing($uri);
        
        // do we have a page-controller class?
        if (! $class) {
            return $this->_notFound($page);
        }
        
        // instantiate the controller class and fetch its content
        $obj = Solar::factory($class);
        $obj->setFrontController($this);
        
        // was this the result of a static routing? if so, force the
        // page controller to use the route name.
        if ($this->_routing_key) {
            $obj->setController($this->_routing_key);
        }
        
        // done, fetch the page-controller results
        return $obj->fetch($uri);
    }
    
    /**
     * 
     * Displays the output of an page/action/info specification URI.
     * 
     * @param string $spec A page/action/info spec for the front
     * controller. E.g., 'bookmarks/user/pmjones/php+blog?page=2'.
     * 
     * @return string The output of the page action.
     * 
     */
    public function display($spec = null)
    {
        echo $this->fetch($spec);
    }
    
    /**
     * 
     * Rewrite the URI path according to a dynamic ruleset.
     * 
     * @param Solar_Uri $uri The URI to rewrite in place.
     * 
     * @return void
     * 
     */
    protected function _rewrite($uri)
    {
        // does it match a rewrite rule?
        $newpath = $this->_rewrite->match($uri);
        if ($newpath) {
            $uri->setPath($newpath);
            $this->_explain['rewrite_rule'] = $this->_rewrite->explain();
            $this->_explain['rewrite_uri'] = $uri->getFrontPath();
        } else {
            $this->_explain['rewrite_rule'] = $this->_rewrite->explain();
        }
    }
    
    /**
     * 
     * Checks the URI to see what page name and controller class we should
     * route to.
     * 
     * @param Solar_Uri $uri The URI to route on.
     * 
     * @return void
     * 
     */
    protected function _routing($uri)
    {
        // first path-element is the page-name.
        $page = strtolower(reset($uri->path));
        if (empty($page)) {
            // page-name is blank. get the default class.
            // remove the empty element from the path.
            $class = $this->_getPageClass($this->_default);
            array_shift($uri->path);
            $this->_explain['routing_page']  = "empty, using default page '{$this->_default}'";
        } elseif (in_array($page, $this->_disable)) {
            // page-name is disabled. get the default class.
            // leave existing elements in the path.
            $class = $this->_getPageClass($this->_default);
            $this->_explain['routing_page'] = "'$page' disabled, using default page '{$this->_default}'";
        } else {
            // look for a controller for the requested page.
            $class = $this->_getPageClass($page);
            if (! $class) {
                // no class for the page-name. get the default class.
                // leave existing elements in the path.
                $class = $this->_getPageClass($this->_default);
                $this->_explain['routing_page'] = "no class for page '$page', using default page '{$this->_default}'";
            } else {
                // found a class. don't need the page-name any more, so
                // remove it from the path.
                array_shift($uri->path);
                $this->_explain['routing_page'] = $page;
            }
        }
        
        $this->_explain['routing_class'] = $class;
        $this->_explain['routing_uri']   = $uri->getFrontPath();
        return array($page, $class);
    }
    
    /**
     * 
     * Finds the page-controller class name from a page name.
     * 
     * @param string $page The page name.
     * 
     * @return string The related page-controller class picked from
     * the routing, or from the list of available classes.  If not found,
     * returns false.
     * 
     */
    protected function _getPageClass($page)
    {
        if (! empty($this->_routing[$page])) {
            // found an explicit route
            $this->_routing_key = $page;
            $class = $this->_routing[$page];
        } else {
            // no explicit route
            $this->_routing_key = null;
            
            // try to find a matching class
            $page = str_replace('-',' ', strtolower($page));
            $page = str_replace(' ', '', ucwords(trim($page)));
            $class = $this->_stack->load($page, false);
        }
        return $class;
    }
    
    /**
     * 
     * Executes when fetch() cannot find a related page-controller class.
     * 
     * Generates an "HTTP 1.1/404 Not Found" status header and returns a
     * short HTML page describing the error.
     * 
     * @param string $page The name of the page not found.
     * 
     * @return string
     * 
     */
    protected function _notFound($page)
    {
        $content[] = "<html><head><title>Not Found</title></head><body>";
        $content[] = "<h1>404 Not Found</h1>";
        $content[] = "<p>"
                   . htmlspecialchars("Page controller class for '$page' not found.")
                   . "</p>";
        
        if ($this->_config['explain']) {
            $content[] = "<h2>Track</h2>";
            $content[] = "<dl>";
            foreach ($this->_explain as $code => $text) {
                $content[] = "<dt><code>{$code}:</code></dt>";
                $content[] = "<dd><code>"
                           . ($text ? htmlspecialchars($text) : "<em>empty</em>")
                           . "</code></dd>";
            }
            $content[] = "</dl>";
            
            $content[] = "<h2>Page Class Prefixes</h2>";
            $content[] = '<ol>';
            foreach ($this->_stack->get() as $class) {
                $content[] = "<li>$class*</li>";
            }
            $content[] = '</ol>';
        }
        
        $content[] = "</body></html>";
        
        $response = Solar_Registry::get('response');
        $response->setStatusCode(404);
        $response->content = implode("\n", $content);
        
        return $response;
    }
}