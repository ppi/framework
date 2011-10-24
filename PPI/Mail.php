<?php
/**
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Core
 * @link      www.ppiframework.com
 */
class PPI_Mail {

    /**
     * PPI Mail Sending Functioin
     * @param array $p_aOptions The options for sending to the mail library
     * @uses $p_aOptions[subject, body, toaddr] are all mandatory.
     * @uses Options available are toname
     * @return boolean The result of the mail sending process
     */
    static function sendMail(array $p_aOptions) {

		$oConfig = PPI_Helper::getConfig();
        $oEmail  = new PPI_Model_Email_Advanced();
        if(!isset($p_aOptions['subject'], $p_aOptions['body'], $p_aOptions['toaddr'])) {
            throw new PPI_Exception('Invalid parameters to sendMail');
        }

		$oEmail->Subject = $p_sSubject;
        if(isset($p_aOptions['fromaddr'], $p_aOptions['fromname'])) {
            $oEmail->SetFrom($p_aOptions['fromaddr'], $p_aOptions['fromname']);
        } elseif(isset($p_aOptions['fromaddr'])) {
            $oEmail->SetFrom($p_aOptions['fromaddr']);
        } else {
            $oEmail->SetFrom($oConfig->system->adminEmail, $oConfig->system->adminName);
        }

        if(isset($p_aOptions['toaddr'], $p_aOptions['toname'])) {
            $oEmail->AddAddress($p_aOptions['toaddr'], $p_aOptions['toname']);
        } elseif(isset($p_aOptions['toaddr'])) {
            $oEmail->AddAddress($p_aOptions['toaddr']);
        }

        if(isset($p_aOptions['altbody'])) {
            $oEmail->AltBody = $p_sMessage;
        }

		$oEmail->MsgHTML($p_aOptions['body']);

		// If the email sent successfully,
		return $oEmail->Send();

        // @todo - Log the email sending process.

    }

}