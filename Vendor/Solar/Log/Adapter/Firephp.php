<?php
/**
 * 
 * Log adapter for Firephp/Firebug.
 * 
 * @category Solar
 * 
 * @package Solar_Log
 * 
 * @author Richard Thomas <richard@phpjack.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Firephp.php 4626 2010-07-02 13:27:51Z pmjones $
 * 
 */
class Solar_Log_Adapter_Firephp extends Solar_Log_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @config string|array events The event types this instance
     *   should recognize; a comma-separated string of events, or
     *   a sequential array.  Default is all events ('*').
     * 
     * @config string format The line format for each saved event.
     *   Use '%t' for the timestamp, '%e' for the class name, '%e' for
     *   the event type, '%m' for the event description, and '%%' for a
     *   literal percent.  Default is '%t %c %e %m'.
     * 
     * @config string output Output mode.  Set to 'html' for HTML, or 'text' for plain 
     *   text.  Default autodetects by SAPI version.  Value is ignored by this
     *   adapter, since it encodes everything into JSON format.
     * 
     * @config dependency response A Solar_Http_Response dependency injection.
     * 
     * @var array
     * 
     */
    protected $_Solar_Log_Adapter_Firephp = array(
        'events'   => '*',
        'format'   => '%t %c %e %m', // time, class, event, message
        'output'   => null,
        'response' => 'response',
    );
    
    /**
     * 
     * The Solar_Http_Response where headers will be set.
     * 
     * @var Solar_Http_Response
     * 
     */
    protected $_response;

    /**
     * 
     * Which header are we on
     * 
     * @var int
     * 
     */
    protected $_count = 1;
    
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
        
        $this->_response = Solar::dependency(
            'Solar_Http_Response',
            $this->_config['response']
        );
        
        $this->_json = Solar::factory('Solar_Json');
        // Setup headers based on the wildfire standard        
        $this->_response->setHeader(
            'X-Wf-Protocol-1',
            'http://meta.wildfirehq.org/Protocol/JsonStream/0.2'
        );
        
        $this->_response->setHeader(
            'X-Wf-1-Plugin-1',
            'http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3'
        );
        
        $this->_response->setHeader(
            'X-Wf-1-Structure-1',
            'http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1'
        );
        
    }
    
    /**
     * 
     * Sends the log message.
     * 
     * @param string $class The class name reporting the event.
     * 
     * @param string $event The event type (LOG/INFO/WARN/ERROR).
     * 
     * @param string $descr A description of the event. 
     * 
     * @return mixed Boolean false if the event was not saved (usually
     * because it was not recognized), or a non-empty value if it was
     * saved.
     * 
     */
    protected function _save($class, $event, $descr)
    {
        
        if (strlen($descr) <= 5000) {
            $json = $this->_buildJson($class, $event, $descr);
            $this->_setHeader(sizeof($json) . "|$json|");
        } else {
            $json = $this->_buildJson($class, $event, $descr);
            $chunks = chunk_split($json, 5000, "\n");
            $parts = explode("\n", $chunks);
            $this->_setHeader(strlen($json) . "|{$parts[0]}|\\");
            next($parts);
            $num = sizeof($parts);
            // We start with 2 because we skipped the first item and don't want the last item
            $count = 2;
            while (list($key, $part) = each($parts)) {
                $count++;
                if ($part) {
                    $string = "|$part|";
                    if($count < $num) {
                        $string = $string . "\\";
                    }
                    $this->_setHeader($string);
                }
            }
        }
        
        return true;
    }

    /**
     * 
     * Builds json string
     * 
     * @param string $class The class name reporting the event.
     * 
     * @param string $event The event type (LOG/INFO/WARN/ERROR).
     * 
     * @param string $descr A description of the event. 
     * 
     * @return string/json
     * 
     */
    protected function _buildJson($class, $event, $descr)
    {
        $return = array();
        $return[0] = new stdClass();
        $return[1] = $descr;
        $event = strtoupper($event);
        switch($event) {
            case 'GROUP_START':
                $return[0]->Type = 'GROUP_START';
            break;
            case 'GROUP_END':
                $return[0]->Type = 'GROUP_END';
           break;
            case 'LOG':
            case 'INFO':
            case 'WARN':
            case 'ERROR':
            default:
                $return[0]->Type = $event;
                $return[0]->Label = $class;
            break;
        }
        return json_encode($return);
    }
    
    /**
     * 
     * Sets the log message in the response headers.
     * 
     * @param string $data The JSON data for the header.
     *
     * @param int $type 3 - normal, 2 - dump
     * 
     * @return void
     * 
     */
    protected function _setHeader($data)
    {
        $this->_response->setHeader(
            "X-Wf-1-1-1-".$this->_count, "$data"
        );
        $this->_count++;
    }
}
