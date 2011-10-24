<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Controller
 */
namespace PPI\Controller;
class User extends APP_Controller_Application {

	/**
	 * The constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * This method is called after the user logs in successfully, it will determine the location
	 * where they are redirected to.
	 *
	 * @return void
	 */
	protected function postLoginRedirect() {
		$oSession = $this->getSession();
		// Do we have a return Url ?
		if( ($returnUrl = $oSession->get('PPI_Login::returnUrl')) !== null) {
			// Remove return url from session
			$oSession->remove('PPI_Login::returnUrl');
			$this->redirect($returnUrl, false);
		}
		$this->redirect('');
	}

	/**
	 * This function cannot be called directly, it must be extended by a child class and then called.
	 *
	 * @return void
	 */
	protected function login() {

		// If they are already logged in
		if($this->isLoggedIn() !== false) {
			$this->postLoginRedirect();
		}

		// Init
		$oUser = new APP_Model_User();
		$oForm = new PPI_Model_Form();
		$oForm->init('user_login', '', 'post');
		$oForm->setFormStructure($oUser->getLoginFormStructure());

		// If they have submitted the login form
		if($oForm->isSubmitted()) {

			$aSubmitValues = $oForm->getSubmitValues();

			// If the login fails lets set an element error
			if($oUser->login($aSubmitValues['email'], $aSubmitValues['password']) === false) {
				$oForm->setElementError('email', 'Login Failed. Please check your credentials and try again.');
			}

			// If login was successfull, redirect to the postLoginRedirect location
			if($oForm->isValidated()) {
				$this->postLoginRedirect();
			}
		}
		// Load our view
		$this->load('user/login', array('formBuilder' => $oForm->getRenderInformation()));
	}

	/**
	 * This function cannot be called directly, it must be extended by a child class and then called.
	 *
	 * @return void
	 */
	protected function recover() {
		// Take in the username field, which could be the username or the email.
		// Ship out an email to the user's email address with the activationcode

		$oUser = new UserModel();
		$oForm = new PPI_Model_Form();
		$sTemplate = 'user/recover_step1';
		$sFormName = 'user_recover_step1';

		$oForm->init('user_recover_step1');
		$oForm->setFormStructure($oUser->getRecoverFormStructure());

		// We have submitted the email value, now lets dispatch an email to the relevant place.
		if($oForm->isSubmitted() && $oForm->isValidated()) {

			// Get form values
			$aValues = $oForm->getSubmitValues();

			// Which form are we submitting from? Is it the step1, or step2 ?

			// We are in step 2
			if(PPI_Session::getInstance()->get('recover_user_id') !== null) {

				// Lets grab userid from the session and take in the NEW password the user entered.
				// Update the users record and fire them off to the login page.
				$sPassword = $aValues['password'];

				// We are in step 1
			} else {

				// If the email was valid, and the email was dispatched.
				$aUser = $oUser->getRecord('email = ' . $oUser->quote($aValues['email']));
				if(count($aUser) > 0) {

					if($oUser->sendRecoverEmail($aUser)) {
						$successMessage = 'An email has been dispatched to ' . $aValues['email'] . '. Please remember to check your spam folder.';
						$this->redirect('user/recover/successmsg/' . urlencode($successMessage));

						// The email was errornous
					} else {
						$failureMessage = 'We tried to send out a recovery email to your address but there an error.';
						$this->redirect('user/recover/failuremsg/' . urlencode($failureMessage));
					}

					// User record was not round
				} else {
					$failureMessage = 'That email address was not found in our database, please check your input and try again.';
					$this->redirect('user/recover/failuremsg/' . urlencode($failureMessage));
				}

			}





			// See if we have been sent a code
		} else if($this->oInput->get('recover') != '') {

			// Lookup the user with this code
			$aUser = $oUser->getRecord('recover_code = ' . $oUser->quote($this->oInput->get('recover')));
			if(count($aUser) > 0) {
				$sPrimaryKey = $oUser->getPrimaryKey();

				// Wipe the recovery_code
				$oUser->putRecord(array('recover_code' => '', $sPrimaryKey => $aUser[$sPrimaryKey]));

				// Set the looked up userID in the session so when they submit the "new password" form, we know who they are and can update their password.
				PPI_Session::getInstance()->set('recover_user_id', $aUser['id']);


				// Show them the enter new password screen
				$sTemplate = 'user/recover_step2';
				$sFormName = 'user_recover_step2';
			}

			// Show the user the change password screen now that we know who they are.

			// Show the form to get the user to enter their usernameField value
		}



		// Load our view
		$this->load($sTemplate, array(
			'failuremsg'  => $this->oInput->get('failuremsg'),
			'successmsg'  => $this->oInput->get('successmsg'),
			'formBuilder' => $oForm->getRenderInformation()
		));

	}

	/**
	 * This function cannot be called directly, it must be extended by a child class and then called.
     *
     * @return void
	 */
	protected function register() {

		// If they are already logged in, send them to the postloginredirect location
		if($this->isLoggedIn() === true) {
			$this->postLoginRedirect();
		}
		// Init
		$oForm = new PPI_Model_Form();
		$oUser = new APP_Model_User();
		$oForm->init('user_register', '', 'post');
		$oForm->setFormStructure($oUser->_registerFormStructure);

		// If the form has been submitted and has been validated
		if($oForm->isSubmitted() && $oForm->isValidated()) {

			// Get the info from the form and pass it to the usermodel for insertion
			$oUser->putRecord($oForm->getSubmitValues());

			// Redirect to the login page
			$this->redirect('user/login');

		}

		$this->addStylesheet('formbuilder.css');
		$this->addJavascript('jquery-validate/jquery.validate.min.js');

		// show our registration page
		$this->load('user/register', array('formBuilder' => $oForm->getRenderInformation()));

	}



}