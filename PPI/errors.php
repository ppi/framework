<?php

/**
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   PPI
 */

/**
 * The default PPI error handler, will play with some data then throw an exception, thus the set_exception_handler callback is ran
 *
 * @param string $errno The error level (number)
 * @param string $errstr The error message
 * @param string $errfile The error filename
 * @param string $errline The error line
 * @throws PPI_Exception
 */
function ppi_error_handler($errno = '', $errstr = "", $errfile = "", $errline = "") {

	$ppi_exception_thrown = true;
	$error				= array();
	$error['code']		= $errno;
	$error['message']	= $errstr;
	$error['file']		= $errfile;
	$error['line']		= $errline;

	/* throw exception to user */
	$oException = new PPI_Exception();
	if (property_exists($oException, '_traceString')) {
		$error['backtrace'] = $oException->_traceString;
	}

	$error['sql'] = PPI_Registry::get('PPI_Model::Queries_Backtrace', array());
	// this function has the exit() call in it, so we must put it last
	ppi_show_exceptioned_error($error);
}

/**
 * The default exception handler
 *
 * @param object $oException The exception object
 * @return void
 */
function ppi_exception_handler($oException) {

	if (!$oException instanceof Exception) {
		return false;
	}

	$error = array(
		'code'		=> $oException->getCode(),
		'message'	=> $oException->getMessage(),
		'file'		=> $oException->getFile(),
		'line'		=> $oException->getLine(),
		'backtrace'	=> $oException->getTraceAsString(),
		'post'		=> $_POST,
		'cookie'	=> $_COOKIE,
		'get'		=> $_GET,
		'session'	=> $_SESSION
	);

	try {

		if (!PPI_Registry::exists('PPI_Config')) {
			ppi_show_exceptioned_error($error);
			return;
		}

		$oConfig = PPI_Helper::getConfig();
		$error['sql'] = PPI_Registry::get('PPI_Model::Queries_Backtrace', array());

	} catch (PPI_Exception $e) {
		writeErrorToLog($e->getMessage());
	} catch (Exception $e) {
		writeErrorToLog($e->getMessage());
	} catch (PDOException $e) {
		writeErrorToLog($e->getMessage());
	}
	ppi_show_exceptioned_error($error);

	// @todo This should go to an internal error page which doesn't use framework components and show the error code
}

function writeErrorToLog($message) {

	if ('On' !== ini_get('log_errors')) {
		return false;
	}

	$oConfig = PPI_Helper::getConfig();

	if ('syslog' === ($sErrorLog = ini_get('error_log'))) {
		syslog(LOG_ALERT, "\n" . $message . "\n");
	} else if ('' !== $sErrorLog && is_writable($sErrorLog)) {
		file_put_contents($sErrorLog, "\n" . $message . "\n", FILE_APPEND);
	}
}

/**
 * Show this exception
 *
 * @param string $p_aError Error information from the custom error log
 * @return void
 */
function ppi_show_exceptioned_error($p_aError = "") {
	$p_aError['sql'] = PPI_Helper::getRegistry()->get('PPI_Model::Query_Backtrace', array());
	if(!empty($p_aError)) {

		// @todo - fix this shit implementation
		try {
			$logInfo = $p_aError;
			foreach(array('cookie', 'session', 'get', 'post', 'sql') as $field) {
				if(isset($logInfo[$field])) {
					$logInfo[$field] = serialize($logInfo[$field]);
				}
			}
			unset($logInfo['code']);
			$oModel = new PPI_Model_Shared('ppi_exception', 'id');
			$oModel->insert($logInfo);
		} catch(PPI_Exception $e) {} catch(Exception $e) {}

		try {
			$oEmail    = new PPI_Email_PHPMailer();
			$oConfig   = PPI_Helper::getConfig();
			$url       = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
			$ip        = $_SERVER['REMOTE_ADDR'];
			$referer   = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not Available';
			if(isset($oConfig->system->error->email)) {

			$emailBody = <<<EOT
Hey Support Team,
An error has occured. The following information will help you debug:

Message:    {$p_aError['message']}
Line:       {$p_aError['line']}
File:       {$p_aError['file']}
URL:        {$url}
User Agent: {$userAgent}
IP:         {$ip}
Referer:    {$referer}
Backtrace:  {$p_aError['backtrace']}

EOT;
				$aErrorConfig = $oConfig->system->error->email->toArray();
				$aEmails = array_map('trim', explode(',', $aErrorConfig['to']));
				foreach($aEmails as $email) {
					$name = '';
					if(strpos($email, ':') !== false) {
						list($name, $email) = explode(':', $email, 2);
					}
					$oEmail->AddAddress($email, $name);
				}

				$fromEmail = $aErrorConfig['from'];
				$fromName = '';
				if(strpos($fromEmail, ':') !== false) {
					list($fromName, $fromEmail) = explode(':', $fromEmail, 2);
				}
				$oEmail->SetFrom($fromEmail, $fromName);
				$oEmail->Subject = $aErrorConfig['subject'];
				$oEmail->Body = $emailBody;
				$oEmail->Send();
			}

		} catch(PPI_Exception $e) {} catch(Exception $e) {}
	}

	$oApp = PPI_Helper::getRegistry()->get('PPI_App', false);
	if($oApp === false) {
		$sSiteMode = 'development';
		$heading = "Exception";
		require SYSTEMPATH.'View/fatal_code_error.php';
		echo $header.$html.$footer;
		exit;
	}

	$sSiteMode = $oApp->getSiteMode();
	if($sSiteMode == 'development') {
		$heading = "Exception";
		$baseUrl = PPI_Helper::getConfig()->system->base_url;
		require SYSTEMPATH.'View/code_error.php';
		echo $header.$html.$footer;
	} else {

		$controller = new PPI_Controller();
		$controller->systemInit($oApp);
		$controller->render('framework/error', array(
			'message' => $p_aError['message'],
			'errorPageType' => '404'
		));
	}
	exit;
}

/**
 * Set the error and exception handlers
 *
 * @param string $p_sErrorHandler The error handler function name
 * @param string $p_sExceptionHandler The exception handler function name
 * @return void
 */
function setErrorHandlers($p_sErrorHandler = null, $p_sExceptionHandler = null) {

	if (null !== $p_sErrorHandler) {
		set_error_handler($p_sErrorHandler, E_ALL);
	}

	if (null !== $p_sExceptionHandler) {
		set_exception_handler($p_sExceptionHandler);
	}
}
