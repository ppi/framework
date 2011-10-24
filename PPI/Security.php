<?php

/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Core
 * @link      www.ppiframework.com
 *
 */
namespace PPI;
class Security {

	/**
	 * Create a new CSRF key and set it in the session
	 * @return string The Token
	 */
	public static function createCSRF() {
		$token = md5(uniqid(mt_rand(), true));
		self::setCSRF($token);
		return $token;
	}

	/**
	 * Validate CSRF key with one in the session
	 * @param string $token
	 * @return boolean
	 */
	public static function checkCSRF($token) {
		return $token !== null ? self::getCSRF() === $token : false;
	}

	/**
	 * Set the CSRF in the session
	 * @param string $token
	 */
	public static function setCSRF($token) {
		PPI_Helper::getSession()->set('PPI_Security::csrfToken', $token);
	}

	/**
	 * Get the CSRF token from the session
	 * @return string
	 */
	public static function getCSRF() {
		return PPI_Helper::getSession()->get('PPI_Security::csrfToken');
	}
}
