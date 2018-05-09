<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class SessionModel{
	public function __construct(){
	}

    /**
     * @param $backoffice_session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public function checkReverseTypeUser($backoffice_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.CHECK_AFF_ROOT_OR_REVERSE(:p_session_in, :p_result_out)');
			$stmt->bindParam(':p_session_in', $backoffice_session_id);
			$result = "";
			$stmt->bindParam(':p_result_out', $result, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "result"=>$result);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
    }


    /**
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function userPanic($affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$USER_PANIC(:p_affiliate_id_in, :p_on_off_out)');
			$stmt->bindParam(':p_affiliate_id_in', $affiliate_id);
			$status = "";
			$stmt->bindParam(':p_on_off_out', $status, SQLT_CHR, 5);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$status);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//sets panic state to p_affiliate_id_in subject
    /**
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function setPanic($affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SET_PANIC(:p_affiliate_id_in)');
			$stmt->bindParam(':p_affiliate_id_in', $affiliate_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//unsets panic state for p_affiliate_id_in subject
    /**
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function unsetPanic($affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$UNSET_PANIC(:p_affiliate_id_in)');
			$stmt->bindParam(':p_affiliate_id_in', $affiliate_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//breaks player sessions and sets them with panic state
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function panic($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$PANIC(:p_session_id_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

	//bann user from system
    /**
     * @param $subject_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function banUser($subject_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$BANNED_USER(:p_subject_id_in)');
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

	//revalidates packages in oracle memory for ORA-04068 issue
    /**
     * @throws Zend_Exception
     */
	public static function resetPackages(){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PACKAGE_RESET()');
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($ex);
		}
	}

	//for every user action in backoffice perform session validation test
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
	public static function validateSession($session_id){
		$yes_no = NO;
        $remaining_seconds = 0;
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$VALIDATE_SESSION(:p_session_id_in, :p_yes_no_out, :p_remaining_seconds_out)');
            //$stmt = $dbAdapter->prepare('CALL QUICK_FIX.M$VALIDATE_SESSION(:p_session_id_in, :p_yes_no_out, :p_remaining_seconds_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(":p_yes_no_out", $yes_no, SQLT_CHR, 5);
            $stmt->bindParam(":p_remaining_seconds_out", $remaining_seconds, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$yes_no);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			$yes_no = YES;
			return array("status"=>$yes_no);
		}		
	}

    //for every user action in backoffice perform session validation test
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
	public static function pingSession($session_id){
		$yes_no = NO;
        $remaining_seconds = 0;
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$VALIDATE_SESSION(:p_session_id_in, :p_yes_no_out, :p_remaining_seconds_out)');
            //$stmt = $dbAdapter->prepare('CALL QUICK_FIX.M$VALIDATE_SESSION(:p_session_id_in, :p_yes_no_out, :p_remaining_seconds_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(":p_yes_no_out", $yes_no, SQLT_CHR, 5);
            $stmt->bindParam(":p_remaining_seconds_out", $remaining_seconds, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "yes_no_status"=>$yes_no, "remaining_seconds"=>$remaining_seconds);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			$yes_no = YES;
			return array("status"=>OK, "yes_no_status"=>$yes_no, "remaining_seconds"=>0);
		}
	}

	//close session when user logout
    /**
     * @param $session_id
     * @throws Zend_Exception
     */
	public static function closeSession($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{	
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CLOSE_SESSION(:p_session_id_in, :p_subject_id_in, :p_broken_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$subject_id = 0;
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$broken = NO;
			$stmt->bindParam(':p_broken_in', $broken, SQLT_CHR, 5);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);			
		}
	}
	//break user backoffice session on demand
    /**
     * @param $session_id
     * @param $subject_id
     * @throws Zend_Exception
     */
	public static function breakBOSession($session_id, $subject_id){
    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
	$dbAdapter = Zend_Registry::get('db_auth');
	$dbAdapter->beginTransaction();
		try{	
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$BREAK_BO_PLAYER_SESSION(:p_session_id_in, :p_subject_id_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	//breaks player session in active game
    /**
     * @param $player_id
     * @param $panic
     * @throws Zend_Exception
     */
	public static function breakPlayerSession($player_id, $panic){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.BREAK_PLAYER_SESSION(:p_player_id_in, :p_panic_in, :p_new_sess_kills)');
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_panic_in', $panic);
            $new_session_kills = null;
            $stmt->bindParam(':p_new_sess_kills', $new_session_kills);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //list number active player sessions
    /**
     * @param $session_id
     * @return array|null
     * @throws Zend_Exception
     */
	public static function listNumberActivePlayerSession($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$NO_ACTIVE_PLAYER_SESSION(:p_session_id_in, :no_game_session_out, :no_BO_session_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$no_game_session = 0;
			$stmt->bindParam(':no_game_session_out', $no_game_session, SQLT_INT);
			$no_bo_session = 0;
			$stmt->bindParam(':no_BO_session_out', $no_bo_session, SQLT_INT);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("no_game_sessions"=>$no_game_session, "no_bo_sessions"=>$no_bo_session);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			return null;
		}
	}

    /**
     * @param $session_id
     * @throws Zend_Exception
     */
     public static function setTimeModified($session_id){
         /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.SET_TIME_MODIFIED(:p_session_id_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
    }
}