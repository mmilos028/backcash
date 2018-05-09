<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class DateTimeModel{
	public function __construct(){
	}
	
	//returns number of months limited to view reports in past
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function monthsInPast($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CURRENT_REPORT_DATE_LIMIT(:p_session_id_in, :p_report_date_limit_OUT)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$report_date_limit = 2;
			$stmt->bindParam(":p_report_date_limit_OUT", $report_date_limit, SQLT_INT);
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
	
	//returns first day in month before (previous month)
    /**
     * @return bool|string
     */
	public static function firstDayInLastMonth(){
		return date('01-M-Y', strtotime("-1 month"));
	}
	
	//returns first day in month
    /**
     * @return bool|string
     */
	public static function firstDayInMonth(){
		return date('01-M-Y', time());
	}
	
	//returns first day in date time format in month, return format 01-Jan-2015 15:15:15
    /**
     * @return bool|string
     */
	public static function firstDayDateTimeInMonth(){
		return date('01-M-Y H:i:s', time());
	}
}