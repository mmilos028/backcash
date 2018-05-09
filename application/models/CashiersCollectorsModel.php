<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class CashiersCollectorsModel{
	public function __construct(){
	}
	
	//print end shift form values cashier Strong
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function calculateCollectAmountEndShift($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CALC_COLL_AMOUNT_SHIFT_END(:p_session_id_in, :p_amount_out, :p_cash_in_out, :p_cash_out_out, :p_amount_out_s, :p_cash_in_out_s, :p_cash_out_out_s, :p_start_time_out_s, :p_start_amount_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$amount = "0.00";
			$stmt->bindParam(':p_amount_out', $amount, SQLT_CHR, 255);
			$cash_in = "0.00";
			$stmt->bindParam(':p_cash_in_out', $cash_in, SQLT_CHR, 255);
			$cash_out = "0.00";
			$stmt->bindParam(':p_cash_out_out', $cash_out, SQLT_CHR, 255);
			$amount_s = "0.00";
			$stmt->bindParam(':p_amount_out_s', $amount_s, SQLT_CHR, 255);
			$cash_in_s = "0.00";
			$stmt->bindParam(':p_cash_in_out_s', $cash_in_s, SQLT_CHR, 255);
			$cash_out_s = "0.00";
			$stmt->bindParam(':p_cash_out_out_s', $cash_out_s, SQLT_CHR, 255);
			$start_time_s = "";
			$stmt->bindParam(':p_start_time_out_s', $start_time_s, SQLT_CHR, 255);
			$start_amount = "";
			$stmt->bindParam(':p_start_amount_out', $start_amount, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			/*
			require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
			$errorMailHelper = new ErrorMailHelper();
			$errorMailHelper->sendMail("MANAGMENT_CORE.M_DOLAR_CALC_COLL_AMOUNT_SHIFT_END(p_session_id_in = $session_id, p_amount_out = $amount, p_cash_in_out = $cash_in,
			p_cash_out_out = $cash_out, p_amount_out_s = $amount_s, p_cash_in_out_s = $cash_in_s, p_cash_out_out_s = $cash_out_s, 
			p_start_time_out_s = $start_time_s, p_start_amount_out = $start_amount)");
			*/
			return array("amount"=>$amount, "cash_in"=>$cash_in, "cash_out"=>$cash_out, "amount_s"=>$amount_s, "cash_in_s"=>$cash_in_s, "cash_out_s"=>$cash_out_s, "start_time_s"=>$start_time_s, "start_amount"=>$start_amount);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//starts or ends shift for shift cashier Strong
    /**
     * @param $session_id
     * @param $shift_collector_id
     * @param $cash_in
     * @param $cash_out
     * @param $balance
     * @param $currency
     * @param $type_name
     * @param $shift_start_time
     * @param null $comment
     * @param null $amount
     * @return mixed
     * @throws Zend_Exception
     */
	public static function startEndShift($session_id, $shift_collector_id, $cash_in, $cash_out, $balance, $currency, $type_name, $shift_start_time, $comment = null, $amount = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$START_END_SHIFT(:p_session_id_in, :p_shiftcollector_in, :p_cash_in_in, :p_cash_out_in, :p_balance_in, :p_currency_in, :p_start_end_shift, :p_shift_start_time_out, :p_comment_in, :p_amount_in, :p_start_time_out, :p_duration_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_shiftcollector_in', $shift_collector_id);
			$stmt->bindParam(':p_cash_in_in', $cash_in);
			$stmt->bindParam(':p_cash_out_in', $cash_out);
			$stmt->bindParam(':p_balance_in', $balance);
			$stmt->bindParam(':p_currency_in', $currency);
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$subject_type = SubjectTypesModel::getSubjectType($type_name);
            $subject_type = $subject_type['value'];
			$stmt->bindParam(':p_start_end_shift', $subject_type);
			$stmt->bindParam(':p_shift_start_time_out', $shift_start_time);
			$stmt->bindParam(':p_comment_in', $comment);
			$stmt->bindParam(':p_amount_in', $amount);
			$start_time = "";
			$stmt->bindParam(':p_start_time_out', $start_time, SQLT_CHR, 255);
			$duration_out = "";
			$stmt->bindParam(':p_duration_out', $duration_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			/*
			require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
			$errorMailHelper = new ErrorMailHelper();
			$errorMailHelper->sendMail("MANAGMENT_CORE.M_DOLAR_START_END_SHIFT(p_session_id_in = $session_id, p_shiftcollector_in = $shift_collector_id, p_cash_in_in = $cash_in,
			p_cash_out_in = $cash_out, p_balance_in = $balance, p_currency_in = $currency,
			p_start_end_shift = $subject_type ($type_name), p_shift_start_time_out = $shift_start_time, p_comment_in = $comment, 
			p_amount_in = $amount, p_start_time_out = , p_duration_out = )");
			*/
			return array("start_time"=>$start_time, "duration_out"=>$duration_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//calculate collect amount for shifts
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function calculateCollectAmountShift($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CALC_COLL_AMOUNT_SHIFT(:p_session_id_in, :p_amount_out, :p_cash_in_out, :p_cash_out_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$amount = "0.00";
			$stmt->bindParam(':p_amount_out', $amount, SQLT_CHR, 255);
			$cash_in = "0.00";
			$stmt->bindParam(':p_cash_in_out', $cash_in, SQLT_CHR, 255);
			$cash_out = "0.00";
			$stmt->bindParam(':p_cash_out_out', $cash_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("amount"=>$amount, "cash_in"=>$cash_in, "cash_out"=>$cash_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//check open shifts - returns Y if open shift or returns N if shift is closed
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function checkOpenShifts($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CHECK_OPEN_SHIFTS(:p_session_id_in, :p_y_n_out, :p_start_time_out, :p_start_amount)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$y_n_out = NO;
			$stmt->bindParam(':p_y_n_out', $y_n_out, SQLT_CHR, 255);	
			$start_time_out = "";
			$stmt->bindParam(':p_start_time_out', $start_time_out, SQLT_CHR, 255);
			$start_amount = "";
			$stmt->bindParam(':p_start_amount', $start_amount, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("shift_status"=>$y_n_out, "shift_start_time"=>$start_time_out, "start_amount"=>$start_amount);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//total for collect report
    /**
     * @param $session_id
     * @param $affCurrent
     * @param null $currency_in
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function collectReportTotal($session_id, $affCurrent, $currency_in = null, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$COLLECT_PLACE_CASH_REPORT_T(:p_session_id_in, :p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ',:list_cb_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affCurrent); //affiliate for which report is generated
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_cb_out", $cursor);
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//collect place cash report - lists all cash collecting in date range
    /**
     * @param $session_id
     * @param $affCurrent
     * @param null $currency_in
     * @param $start_date
     * @param $end_date
     * @param $pageNumber
     * @param $hitsPerPage
     * @param $columnNo
     * @param $orderBy
     * @param string $onlyTwo
     * @return mixed
     * @throws Zend_Exception
     */
	public static function collectReport($session_id, $affCurrent, $currency_in = null, $start_date, $end_date, $pageNumber, $hitsPerPage, $columnNo, $orderBy, $onlyTwo = YES){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$COLLECT_PLACE_CASH_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_only_two_in, :list_cb_out)');
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//collects cash from collectors place
    /**
     * @param $session_id
     * @param $collector_id
     * @param $place_id
     * @param $amount
     * @param $start_date
     * @param $end_date
     * @param $transaction_name
     * @param null $currency
     * @return mixed
     * @throws Zend_Exception
     */
	public static function doCollectCashAmount($session_id, $collector_id, $place_id, $amount, $start_date, $end_date, $transaction_name, $currency = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$COLLECT_CASH(:p_session_id_in, :p_collector_id_in, :p_place_id_in, :p_amount_in, :p_start_time_in, :p_end_time_in, :p_transaction_type_id, :p_currency_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_collector_id_in', $collector_id);
			$stmt->bindParam(':p_place_id_in', $place_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_start_time_in', $start_date);
			$stmt->bindParam(':p_end_time_in', $end_date);
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$transaction_type_id = SubjectTypesModel::getSubjectType($transaction_name);
            $transaction_type_id = $transaction_type_id['value'];
			$stmt->bindParam(':p_transaction_type_id', $transaction_type_id);
			$stmt->bindParam(':p_currency_in', $currency);
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
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CALCULATE_COLLECT_AMOUNT(:p_session_id_in, :p_place_id_in, :p_start_date_in, :p_end_date_in, :p_amount_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_place_id_in', $place_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$amount = 0.00;
			$stmt->bindParam(':p_amount_out', $amount, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("amount"=>$amount);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
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
	public static function firstCollection($session_id, $collector_id, $collector_name, $place_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$FIRST_COLLECTION(:p_session_id_in, :p_collector_id_in, :p_collector_name_in, :p_place_id_in, :p_is_first_out, :p_last_collection_time_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_collector_id_in', $collector_id);
			$stmt->bindParam(':p_collector_name_in', $collector_name);
			$stmt->bindParam(':p_place_id_in', $place_id);
			$is_first_out = NO;
			$stmt->bindParam(':p_is_first_out', $is_first_out, SQLT_CHR, 5);
			$last_collection_time = "01-Feb-2011 12:00:00";
			$stmt->bindParam(':p_last_collection_time_out', $last_collection_time, SQLT_CHR, 30);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("is_first"=>$is_first_out, "last_collection_time"=>$last_collection_time);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//cash report for cashiers collectors - lists affiliates and local places with last date of collection and collected amount
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
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$COLLECT_CASH_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
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
			$stmt->bindCursor(":list_cb_out", $cursor);
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
			return array("table"=>$table, "info"=>$info, "affiliate_id"=>$aff_id_out, "is_root"=>$is_root);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//returns player details in backoffice
    /**
     * @param $session_id
     * @param $subject_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getCashierCollectorDetails($session_id, $subject_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SUBJECT_DETAIL(:p_session_id_in, :p_subject_id_in, :p_subject_detail_out)');
			$stmt->bindParam(":p_session_id_in", $session_id);
			$stmt->bindParam(":p_subject_id_in", $subject_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_subject_detail_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("details"=>$cursor->current());
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	
	//lists cashiers collectors
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param $subtype_name
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashiersCollectors($session_id, $page_number = 1, $hits_per_page = 50, $subtype_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_ADMINISTRATORS(:p_session_id_in, :p_subtype_name_in, :p_page_number_in, :p_hits_per_page_in, :p_adm_list_out)');
			$stmt->bindParam(":p_session_id_in", $session_id);
			$stmt->bindParam(":p_subtype_name_in", $subtype_name);
			$stmt->bindParam(":p_page_number_in", $page_number);
			$stmt->bindParam(":p_hits_per_page_in", $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_adm_list_out", $cursor);
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	
	//searches cashiers collectors
    /**
     * @param $session_id
     * @param int $pageNo
     * @param int $perPage
     * @param int $column
     * @param string $order
     * @param null $username
     * @param null $first_name
     * @param null $last_name
     * @param null $city
     * @param null $country
     * @param null $parent_aff
     * @param string $show_banned
     * @param $subject_type
     * @return mixed
     * @throws Zend_Exception
     */
	public static function search($session_id, $pageNo = 1, $perPage = 25, $column = 1, $order = 'asc', $username = null, $first_name = null, $last_name = null, $city = null, $country = null, $parent_aff = null, $show_banned = YES, $subject_type){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$SEARCH_USERS(:p_session_id_in, :p_user_name_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_country_in, :p_parent_aff_in, :p_currency_in, :p_banned_in, :p_subject_type_name, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_users_out)');
			$stmt->bindParam(":p_session_id_in", $session_id);
			$stmt->bindParam(":p_user_name_in", $username);
			$stmt->bindParam(":p_first_name_in", $first_name);
			$stmt->bindParam(":p_last_name_in", $last_name);
			$stmt->bindParam(":p_city_in", $city);
			$stmt->bindParam(":p_country_in", $country);
			$stmt->bindParam(":p_parent_aff_in", $parent_aff);
			$currency = ALL;
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(":p_banned_in", $show_banned);
			$stmt->bindParam(":p_subject_type_name", $subject_type);
			$stmt->bindParam(":p_page_number_in", $pageNo);
			$stmt->bindParam(":p_hits_per_page_in", $perPage);
			$stmt->bindParam(":p_order_by_in", $column);
			$stmt->bindParam(":p_sort_order_in", $order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_users_out", $cursor);
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
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}