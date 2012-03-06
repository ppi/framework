<?php
/**
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Email
 */

class PPI_Email_Template extends PPI_Model {

	private $_sender;
	private $_recipient;
	private $_recipientName;
	private $_body;
	private $_subject;
	private $_replacerData;
	private $_templateName;
	private $_headers;

	function __construct($template = '', array $data = array()) {

		parent::__construct('ppi_email_template', 'id');
		if(!empty($template)) {
			$this->setTemplate($template, $data);
		}
	}

	/**
	 * Set the template to be used
	 * @param string $p_sTemplate Template Name
	 * @param array $p_aReplacerData Replacer Tags
	 * @return $this (fluent interface)
	 */
	function setTemplate($p_sTemplate, $p_aReplacerData = array()) {
		// get the template data, does the template exist ?
		$row = parent::getList("name = '$p_sTemplate'")->fetch();

		if(empty($row)) {
			throw new PPI_Exception('Trying to use Email Template: '. $p_sTemplate . ' but it doesn\'t exist');
		}

		// set the replacer data if its been specified here.
		if(!empty($p_aReplacerData)) {
			$this->setReplacerData($p_aReplacerData);
		}
		$this->_subject         = $row['subject'];
		$this->_body            = $row['body'];
		$this->_sender          = $row['from'];
		$this->_senderName      = $row['from_name'];
		$this->_templateName    = $p_sTemplate;
		return $this;
	}

	/**
	 * Replace the data from the templates
	 * @todo build up 2 arrays to go into a singular str_ireplace() call
	 * @return void
	 */
	function replaceData() {

		if(!empty($this->_replacerData)) {
			foreach($this->_replacerData as $key => $replace) {
				// Try to replace the subject
				if($this->_subject != '') {
					$this->_subject = str_ireplace('['.$key.']', $this->_replacerData[$key], $this->_subject);
				}
				if($this->_body != '') {
					$this->_body = str_ireplace('['.$key.']', $this->_replacerData[$key], $this->_body);
				}
			}
		}
	}

	/**
	 * Send the email out
	 *
	 * @return boolean
	 */
	function sendMail() {

		$this->replaceData();
		$mailer = Swift_Mailer::newInstance(Swift_MailTransport::newInstance());
		// @todo add $this->_senderName
		// @todo add $this->_recipientName
		$message = Swift_Message::newInstance($this->_subject)
			->setFrom(array($this->_sender => $this->_senderName))
			->setTo(array($this->_recipient => $this->_recipientName))
			->setBody($this->_body);

		$result = $mailer->send($message);

		return $result > 0;

		// log the mail send - this is a bug, it will try to insert to ppi_email_templates - this should insert to email_log instead.
		/*
		$oLog = new PPI_Model_Log();
		$oLog->addEmailLog(array(
			'to'       => $this->_recipient,
			'subject'  =>  $this->_subject,
			'body'     => $this->_body,
			'headers'  => $this->_headers,
			'tpl_name' => $this->_templateName
		));
		*/
	}

	/**
	 * Set the Sender
	 * @param string $p_sSender The Sender
	 * @return $this (fluent interface)
	 */
	function setSender($p_sSender) {
		$this->_sender = $p_sSender;
		return $this;
	}

	/**
	 * Set the Recipient
	 * @param mixed $p_mRecipient The Recipient(s)
	 * @return $this (fluent interface)
	 */
	function setRecipient($p_mRecipient) {
		if(is_array($p_mRecipient)) {
			foreach($p_mRecipient as $email => $name) {
				$this->_recipient = $email;
				$this->_recipientName = $name;
				break;
			}

		} elseif(is_string($p_mRecipient)) {
			$this->_recipient = $p_mRecipient;
		}
		return $this;
	}

	/**
	 * Set the Subject
	 * @param string $p_sSubject The Subject
	 * @return $this (fluent interface)
	 */
	function setSubject($p_sSubject) {
		$this->_subject = $p_sSubject;
		return $this;
	}
	/**
	 * Set the replacer data
	 * @param array $p_aData The replacer tags
	 * @return $this (fluent interface)
	 */
	function setReplacerData(array $p_aData) {
		$this->_replacerData = $p_aData;
		return $this;
	}

	/**
	 * Get the addedit structure for Formbuilder
	 * @param string $p_sMode The Mode
	 * @return array
	 */
	function getAddEditFormStructure($p_sMode) {
		$structure = array(
			'fields' => array(
				'name'        => array('type' => 'text', 'size' => 55, 'label' => 'Name'),
				'subject'     => array('type' => 'text', 'size' => 55, 'label' => 'Subject'),
				'from'        => array('type' => 'text', 'size' => 55, 'label' => 'From Email Address'),
				'from_name'   => array('type' => 'text', 'size' => 55, 'label' => 'From Email Name'),
				'body'        => array('type' => 'textarea', 'rows' => 10, 'cols' => 45, 'label' => 'Description')
			),
			'rules' =>  array(
				'name'       => array('type' => 'required', 'message' => 'Field cannot be blank'),
				'subject'    => array('type' => 'required', 'message' => 'Field cannot be blank'),
				'from'       => array('type' => 'required', 'message' => 'Field cannot be blank'),
				'body'       => array('type' => 'required', 'message' => 'Field cannot be blank'),
			)
		);
		return $structure;
}
}