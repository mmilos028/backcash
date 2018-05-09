<?php
class ErrorMailHelper {
	
	public static function sendMail($exception_text){
		$config = Zend_Registry::get("config");
		try{
			if($config->sendErrorsOnMail == "true"){
				$from = $config->mailSendErrorFrom;
				$recipients = $config->mailSendErrorTo;
				$smtpServer = $config->smtpServer;
				$tr = new Zend_Mail_Transport_Smtp($smtpServer);
				$mail = new Zend_Mail('UTF-8');
				$recipients_arr = explode(",", $recipients);
				$mail->addTo($recipients_arr, $config->mailToTitle);
				$mail->setBodyHtml($exception_text);
				$mail->setFrom($from, $config->mailFromTitle);
				$mail->setSubject($config->mailSubjectTitle);
				Zend_Mail::setDefaultTransport($tr);
				$mail->send();
			}
		} catch(Zend_Exception $ex){
		}
	}
	
	/**
	 * 
	 * Write error to log file
	 * @param string $message
	 */
	public static function writeErrorLog($message){
		try{
			$error_logger = Zend_Registry::get("logger");
			$error_logger->log($message, Zend_Log::ERR);
		}catch(Zend_Exception $ex){			
		}
	}
	
	/**
	 * 
	 * Write info to log file
	 * @param string $message
	 */
	public static function writeInfoLog($message){
		try{
			$error_logger = Zend_Registry::get("logger");
			$error_logger->log($message, Zend_Log::INFO);
		}catch(Zend_Exception $ex){			
		}
	}
	
	/**
	 * 
	 * Wrap method to write to log file and send mail
	 * @param string $mail_message
	 * @param string $log_message
	 */
	public static function writeError($mail_message, $log_message){
		try{
			if(strlen($mail_message) > 0){
				self::sendMail($mail_message);
			}
			if(strlen($log_message) > 0){
				self::writeErrorLog($log_message);
			}
		}catch(Zend_Exception $ex){			
		}
	}
	
	/**
	 * 
	 * Wrap method to write to log file and send mail
	 * @param string $mail_message
	 * @param string $log_message
	 */
	public static function writeInfo($mail_message, $log_message){
		try{
			if(strlen($mail_message) > 0){
				self::sendMail($mail_message);
			}
			if(strlen($log_message) > 0){
				self::writeInfoLog($log_message);
			}
		}catch(Zend_Exception $ex){			
		}
	}

    public static function writeToFirebugInfo($message){
        $config = Zend_Registry::get('config');
        if($config->db->profiler->enabled) {
            $writer = new Zend_Log_Writer_Firebug();
            $writer->setDefaultPriorityStyle('TRACE');
            $logger = new Zend_Log($writer);
            $logger->log($message, Zend_Log::INFO);
        }
    }
}