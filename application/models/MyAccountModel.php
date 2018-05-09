<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class MyAccountModel{
	public function __construct($config = array()){
	}
	//returns user informations for affiliate
    /**
     * @param $session_id
     * @param int $subject_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getUserInformationDirect($session_id, $subject_id = 0){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$USER_INFO_DIRECT(:p_session_id_in, :p_subject_id_in, :p_user_info_out, :p_currency_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$p_user_info = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_user_info_out', $p_user_info);
			$p_currency_list = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_currency_list_out', $p_currency_list);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$p_user_info->execute();
			$p_currency_list->execute();
			$p_user_info->free();		
			$p_currency_list->free();
			$dbAdapter->closeConnection();
			return array("details"=>$p_user_info, "currency_list"=>$p_currency_list);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	//returns user informations for affiliate
    /**
     * @param $session_id
     * @param int $subject_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getUserInformation($session_id, $subject_id = 0){
    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
	$dbAdapter = Zend_Registry::get('db_auth');
	$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$USER_INFO(:p_session_id_in, :p_subject_id_in, :p_user_info_out, :p_currency_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$p_user_info = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_user_info_out', $p_user_info);
			$p_currency_list = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_currency_list_out', $p_currency_list);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$p_user_info->execute();
			$p_currency_list->execute();
			$p_user_info->free();
			$p_currency_list->free();
			$dbAdapter->closeConnection();
			return array("details"=>$p_user_info, "currency_list"=>$p_currency_list);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}