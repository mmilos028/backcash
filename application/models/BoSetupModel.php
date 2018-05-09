<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class BoSetupModel{
	public function __construct(){
	}
	
	//get current Auto Stop Period for skill games
    /**
     * @param $session_id
     * @return string
     * @throws Zend_Exception
     */
	public static function currentAutoStopPeriod($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.GET_AUTO_STOP_PERIOD(:p_session_id_in, :p_auto_stop_period_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$auto_stop_period = "";
			$stmt->bindParam(':p_auto_stop_period_out', $auto_stop_period, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $auto_stop_period;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
							  
	//set auto stop period for skill games
    /**
     * @param $session_id
     * @param $auto_stop_period
     * @throws Zend_Exception
     */
	public static function setAutoStopPeriodGlobal($session_id, $auto_stop_period){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.SET_UP_AUTO_STOP_PERIOD(:p_session_id_in, :p_auto_stop_period)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_auto_stop_period', $auto_stop_period);
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
	
	//get current Skill Reel Rotate Speed for skill games
    /**
     * @param $session_id
     * @throws Zend_Exception
     */
	public static function currentSkillReelRotateSpeed($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.GET_SKILL_REEL_ROTATE_SPEED (:p_session_id_in, :p_reel_rotate_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$reel_rotate_value = "";
			$stmt->bindParam(':p_reel_rotate_out', $reel_rotate_value, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $reel_rotate_value;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
							  
	//set reel rotate speed for skill games
    /**
     * @param $session_id
     * @param $reel_rotate_speed
     * @throws Zend_Exception
     */
	public static function setSkillReelRotateSpeedGlobal($session_id, $reel_rotate_speed){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.SET_UP_SKILL_REEL_ROTATE_SPEED(:p_session_id_in, :p_reel_rotate_speed)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_reel_rotate_speed', $reel_rotate_speed);
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
	
	//get current buffer coefficient value
    /**
     * @param $session_id
     * @throws Zend_Exception
     */
	public static function currentBufferCoefficient($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.GET_BUFFER_COEFFICIENT(:p_session_id_in, :p_buffer_coef_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$buffer_coefficient_value = "";
			$stmt->bindParam(':p_buffer_coef_out', $buffer_coefficient_value, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $buffer_coefficient_value;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
							  
	//set buffer coefficient value
    /**
     * @param $session_id
     * @param $buffer_coefficient_value
     * @throws Zend_Exception
     */
	public static function setBufferCoefficientGlobal($session_id, $buffer_coefficient_value){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.SET_UP_BUFFER_COEFFICIENT(:p_session_id_in, :p_buffer_coef_value)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_buffer_coef_value', $buffer_coefficient_value);
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
	
	//PROCEDURE M$SET_ONLINE_CASINO ( p_online_casino_IN IN MANAGMENT_TYPES.NO%TYPE )
    /**
     * @param $status
     * @throws Zend_Exception
     */
	public static function setOnlineCasinoStatus($status){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SET_ONLINE_CASINO(:p_online_casino_IN)');
			$stmt->bindParam(':p_online_casino_IN', $status);
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
	
	//get online casino status in backoffice setup
    /**
     * @throws Zend_Exception
     */
	public static function getOnlineCasinoStatus(){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$GET_ONLINE_CASINO(:p_online_casino_out)');
			$online_casino = NO;
			$stmt->bindParam(':p_online_casino_out', $online_casino, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $online_casino;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//set number of items per page default for first time loading reports in backoffice
    /**
     * @param $session_id
     * @param $perPage
     * @return mixed
     * @throws Zend_Exception
     */
	public static function setNumberOfItemsPerPage($session_id, $perPage){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SET_UP_LINES_PER_PAGE(:p_session_id_in, :p_lines_per_page_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_lines_per_page_in', $perPage);
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
	
	//get default number of items per page in backoffice
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function numberOfItemsPerPage($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CURRENT_LINES_FOR_PAGE(:p_session_id_in, :p_LINES_FOR_PAGE_OUT)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$lines_for_page = 200;
			$stmt->bindParam(':p_LINES_FOR_PAGE_OUT', $lines_for_page, SQLT_INT);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("lines_for_page"=>$lines_for_page);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//get crosscheck payback statistic report
    /**
     * @param $session_id
     * @param $pageNo
     * @param $perPage
     * @param $orderBy
     * @param $sortOrder
     * @throws Zend_Exception
     */
	public static function crosscheckPayback($session_id, $pageNo, $perPage, $orderBy, $sortOrder){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$CROSS_CHECK_PAY_BACK(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_pay_back_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $orderBy);
			$stmt->bindParam(':p_sort_order_in', $sortOrder);			
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_pay_back_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array($table, $info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//update session type details for system
    /**
     * @param $session_id
     * @param $session_type_id
     * @param $duration_limit
     * @param $auto_close
     * @return mixed
     * @throws Zend_Exception
     */
	public static function updateSessionType($session_id, $session_type_id, $duration_limit, $auto_close){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CHANGE_SESSION_TYPE(:p_session_id_in, :p_session_type_id_in, :p_duration_limit_in, :p_auto_close_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_session_type_id_in', $session_type_id);
			$stmt->bindParam(':p_duration_limit_in', $duration_limit);
			$stmt->bindParam(':p_auto_close_in', $auto_close);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            return null;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			if($ex->getCode() == '20454'){
				return '20454';
			}else{
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				ErrorMailHelper::writeError($message, $message);
				throw new Zend_Exception($message);
			}
		}
	}
	
	//list all session types for system in backoffice
    /**
     * @param $session_id
     * @param null $session_type_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listSessionTypes($session_id, $session_type_id = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_SESSION_TYPES(:p_session_id_in, :p_session_type_id_in, :p_session_types_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_session_type_id_in', $session_type_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_session_types_list_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			if($ex->getCode() == '20454'){
				return '20454'; 
			}else{
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				ErrorMailHelper::writeError($message, $message);
				throw new Zend_Exception($message);
			}
		}		
	}
	
	//set current reprot date limit
    /**
     * @param $session_id
     * @param $report_date_limit
     * @throws Zend_Exception
     */
	public static function setReportDateLimit($session_id, $report_date_limit){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SET_UP_REPORT_DATE_LIMIT(:p_session_id_in, :p_report_date_limit)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_report_date_limit', $report_date_limit);
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
	
	//get current report date limit
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function currentReportDateLimit($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CURRENT_REPORT_DATE_LIMIT(:p_session_id_in, :p_report_date_limit_OUT)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$report_date_limit = '0';
			$stmt->bindParam(':p_report_date_limit_OUT', $report_date_limit, SQLT_CHR, 10);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("report_date_limit"=>$report_date_limit);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}