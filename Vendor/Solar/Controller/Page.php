<?php
/**
 * 
 * Abstract page-controller class.
 * 
 * Expects a directory structure similar to the following ...
 * 
 *     Vendor/              # your vendor namespace
 *       App/               # subdirectory for page-controllers
 *         Example.php      # an example app
 *         Example/
 *           Layout/        # layout files to override shared layouts
 *             ...
 *           Locale/        # locale files
 *             en_US.php
 *             pt_BR.php
 *           Public/        # public assets
 *             style.css    
 *             script.js    
 *             image.jpg    
 *           View/          # view scripts
 *             _item.php    # partial template
 *             list.php     # full template
 *             edit.php     # another full template
 * 
 * When you call [[Solar_Controller_Front::fetch() | fetch()]], these intercept methods are run in the
 * following order ...
 * 
 * 1. [[Solar_Controller_Page::_load() | ]] to load class properties from the fetch() URI specification
 * 
 * 2. [[Solar_Controller_Page::_preRun() | ]] before the first action
 * 
 * 3. [[Solar_Controller_Page::_preAction() | ]] before each action (including _forward()-ed actions)
 * 
 * 4. ... The action method itself runs here ...
 * 
 * 5. [[Solar_Controller_Page::_postAction() | ]] after each action
 * 
 * 6. [[Solar_Controller_Page::_postRun() | ]] after the last action, and before rendering
 * 
 * 7. [[Solar_Controller_Page::_render() | ]] to render the view and layout;
 *    this in its turn calls [[Solar_Controller_Page::_setViewObject() | ]] 
 *    and [[Solar_Controller_Page::_renderView() | ]] for the view, then
 *    [[Solar_Controller_Page::_setLayoutTemplates() | ]] and 
 *    [[Solar_Controller_Page::_renderLayout() | ]] for the layout.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Page.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
abstract class Solar_Controller_Page extends Solar_Base
{
    /**
     * 
     * The action being requested of (performed by) the controller.
     * 
     * @var string
     * 
     */
    protected $_action = null;
    
    /**
     * 
     * The default controller action.
     * 
     * @var string
     * 
     */
    protected $_action_default = null;
    
    /**
     * 
     * Tells which alternative output formats are supported by which actions.
     * 
     * Array format is 'action' => array('format', 'format', 'format').
     * 
     * If an action is not listed, it will not respond to alternative formats.
     * 
     * Using a '*' as the action key means "all actions allow this format".
     * 
     * For example ...
     * 
     * {{code: php
     *     $_action_format = array(
     *         // multiple formats
     *         'browse' => array('rss', 'atom')
     *         // shorthand for just one format
     *         'read'   => 'atom',
     *         // shorthand for all actions
     *         '*'      => 'xml'
     *     );
     * }}
     * 
     * @var array
     * 
     * @todo Make the action key a little smarter.  Right now, you need to 
     * specify action names as "fooBar", not "actionFooBar" or "foo-bar".
     * Maybe a method "_getActionFormat()" to translate the key to the right
     * format (e.g., 'foo-bar' to "fooBar").
     * 
     */
    protected $_action_format = array();
    
    /**
     * 
     * Session data, including read-once flashes.
     * 
     * @var Solar_Session
     * 
     */
    protected $_session;
    
    /**
     * 
     * Request parameters collected from the URI pathinfo.
     * 
     * @var array
     * 
     */
    protected $_info = array();
    
    /**
     * 
     * The name of the layout to be rendered.
     * 
     * @var string
     * 
     */
    protected $_layout = null;
    
    /**
     * 
     * The default layout to use.
     * 
     * @var string
     * 
     */
    protected $_layout_default = 'default';
    
    /**
     * 
     * The name of the variable where content is placed in the layout.
     * 
     * Default is 'layout_content'.
     * 
     * @var string
     * 
     */
    protected $_layout_var = 'layout_content';
    
    /**
     * 
     * The short-name of this page-controller.
     * 
     * @var string
     * 
     */
    protected $_controller = null;
    
    /**
     * 
     * Request parameters collected from the URI query string.
     * 
     * @var string
     * 
     */
    protected $_query = array();
    
    /**
     * 
     * Name of the form element that holds the process request value (such as
     * 'Save', 'Next', 'Cancel', etc)
     * 
     * Default is 'process', as in $_POST['process'].
     * 
     * @var string
     * 
     * @see _isProcess()
     * 
     */
    protected $_process_key = 'process';
    
    /**
     * 
     * The name of the view to be rendered.
     * 
     * @var string
     * 
     */
    protected $_view = null;
    
    /**
     * 
     * Use this output format for views.
     * 
     * For example, say the action is "read". In the default case, the format
     * is empty, so  the _render() method will look for a view named 
     * "read.php". However, if the format is "xml", the _render() method will
     * look for a view named "read.xml.php".
     * 
     * Has no effect on the layout script that _render() looks for.
     * 
     * @var string
     * 
     */
    protected $_format = null;
    
    /**
     * 
     * What is the default output format?
     * 
     * @var string
     * 
     */
    protected $_format_default = null;
    
    /**
     * 
     * Which formats go with which layouts?
     * 
     * Empty/false/null means "no layout", boolean true means "the current or 
     * default layout", and a string means that particular layout.
     * 
     * The default is that XHTML formats use the current layout, and all other
     * explicit formats get no layout.
     * 
     * @var string
     * 
     */
    protected $_format_layout = array(
        'xhtml' => true,
    );
    
    /**
     * 
     * Request environment details: get, post, etc.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;
    
    /**
     * 
     * These helper classes will be added in the middle of the stack, between
     * the Solar_View_Helper final fallbacks and the vendor+app specific 
     * helpers.
     * 
     * @var array
     * 
     */
    protected $_helper_class = array();
    
    /**
     * 
     * The response object with headers and body.
     * 
     * @var Solar_Http_Response
     * 
     */
    protected $_response;
    
    /**
     * 
     * The rewrite rules object.
     * 
     * @var Solar_Uri_Rewrite
     * 
     */
    protected $_rewrite;
    
    /**
     * 
     * The class used for view objects.
     * 
     * @var string
     * 
     */
    protected $_view_class = 'Solar_View';
    
    /**
     * 
     * The object used for rendering views and layouts.
     * 
     * @var Solar_View
     * 
     */
    protected $_view_object;
    
    /**
     * 
     * The front-controller object (if any) that invoked this page-controller.
     * 
     * @var Solar_Controller_Front
     * 
     */
    protected $_front;
    
    /**
     * 
     * Maps format name keys to Content-Type values.
     * 
     * When $this->_format matches one of the keys, the controller will set
     * the matching Content-Type header automatically in the response object.
     * 
     * @var array
     * 
     */
    protected $_format_type = array(
        null        => 'text/html',
        'atom'      => 'application/atom+xml',
        'css'       => 'text/css',
        'htm'       => 'text/html',
        'html'      => 'text/html',
        'js'        => 'text/javascript',
        'json'      => 'application/json',
        'pdf'       => 'application/pdf',
        'ps'        => 'application/postscript',
        'rdf'       => 'application/rdf+xml',
        'rss'       => 'application/rss+xml',
        'rss2'      => 'application/rss+xml',
        'rtf'       => 'application/rtf',
        'text'      => 'text/plain',
        'txt'       => 'text/plain',
        'xhtml'     => 'application/xhtml+xml',
        'xml'       => 'application/xml',
    );
    
    /**
     * 
     * The character set to use when setting the Content-Type header.
     * 
     * @var string
     * 
     */
    protected $_charset = 'utf-8';
    
    /**
     * 
     * An array of application error messages.
     * 
     * @var array
     * 
     */
    protected $_errors = array();
    
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
        
        $class = get_class($this);
        
        // create the session object for this class
        $this->_session = Solar::factory(
            'Solar_Session',
            array('class' => $class)
        );
        
        // get the registered response object
        $this->_response = Solar_Registry::get('response');
        
        // auto-set the name; for example Vendor_App_SomeThing => 'some-thing'
        if (empty($this->_controller)) {
            $pos = strrpos($class, '_');
            $this->_controller = substr($class, $pos + 1);
            $this->_controller = preg_replace(
                '/([a-z])([A-Z])/',
                '$1-$2',
                $this->_controller
            );
            $this->_controller = strtolower($this->_controller);
        }
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
        
        // get the registered rewrite object
        $this->_rewrite = Solar_Registry::get('rewrite');
        
        // extended setup
        $this->_setup();
    }
    
    /**
     * 
     * Try to force users to define what their view variables are.
     * 
     * @param string $key The property name.
     * 
     * @param mixed $val The property value.
     * 
     * @return void
     * 
     */
    public function __set($key, $val)
    {
        throw $this->_exception('ERR_NO_SUCH_PROPERTY', array(
            'class' => get_class($this),
            'property' => $key,
        ));
    }
    
    /**
     * 
     * Try to force users to define what their view variables are.
     * 
     * @param string $key The property name.
     * 
     * @return void
     * 
     */
    public function __get($key)
    {
        throw $this->_exception('ERR_NO_SUCH_PROPERTY', array(
            'class' => get_class($this),
            'property' => $key,
        ));
    }
    
    /**
     * 
     * Injects the front-controller object that invoked this page-controller.
     * 
     * @param Solar_Controller_Front $front The front-controller.
     * 
     * @return void
     * 
     */
    public function setFrontController($front)
    {
        $this->_front = $front;
    }
    
    /**
     * 
     * Sets the name for this page-controller; generally used only by the 
     * front-controller when static routing leads to this page.
     * 
     * @param string $controller The name for this page controller.
     * 
     * @return void
     * 
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
    }
    
    /**
     * 
     * Executes the requested action and returns its output with layout.
     * 
     * If an exception is thrown during the fetch() process, it is caught and
     * sent along to the _exceptionDuringFetch() method, which may generate
     * and return alternative output.
     * 
     * @param string $spec The action specification string, for example,
     * "tags/php+framework" or "user/pmjones/php+framework?page=3"
     * 
     * @return Solar_Http_Response A response object with headers and body 
     * from the action, view, and layout.
     * 
     */
    public function fetch($spec = null)
    {
        try {
            
            // load action, info, and query properties
            $this->_load($spec);
            
            // prerun hook
            $this->_preRun();
            
            // is this a csrf attempt?
            if ($this->_request->isCsrf()) {
                // looks like a forgery
                $this->_csrfAttempt();
            } else {
                // action chain, with pre- and post-action hooks
                $this->_forward($this->_action, $this->_info);
            }
            
            // postrun hook
            $this->_postRun();
            
            // render the view and layout, with pre- and post-render hooks
            $this->_render();
            
            // done, return the response headers, cookies, and body
            return $this->_response;
            
        } catch (Exception $e) {
            
            // an exception was thrown somewhere, attempt to rescue it
            return $this->_exceptionDuringFetch($e);
            
        }
    }
    
    /**
     * 
     * Executes the requested action and displays its output.
     * 
     * @param string $spec The action specification string, for example,
     * "tags/php+framework" or "user/pmjones/php+framework?page=3"
     * 
     * @return void
     * 
     */
    public function display($spec = null)
    {
        $response = $this->fetch($spec);
        $response->display();
    }
    
    /**
     * 
     * Shows application errors.
     * 
     * @return void
     * 
     */
    public function actionError()
    {
    }
    
    /**
     * 
     * Sets the response body based on the view, including layout, with
     * pre- and post-rendering logic.
     * 
     * @return void
     * 
     */
    protected function _render()
    {
        // if no view and no layout, there's nothing to render
        if (! $this->_view && ! $this->_layout) {
            $this->_setContentType();
            return;
        }
        
        $this->_setViewObject();
        $this->_preRender();
        $this->_view_object->assign($this);
        
        if ($this->_view) {
            $this->_renderView();
        }
        
        if ($this->_layout) {
            $this->_setLayoutTemplates();
            $this->_renderLayout();
        }
        
        $this->_setContentType();
        
        $this->_postRender();
    }
    
    /**
     * 
     * Sets $this->_view_object for rendering.
     * 
     * @return void
     * 
     */
    protected function _setViewObject()
    {
        // set up a view object, its template paths, and its helper stacks
        $this->_view_object = Solar::factory($this->_view_class);
        $this->_addViewTemplates();
        $this->_addViewHelpers();
        $this->_fixViewObject();
    }
    
    /**
     * 
     * Sets the locale class for the getText helper, and adds special
     * convenience variables, in $this->_view_object for rendering.
     * 
     * @return void
     * 
     */
    protected function _fixViewObject()
    {
        // set the locale class for the getText helper
        $class = get_class($this);
        $this->_view_object->getHelper('getTextRaw')->setClass($class);
        
        // inject special vars into the view
        $this->_view_object->controller_class = get_class($this);
        $this->_view_object->controller       = $this->_controller;
        $this->_view_object->action           = $this->_action;
        $this->_view_object->layout           = $this->_layout;
        $this->_view_object->errors           = $this->_errors;
    }
    
    /**
     * 
     * Uses $this->_view_object to render the view into $this->_response.
     * 
     * @return void
     * 
     */
    protected function _renderView()
    {
        // set the template name from the view and format
        $tpl = $this->_view
             . ($this->_format ? ".{$this->_format}" : "")
             . ".php";
        
        // fetch the view
        try {
            $this->_response->content = $this->_view_object->fetch($tpl);
        } catch (Solar_View_Exception_TemplateNotFound $e) {
            throw $this->_exception('ERR_VIEW_NOT_FOUND', array(
                'path' => $e->getInfo('path'),
                'name' => $e->getInfo('name'),
            ));
        }
    }
    
    /**
     * 
     * Uses $this->_view_object to render the layout into $this->_response.
     * 
     * @return void
     * 
     */
    protected function _renderLayout()
    {
        // assign the previous output
        $this->_view_object->assign($this->_layout_var, $this->_response->content);
        
        // set the template name from the layout value
        $tpl = $this->_layout . ".php";
        
        // fetch the layout
        try {
            $this->_response->content = $this->_view_object->fetch($tpl);
        } catch (Solar_View_Exception_TemplateNotFound $e) {
            throw $this->_exception('ERR_LAYOUT_NOT_FOUND', array(
                'path' => $e->getInfo('path'),
                'name' => $e->getInfo('name'),
            ));
        }
    }
    
    /**
     * 
     * Sets a Content-Type header in the response based on $this->_format,
     * but only if the response does not already have a Content-Type set.
     * 
     * @return void
     * 
     */
    protected function _setContentType()
    {
        if ($this->_response->getHeader('Content-Type')) {
            return;
        }
        
        // get the current format (the _fixFormat() method will have set the
        // default already, if needed)
        $format = $this->_format;
        
        // do we have a content-type for the format?
        if (! empty($this->_format_type[$format])) {
            
            // yes, retain the content-type
            $val = $this->_format_type[$format];
            
            // add charset if one exists
            if ($this->_charset) {
                $val .= '; charset=' . $this->_charset;
            }
            
            // set the response header for content-type
            $this->_response->setHeader('Content-Type', $val);
        }
    }
    
    /**
     * 
     * Adds to the helper-class stack on a view object.
     * 
     * Automatically sets up a helper-class stack for you, searching
     * for helper classes in this order ...
     * 
     * 1. Vendor_View_Helper_
     * 
     * 2. Solar_View_Helper_
     * 
     * @return void
     * 
     */
    protected function _addViewHelpers()
    {
        // start with requested helper classes
        $stack = $this->_helper_class;
        
        // find vendors, disregarding Solar itself (since Solar_View will
        // add that anyway)
        $vendors = Solar_Class::vendors($this);
        array_shift($vendors);
        
        // add each vendor to the stack in turn
        foreach ($vendors as $vendor) {
            $stack[] = "{$vendor}_View_Helper";
        }
        
        // set the helper classes on the view object
        $this->_view_object->addHelperClass($stack);
    }
    
    /**
     * 
     * Adds template paths to $this->_view_object.
     * 
     * The search-path will be in this order, for a Vendor_App_Example class
     * extended from Vender_Controller_Page ...
     * 
     * 1. Vendor/App/Example/View/
     * 
     * 2. Vendor/Controller/Page/View/
     * 
     * 3. Solar/Controller/Page/View/
     * 
     * @return void
     * 
     */
    protected function _addViewTemplates()
    {
        // get the parents of the current class, including self
        $stack = array_reverse(Solar_Class::parents($this, true));
        
        // remove Solar_Base
        array_pop($stack);
        
        // convert underscores to slashes, and add /View
        foreach ($stack as $key => $val) {
            $stack[$key] = str_replace('_', '/', $val) . '/View';
        }
        
        // done, add the stack
        $this->_view_object->addTemplatePath($stack);
    }
    
    /**
     * 
     * Resets $this->_view_object to use the Layout templates.
     * 
     * This effectively re-uses the Solar_View object from the page
     * (with its helper objects and data) to build the layout.  This
     * helps to transfer JavaScript and other layout data back up to
     * the layout with zero effort.
     * 
     * Automatically sets up a template-path stack for you, searching
     * for layout files (e.g.) in this order ...
     * 
     * 1. Vendor/App/Example/Layout/
     * 
     * 2. Vendor/Controller/Page/Layout/
     * 
     * 3. Solar/Controller/Page/Layout/
     * 
     * @return void
     * 
     */
    protected function _setLayoutTemplates()
    {
        // get the parents of the current class, including self
        $stack = array_reverse(Solar_Class::parents($this, true));
        
        // remove Solar_Base
        array_pop($stack);
        
        // convert underscores to slashes, and add /Layout
        foreach ($stack as $key => $val) {
            $stack[$key] = str_replace('_', '/', $val) . '/Layout';
        }
        
        // done, add the stack
        $this->_view_object->setTemplatePath($stack);
    }
    
    /**
     * 
     * Loads properties from an action specification.
     * 
     * @param string $spec The action specification.
     * 
     * @return void
     * 
     */
    protected function _load($spec)
    {
        $this->_loadInfoQueryFormat($spec);
        $this->_fixAction();
        $this->_fixFormat();
        $this->_fixLayout();
        $this->_fixInfo();
    }
    
    /**
     * 
     * Given an action specification, loads $_info, $_query, and $_format.
     * 
     * @param string $spec The action specification.
     * 
     * @return void
     * 
     */
    protected function _loadInfoQueryFormat($spec)
    {
        // process the action/param.format?query specification
        if (! $spec) {
            
            // no spec, use the current URI
            $uri = Solar::factory('Solar_Uri_Action');
            $this->_info = $uri->path;
            $this->_query = $uri->query;
            $this->_format = $uri->format;
            
        } elseif ($spec instanceof Solar_Uri_Action) {
            
            // pull from a Solar_Uri_Action object
            $this->_info = $spec->path;
            $this->_query = $spec->query;
            $this->_format = $spec->format;
            
        } else {
            
            // a string, assumed to be an action/param.format?query spec.
            $uri = Solar::factory('Solar_Uri_Action');
            $uri->set($spec);
            $this->_info = $uri->path;
            $this->_query = $uri->query;
            $this->_format = $uri->format;
            
        }
        
        // if the first param is the controller name, drop it.
        // needed when no spec is passed and we're using the default URI.
        $shift = ! empty($this->_info[0])
              && $this->_info[0] == $this->_controller;
              
        if ($shift) {
            array_shift($this->_info);
        }
        
        // ignore .php formats
        if (strtolower($this->_format) == 'php') {
            $this->_format = null;
        }
        
        // now find the action from the info.
        // do we have an initial info element as an action method?
        if (empty($this->_info[0])) {
            // use the default action
            $this->_action = $this->_action_default;
        } else {
            // save it and remove from info
            $this->_action = array_shift($this->_info);
        }
    }
    
    /**
     * 
     * Fixes the requested $_action value based on the various properties
     * available.
     * 
     * By default, leaves the $_action value as-is; if the action does not
     * map to a method, _notFound() will be triggered.
     * 
     * @return void
     * 
     */
    protected function _fixAction()
    {
    }
    
    /**
     * 
     * Fixes the requested $_format value based on the various properties
     * available.
     * 
     * @return void
     * 
     */
    protected function _fixFormat()
    {
        // are we asking for a non-default format?
        // the trim() lets us get a string-zero format.
        if (trim($this->_format) === '') {
            // no explicit format requested, use the default format
            $this->_format = $this->_format_default;
            return;
        }
            
        // what formats does the action allow?
        $action_format = $this->_getActionFormat($this->_action);
        
        // does the action support the requested format?
        if (in_array($this->_format, $action_format)) {
            // it does, so we're done
            return;
        }
        
        // action does not support the format.
        // add the format extension back to the last param.
        // that's because it might be an actual file name.
        $val = end($this->_info);
        
        // what's the key on the last param?
        $key = key($this->_info);
        if ($key === null) {
            // array was empty; force to zero
            $key = 0;
        }
        
        // add the info back
        $this->_info[$key] = $val . '.' . $this->_format;
        
        // use the default format
        $this->_format = $this->_format_default;
    }
    
    /**
     * 
     * Fixes the $_layout value based on the various properties available.
     * 
     * @return void
     * 
     */
    protected function _fixLayout()
    {
        // convenience variable
        $format = $this->_format;
        
        // "no format" means "use the default layout"
        if (! $format) {
            $this->_layout = $this->_layout_default;
            return;
        }
        
        // no format => layout mapping means no layout
        if (empty($this->_format_layout[$format])) {
            $this->_layout = null;
            return;
        }
        
        // use the default, or a specific one?
        $layout = $this->_format_layout[$format];
        if ($layout === true) {
            // use the default
            $this->_layout = $this->_layout_default;
        } else {
            // use a specific layout
            $this->_layout = $layout;
        }
    }
    
    /**
     * 
     * Fixes the $_info value based on the various properties available.
     * 
     * Removes empty-string info elements from the end of the array. this
     * happens sometimes with elements being added and removed from format
     * checking, and helps make sure that action default parameters are
     * honored.
     * 
     * @return void
     * 
     */
    protected function _fixInfo()
    {
        $i = count($this->_info);
        while ($i --) {
            // the trim lets us keep literal '0' strings
            if (trim($this->_info[$i]) !== '') {
                // not empty, stop removing blanks
                break;
            } else {
                unset($this->_info[$i]);
            }
        }
    }
    
    /**
     * 
     * Retrieves the TAINTED value of a path-info parameter by position.
     * 
     * Note that this value is direct user input; you should sanitize it
     * with Solar_Filter (or some other technique) before using it.
     * 
     * @param int $key The path-info parameter position.
     * 
     * @param mixed $val If the position does not exist, use this value
     * as a default in its place.
     * 
     * @return mixed The value of that query key.
     * 
     */
    protected function _info($key, $val = null)
    {
        $exists = array_key_exists($key, $this->_info)
               && $this->_info[$key] !== null;
               
        if ($exists) {
            return $this->_info[$key];
        } else {
            return $val;
        }
    }
    
    /**
     * 
     * Retrieves the TAINTED value of a query request key by name.
     * 
     * Note that this value is direct user input; you should sanitize it
     * with Solar_Filter (or some other technique) before using it.
     * 
     * @param string $key The query key.
     * 
     * @param mixed $val If the key does not exist, use this value
     * as a default in its place.
     * 
     * @return mixed The value of that query key.
     * 
     */
    protected function _query($key, $val = null)
    {
        $exists = array_key_exists($key, $this->_query)
               && $this->_query[$key] !== null;
        
        if ($exists) {
            return $this->_query[$key];
        } else {
            return $val;
        }
    }
    
    /**
     * 
     * Redirects to another controller and action, then calls exit(0).
     * 
     * @param Solar_Uri_Action|string $spec The URI to redirect to.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '302 Found'.
     * 
     * @return void
     * 
     */
    protected function _redirect($spec, $code = 302)
    {
        $this->_response->redirect($spec, $code);
        exit(0);
    }
    
    /**
     * 
     * Redirects to another controller and action after disabling HTTP caching.
     * 
     * The _redirect() method is often called after a successful POST
     * operation, to show a "success" or "edit" controller. In such cases, clicking
     * clicking "back" or "reload" will generate a warning in the
     * browser allowing for a possible re-POST if the user clicks OK.
     * Typically this is not what you want.
     * 
     * In those cases, use _redirectNoCache() to turn off HTTP caching, so
     * that the re-POST warning does not occur.
     * 
     * This method sends the following headers before setting Location:
     * 
     * {{code: php
     *     header("Cache-Control: no-store, no-cache, must-revalidate");
     *     header("Cache-Control: post-check=0, pre-check=0", false);
     *     header("Pragma: no-cache");
     * }}
     * 
     * @param Solar_Uri_Action|string $spec The URI to redirect to.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '303 See Other'.
     * 
     * @return void
     * 
     */
    protected function _redirectNoCache($spec, $code = 303)
    {
        $this->_response->redirectNoCache($spec, $code);
        exit(0);
    }
    
    /**
     * 
     * Forwards internally to another action, using pre- and post-
     * action hooks, and resets $this->_view to the requested action.
     * 
     * You should generally use "return $this->_forward(...)" instead
     * of just $this->_forward; otherwise, script execution will come
     * back to where you called the forwarding.
     * 
     * @param string $action The action name.
     * 
     * @param array $params Parameters to pass to the action method.
     * 
     * @return void
     * 
     */
    protected function _forward($action, $params = null)
    {
        // set the current action on entry
        $this->_action = $action;
        
        // make sure params is an array
        settype($params, 'array');
        
        // run this before every action, may change the requested action.
        $this->_preAction();
        
        // does a related action-method exist?
        $method = $this->_getActionMethod($this->_action);
        if (! $method) {
            
            // no method found for the action.
            // this is the last thing we do in this chain.
            $this->_notFound($this->_action, $params);
            
        } else {
            
            // set the view to the requested action
            $this->_view = $this->_getActionView($this->_action);
        
            // run the action method, which may itself _forward() to other
            // actions.  pass all parameters in order.
            call_user_func_array(
                array($this, $method),
                $params
            );
        }
        
        // run this after every action
        $this->_postAction();
        
        // set the current action on exit so that $this->_action is
        // always the **first** action requested when we finally exit.
        $this->_action = $action;
    }
    
    /**
     * 
     * Whether or not user requested a specific process within the action.
     * 
     * By default, looks for $process_key in [[Solar_Request::post()]] to get the
     * value of the process request.
     * 
     * Checks against "PROCESS_$type" locale string for matching.  For example,
     * $this->_isProcess('save') checks Solar_Request::post('process') 
     * against $this->locale('PROCESS_SAVE').
     * 
     * @param string $type The process type; for example, 'save', 'delete',
     * 'preview', etc.  If empty, returns true if *any* process type
     * was posted.
     * 
     * @param string $process_key If not empty, check against this
     * [[Solar_Request::post()]] key instead $this->_process_key. Default
     * null.
     * 
     * @return bool
     * 
     */
    protected function _isProcess($type = null, $process_key = null)
    {
        // make sure we know what post-var to look in
        if (empty($process_key)) {
            $process_key = $this->_process_key;
        }
        
        // didn't ask for a process type; answer if *any* process was
        // requested.
        if (empty($type)) {
            $any = $this->_request->post($process_key);
            return ! empty($any);
        }
        
        // asked for a process type, find the locale string for it.
        $locale_key = 'PROCESS_' . strtoupper($type);
        $locale = $this->locale($locale_key);
        
        // $process must be non-empty, and must match locale string.
        // not enough just to match the locale string, as it might
        // be empty.
        $process = $this->_request->post($process_key, false);
        return $process && $process == $locale;
    }
    
    /**
     * 
     * Returns the method name for an action.
     * 
     * @param string $action The action name.
     * 
     * @return string The method name, or boolean false if the action
     * method does not exist.
     * 
     */
    protected function _getActionMethod($action)
    {
        // convert example-name to "actionExampleName"
        $method = str_replace('-', ' ', $action);
        $method = ucwords(trim($method));
        $method = 'action' . str_replace(' ', '', $method);
        
        // does the method exist?
        if (method_exists($this, $method)) {
            return $method;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Returns the allowed format list for a given action.
     * 
     * Allows the use of "foo-bar" (preferred), "fooBar", or "actionFooBar"
     * as the action key in the action_format array.
     * 
     * @param string $action The action name.
     * 
     * @return array The list of formats allowed for the action.
     * 
     */
    protected function _getActionFormat($action)
    {
        // skip if there are no action formats
        if (empty($this->_action_format)) {
            return array();
        }
        
        // look for "all actions" formats
        $all = empty($this->_action_format['*'])
             ? array()
             : (array) $this->_action_format['*'];
             
        // look for the action as passed (foo-bar)
        $key = $action;
        if (! empty($this->_action_format[$key])) {
            return array_merge($all, (array) $this->_action_format[$key]);
        }
        
        // convert the action to method style (fooBar) and look again
        $key = str_replace('-', ' ', $action);
        $key = ucwords(trim($key));
        $key = str_replace(' ', '', $key);
        $key[0] = strtolower($key[0]);
        if (! empty($this->_action_format[$key])) {
            return array_merge($all, (array) $this->_action_format[$key]);
        }
        
        // convert the action to full method style (actionFooBar)
        $key = 'action' . ucfirst($key);
        if (! empty($this->_action_format[$key])) {
            return array_merge($all, (array) $this->_action_format[$key]);
        }
        
        // no other ways to look for it
        return $all;
    }
    
    /**
     * 
     * Returns the view name for an action.
     * 
     * @param string $action The action name.
     * 
     * @return string The related view name.
     * 
     */
    protected function _getActionView($action)
    {
        // convert example-name to exampleName
        $view = str_replace('-', ' ', $action);
        $view = ucwords(trim($view));
        $view = str_replace(' ', '', $view);
        $view[0] = strtolower($view[0]);
        return $view;
    }
    
    // -----------------------------------------------------------------
    //
    // Behavior hooks.
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Executes after construction.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    /**
     * 
     * Executes before the first action.
     * 
     * @return void
     * 
     */
    protected function _preRun()
    {
    }
    
    /**
     * 
     * Executes before each action.
     * 
     * @return void
     * 
     */
    protected function _preAction()
    {
    }
    
    /**
     * 
     * Executes after each action.
     * 
     * @return void
     * 
     */
    protected function _postAction()
    {
    }
    
    /**
     * 
     * Executes after the last action.
     * 
     * @return void
     * 
     */
    protected function _postRun()
    {
    }
    
    /**
     * 
     * Executes before rendering the controller view and layout.
     * 
     * Use this to pre-process $this->_view_object, or to manipulate
     * controller properties with view helpers.
     * 
     * The default implementation sets the locale class for the getText
     * helper.
     * 
     * @return void
     * 
     */
    protected function _preRender()
    {
    }
    
    /**
     * 
     * Executes after rendering the controller view and layout.
     * 
     * Use this to do a final filter or manipulation of $this->_response
     * from the view and layout scripts.  By default, it leaves the
     * response alone.
     * 
     * @return void
     * 
     */
    protected function _postRender()
    {
    }
    
    /**
     * 
     * Adds an error message, then forwards to the 'error' action.
     * 
     * @param string $key The error-message locale key.
     * 
     * @param array $replace Replacement strings for the error message.
     * 
     * @return void
     * 
     */
    protected function _error($key, $replace = null)
    {
        $this->_errors[] = $this->locale($key, 1, $replace);
        $this->_response->setStatusCode(500);
        return $this->_forward('error');
    }
    
    /**
     * 
     * Indicates this is a cross-site request forgery attempt.
     * 
     * @return void
     * 
     */
    protected function _csrfAttempt()
    {
        $this->_errors[] = 'ERR_CSRF_ATTEMPT';
        $vars = $this->_request->post();
        foreach ((array) $vars as $key => $val) {
            $this->_errors[] = "$key: $val";
        }
        
        $this->_response->setStatusCode(403);
        return $this->_forward('error');
    }
    
    /**
     * 
     * Indicates an action (or other page) was not found.
     * 
     * @param string $action The name for the action that was not found.
     * 
     * @param array $params The params for the action that was not found.
     * 
     * @return void
     * 
     */
    protected function _notFound($action, $params = null)
    {
        $this->_errors[] = "Controller: \"{$this->_controller}\"";
        $this->_errors[] = "Action: \"$action\"";
        $this->_errors[] = "Format: \"{$this->_format}\"";
        foreach ((array) $params as $key => $val) {
            $this->_errors[] = "Param $key: $val";
        }
        $this->_response->setStatusCode(404);
        
        // just set the view; if we call _forward('error') we'll get the
        // error view, not the not-found view.
        $this->_view = 'notFound';
    }
    
    /**
     * 
     * When an exception is thrown during the fetch() process, use this
     * method to recover from it.
     * 
     * This default implementation just displays the exception, but extended
     * classes may override the behavior to return alternative output from
     * the failed fetch().
     * 
     * @param Exception $e The exception thrown during the fetch() process.
     * 
     * @return string The alternative output from the rescued exception.
     * 
     */
    protected function _exceptionDuringFetch(Exception $e)
    {
        $this->_errors[] = $e;
        $this->_view = 'exception';
        $this->_response->setStatusCode(500);
        
        // render directly; because this came from the fetch process, we
        // can't depend on that process to complete successfully.
        $this->_render();
        return $this->_response;
    }
}
