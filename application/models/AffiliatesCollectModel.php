<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class AffiliatesCollectModel{
	public function __construct(){
	}

    /**
     * @param $session_id
     * @param $affCurrent
     * @param $currency_in
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @param int $pageNumber
     * @param int $hitsPerPage
     * @param int $columnNo
     * @param string $orderBy
     * @return mixed
     * @throws Zend_Exception
     */
    public static function collectCashReport($session_id, $affCurrent, $currency_in, $direction, $start_date, $end_date, $pageNumber = 1, $hitsPerPage = 200, $columnNo = 1, $orderBy = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_COLLECT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_id_in_out, :list_CB_out, :p_is_root_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affCurrent); //affiliate for which report is generated
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction); //direction 0 is for UP, 1 is for DOWN
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $pageNumber);
			$stmt->bindParam(':p_hits_per_page_in', $hitsPerPage);
			$stmt->bindParam(':p_order_by_in', $columnNo);
			$stmt->bindParam(':p_sort_order_in', $orderBy);
			$aff_id_out = "100000000000";
			$stmt->bindParam(':p_aff_id_in_out', $aff_id_out, SQLT_CHR, 255); //returns affiliate id for level visited before
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_CB_out", $cursor);
			$is_root = 0; //if this value equals 1 then don't allow to go UP LEVEL, this is root aff
			$stmt->bindParam(':p_is_root_out', $is_root, SQLT_INT);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("table"=>$table, "info"=>$info, "aff_id_out"=>$aff_id_out, "is_root"=>$is_root);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //first collection cash
    /**
     * @param $session_id
     * @param $collector_id
     * @param $collector_name
     * @param $place_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function firstCollectionAffiliate($session_id, $collector_id, $collector_name, $place_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$FIRST_COLLECTION_AFF(:p_session_id_in, :p_collector_id_in, :p_collector_name_in, :p_place_id_in, :p_is_first_out, :p_last_collection_time_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_collector_id_in', $collector_id);
			$stmt->bindParam(':p_collector_name_in', $collector_name);
			$stmt->bindParam(':p_place_id_in', $place_id);
			$is_first_out = NO;
			$stmt->bindParam(':p_is_first_out', $is_first_out, SQLT_CHR, 255);
			$last_collection_time = "01-Feb-2011 12:00:00";
			$stmt->bindParam(':p_last_collection_time_out', $last_collection_time, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("is_first_out"=>$is_first_out, "last_collection_time"=>$last_collection_time);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //collect report for affiliate
    /**
     * @param $session_id
     * @param $affCurrent
     * @param null $currency_in
     * @param $start_date
     * @param $end_date
     * @param $pageNumber
     * @param $hitsPerPage
     * @param int $columnNo
     * @param string $orderBy
     * @param string $onlyTwo
     * @return mixed
     * @throws Zend_Exception
     */
	public static function collectReportAffiliate($session_id, $affCurrent, $currency_in = null, $start_date, $end_date, $pageNumber, $hitsPerPage, $columnNo = 1, $orderBy = "asc", $onlyTwo = YES){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$COLLECT_AFF_CASH_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_only_two_in, :list_cb_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affCurrent); //affiliate for which report is generated
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $pageNumber);
			$stmt->bindParam(':p_hits_per_page_in', $hitsPerPage);
			$stmt->bindParam(':p_order_by_in', $columnNo);
			$stmt->bindParam(':p_sort_order_in', $orderBy);
			$stmt->bindParam(':p_only_two_in', $onlyTwo); //if is required to return only two last rows from report
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_cb_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("table"=>$table, "info"=>$info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //calculates remaining amount on date change in form collect cash
    /**
     * @param $session_id
     * @param $place_id
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function calculateCollectAmount($session_id, $place_id, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CALCULATE_COLLECT_AMOUNT_AFF(:p_session_id_in, :p_place_id_in, :p_start_DATE_in, :p_end_DATE_in, :p_amount_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_place_id_in', $place_id);
			$stmt->bindParam(':p_start_DATE_in', $start_date);
			$stmt->bindParam(':p_end_DATE_in', $end_date);
			$amount = 0;
			$stmt->bindParam(':p_amount_out', $amount, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("amount"=>$amount);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //total for collect report affiliate
    /**
     * @param $session_id
     * @param $affCurrent
     * @param null $currency_in
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function collectReportTotalAffiliate($session_id, $affCurrent, $currency_in = null, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$COLLECT_AFF_CASH_REPORT_T(:p_session_id_in, :p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ',:list_CB_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affCurrent); //affiliate for which report is generated
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_CB_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}