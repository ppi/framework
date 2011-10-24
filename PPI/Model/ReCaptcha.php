<?php
/**
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
namespace PPI\Model;
class ReCaptcha {
    /**
     * URI to the regular API
     * @var string API Server
     */
    const API_SERVER = 'http://api.recaptcha.net';

    /**
     * URI to the secure API
     * @var string API Server Secure
     */
    const API_SERVER_SECURE = 'https://api-secure.recaptcha.net';

    /**
     * URI to the verify server
     * @var string Verify Server
     */
    const VERIFY_SERVER = 'api-verify.recaptcha.net';

    /**
     * Public key used when displaying the captcha
     * @var string $_publicKey
     */
    protected $_publicKey = null;

    /**
     * Private key used when verifying user input
     * @var string $_privateKey
     */
    protected $_privateKey = null;

    /**
     * Ip address used when verifying user input
     * @var string $_ip
     */
    protected $_ip = null;


    /**
     * Parameters for the object
     * @var array $_params
     */
    protected $_params = array(
        'ssl' => false, /* Use SSL or not when generating the recaptcha */
        'error' => null, /* The error message to display in the recaptcha */
        'xhtml' => false /* Enable XHTML output (this will not be XHTML Strict
                            compliant since the IFRAME is necessary when
                            Javascript is disabled) */
    );

    /**
     * Options for tailoring reCaptcha
     * See the different options on http://recaptcha.net/apidocs/captcha/client.html
     * @var array $_options
     */
    protected $_options = array(
        'theme' => 'red',
        'lang' => 'en',
    );

	function __construct() {
		global $oConfig;
        if ($oConfig->layout->captchaPublicKey !== '') {
            $this->setPublicKey($oConfig->layout->captchaPublicKey);
        } else {
        	throw new PPI_Exception('Unable to use Captcha Service with no Public Key')	;
		}
        if ($oConfig->layout->captchaPrivateKey !== '') {
            $this->setPrivateKey($oConfig->layout->captchaPrivateKey);
        } else {
        	throw new PPI_Exception('Unable to use Captcha Service with no Private Key')	;
		}
        $this->setIp($_SERVER['REMOTE_ADDR']);
	}

	function getHTML($error = null, $use_ssl = false) {
		global $oConfig;
		if ($use_ssl) {
				$server = self::API_SERVER_SECURE;
	        } else {
				$server = self::API_SERVER;
	        }
	        $errorpart = "";
	        if ($error) {
	           $errorpart = "&amp;error=" . $error;
	        }
	        return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $this->getPublicKey() . $errorpart . '"></script>

		<noscript>
	  		<iframe src="'. $server . '/noscript?k=' . $this->getPublicKey() . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
	  		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
	  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
		</noscript>';
	}

   /**
     * Verify the user input
     *
     * This method calls up the post method and returns a
     *
     * @param string $challengeField
     * @param string $responseField
     * @return array
     */
	function verify($challenge, $response) {
		// Discard spam submissions (Taken From ReCaptcha API)
		if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
			return array('valid' => false, 'error' => 'incorrect-captcha-sol');
		}
		// Send the information to ReCaptcha using fsockopen
		$aData['challenge']	 = $challenge;
		$aData['response']	 = $response;
		$aData['privatekey'] = $this->getPrivateKey();
		$aData['remoteip'] 	 = $this->getIp();
		$sQueryRequest 		 = $this->queryStringEncode($aData);
        $sHTTPReq  			 = "POST /verify HTTP/1.0\r\n";
        $sHTTPReq 			 .= "Host: ".self::VERIFY_SERVER."\r\n";
		$sHTTPReq 			 .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$sHTTPReq 			 .= "Content-Length: " . strlen($sQueryRequest) . "\r\n";
		$sHTTPReq 			 .= "User-Agent: reCAPTCHA/PHP\r\n";
		$sHTTPReq 			 .= "\r\n";
		$sHTTPReq 			 .= $sQueryRequest;
		if( false == ( $fs = @fsockopen(self::VERIFY_SERVER, 80, $errno, $errstr, 10) ) ) {
			throw new PPI_Exception('Could not open socket to ReCaptcha');
		}
		fwrite($fs, $sHTTPReq);
		$response = '';

		// Retreive the response from ReCaptcha
		while(!feof($fs)) {
			$response .= fgets($fs, 1160); // One TCP-IP packet
		}
		// Obtain the result and an optional error message
		list(, $aAnswers) 			= explode("\r\n\r\n", $response, 2);
		list($sVerified, $sError) 	= explode("\n", $aAnswers);
		if( trim($sVerified) == 'true') {
		    $aResponse['valid'] = true;
		} else {
		    $aResponse['valid'] = false;
		    $aResponse['error'] = $sError;
		}
		return $aResponse;
	}

	/**
	 * Encodes the given data into a query string format
	 * @param array $data array of string elements to be encoded
	 * @return string encoded request
	 */
	function queryStringEncode ($data) {
		$req = '';
		foreach($data as $key => $value) {
			$req .= $key . '=' . urlencode(stripslashes($value)) . '&';
		}
		// Change this to $req = substr($req, 0, -1);
		return substr($req,0,strlen($req)-1);
	}

    /**
     * Set the ip property
     * @param string $ip
     * @return PPI_Model_ReCaptcha
     */
    public function setIp($ip) {
        $this->_ip = $ip;
        return $this;
    }

    /**
     * Get the ip property
     * @return string
     */
    public function getIp() {
        return $this->_ip;
    }

    /**
     * Get the public key
     * @return string
     */
    public function getPublicKey() {
        return $this->_publicKey;
    }

    /**
     * Set the public key
     * @param string $publicKey
     * @return PPI_Model_ReCaptcha
     */
    public function setPublicKey($publicKey) {
        $this->_publicKey = $publicKey;
        return $this;
    }

    /**
     * Get the private key
     * @return string
     */
    public function getPrivateKey() {
        return $this->_privateKey;
    }

    /**
     * Set the private key
     * @param string $privateKey
     * @return PPI_Model_ReCaptcha
     */
    public function setPrivateKey($privateKey) {
        $this->_privateKey = $privateKey;
        return $this;
    }
}
