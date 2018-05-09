<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
//SENDS MAILS BY FORWARDING REQUEST TO ONLINECASINOSERVICE APPLICATION
//TO WEB SITE PLAYERS or on SUPPORT MAILs
class WebSiteEmailSender{


	//MyAccountPanelController::sendCustomerMailAction
	//sends customer mail to administrator
	public function sendCustomerMailToAdministrator($mailTo, $mailFrom, $mailTitle, $mailContent){
		$config = Zend_Registry::get('config');
		$mailCustomerToAdministratorWebServiceURL = $config->mailCustomerToAdministratorWebService;
		$fields = array(
			'mail_to'=>$mailTo,
			'mail_from'=>$mailFrom,
			'mail_title'=>$mailTitle,
			'mail_content'=>$mailContent
		);
        $fields_string = "";
		foreach($fields as $key=>$value) {
			$fields_string .= $key.'='.$value.'&';
		}
		rtrim($fields_string, '&');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $mailCustomerToAdministratorWebServiceURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		//disable ssl verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		if(curl_errno($ch)){
			//there was an error sending custom mail to player
			$error_message = curl_error($ch);
			require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
			$message = "Error while sending customer mail to administrator. <br /> Message: <br /> {$error_message}";
			ErrorMailHelper::writeError($message, $message);
			return array("status"=>NOK);
		}else{
			curl_close($ch);
			return array("status"=>OK, "message"=>$data);
		}
	}
}