<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Core
 * @link      www.ppiframework.com
 */

class PPI_Exception extends Exception {

    /**
     * The backtrace
     *
     * @var string
     */
	public $_traceString = '';

    /**
     * The error line
     *
     * @var int|string
     */
	public $line = '';

    /**
     * The error code
     *
     * @var int|string
     */
	public $code = '';

    /**
     * The error message
     *
     * @var string
     */
	public $message = '';

    /**
     * The error filename
     *
     * @var string
     */
	public $file = '';

    /**
     * The backtrace as an array
     *
     * @var array
     */
	public $_traceArray = array();

    /**
     * Any SQL queries that have been ran
     *
     * @var array
     */
	public $_queries = array();

    /**
     * Singleton instance
     *
     * @var null
     */
    private static $_instance = null;

    /**
     * Initialize the default registry instance.

     * @return void
     */
    protected static function init() {
        self::setInstance(new PPI_Exception());
    }

    /**
     * Set the default registry instance to a specified instance.
     *
     * @return void
     */
    public static function setInstance(PPI_Exception $instance) {
        self::$_instance = $instance;
    }
    /**
     * Retrieves the default exception instance.
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::init();
        }
        return self::$_instance;
    }

    /**
     * Initialise the PPI_Exception object and start setting up debug info such as backtraces.
     *
     * @param string $message
     * @param null|array $sqlQueries
     */
	function __construct($message = '', $sqlQueries = null) {
		parent::__construct($message);
		$this->_traceString = $this->getTraceAsString();
		$this->_traceArray	= $this->getTrace();
		$this->_queries 	= PPI_Helper::getRegistry()->get('PPI_Model_Queries', array());
	}

	/**
     * This function shows a 403 error
     *
     * @access	public
     * @return	void
     */
	function show_403($p_sMessage = "") {
		$heading = "403 Forbidden";
		$message = (!empty ($p_sMessage) ) ? $p_sMessage : "You are not allowed to access the requested location";
		PPI_Helper::getRegistry()->set('PPI_View::httpResponseCode', 403);
		require SYSTEMPATH.'errors/403.php';
	}


	/**
    * This function shows a 404 error
    *
    * @access	public
    * @todo     change this to a new function name
    * @todo     make this do 404 on the HTTP status line
    * @param    string argument name
    * @return	void
    */
	static function show_404($p_sLocation = "", $p_bUseImage = false) {
		$heading = "Page cannot be found";
		$message = !empty($p_sLocation) ? $p_sLocation : "The page you requested was not found.";
		PPI_Helper::getRegistry()->set('PPI_View::httpResponseCode', 404);
		header('Status: 404 Not Found');
		$oView   = new PPI_Controller();
		$oView->render('framework/404', array(
			'heading'       => 'Page cannot be found', 'message' => $message,
			'errorPageType' => '404'
		));
        exit;
	}

	/**
	 * Show an error message
	 *
	 * @param string $p_sMsg
     * @return void
	 */
	function show_error($p_sMsg) {
        $oConfig = PPI_Helper::getConfig();
		$heading = "PHP Error";
		$baseUrl = $oConfig->system->base_url;
		require SYSTEMPATH.'View/code_error.php';
		echo $header.$html.$footer;
	}

	/**
	 * Same as show error but return the content as a param
	 *
	 * @param array $p_aError
	 * @return string The HTML contents
	 */
	function getErrorForEmail($p_aError = "") {
		$oConfig = PPI_Helper::getConfig();
		$heading = "PHP Error";
		$baseUrl = $oConfig->system->base_url;
		require SYSTEMPATH.'View/code_error.php';
		return $html;
	}
}
