<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class CashReportModel{
	public function __construct(){
	}

	//lists cash report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $currency
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReport($session_id, $affiliate_id, $currency, $direction, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
            if($rola != ROLA_AD_COLLECTOR){
                $stmt = $dbAdapter->prepare('CALL REPORTS_BO.CASH_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_reverse_y_n, :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
            }else{
                if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
                    $start_date = $_SESSION['auth_space']['session']['last_time_collect'];
                }
                $stmt = $dbAdapter->prepare('CALL REPORTS_BO.CASH_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_reverse_y_n, :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
            }
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_aff_id_in', $affiliate_id); //affiliate for which report is generated
            $stmt->bindParam(':p_currency_in', $currency);
            $stmt->bindParam(':p_aff_id_up_level_in', $direction); //direction 0 is for UP, 1 is for DOWN
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            require_once HELPERS_DIR . DS . 'ReverseModeHelper.php';
            $is_reverse_user = ReverseModeHelper::shouldRemoveWithdrawTransactionsInCreditTransfer();
            $is_reverse_user = $is_reverse_user ? 1 : -1;
            $stmt->bindParam(':p_reverse_y_n', $is_reverse_user);
			$aff_id_out = '100000000000';
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
			return array("cursor"=>$cursor, "aff_id_out"=>$aff_id_out, "is_root"=>$is_root);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //lists cash report total report
    /**
     * @param $session_id
     * @param $currency_in
     * @param $affCurrent
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReportTotal($session_id, $currency_in, $affCurrent, $direction, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_REPORT_T(:p_session_id_in, :p_currency_in, :p_aff_id_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_REPORT_T(:p_session_id_in, :p_currency_in, :p_aff_id_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_in', $affCurrent);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$aff_id = null;
			$stmt->bindParam(':p_aff_id_in_out', $aff_id, SQLT_CHR, 255);
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
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //lists cash report total apt for subject types
    /**
     * @param $session_id
     * @param $currency_in
     * @param $aff_id
     * @param $direction
     * @param $startdate
     * @param $enddate
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReportTotalApt($session_id, $currency_in, $aff_id, $direction, $startdate, $enddate){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_REPORT_APT(:p_session_id_in, :p_currency_in, :p_aff_id_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_gw_t_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != "" ){
					$startdate = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_REPORT_APT(:p_session_id_in, :p_currency_in, :p_aff_id_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_gw_t_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction);
			$stmt->bindParam(':p_start_date_in', $startdate);
			$stmt->bindParam(':p_end_date_in', $enddate);
			$aff_id_out = '';
			$stmt->bindParam(':p_aff_id_in_out', $aff_id_out, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_cb_gw_t_out", $cursor);
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

    //lists cash report daily
    /**
     * @param $session_id
     * @param $affCurrent
     * @param $currency
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReportDaily($session_id, $affCurrent, $currency, $direction, $start_date, $end_date){
		/*
		$message = "REPORTS_BO.CASH_REPORT_DAILY(:p_session_id_in = {$session_id}, :p_aff_id_in = {$affCurrent}, :p_currency_in = {$currency} , :p_aff_id_up_level_in = 1, :p_start_date_in = {$start_date}, :p_end_date_in = {$end_date}, :p_aff_id_in_out , :list_cb_out, :p_is_root_out)";
		$helperErrorMail = new ErrorMailHelper();
		$helperErrorMail->writeError($message, $message);
		*/
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CASH_REPORT_DAILY(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_aff_id_in_out, :list_cb_out, :p_is_root_out, :p_has_integration_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] !=""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CASH_REPORT_DAILY(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_aff_id_in_out, :list_cb_out, :p_is_root_out, :p_has_integration_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affCurrent); //affiliate id to generate report for
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction); //0 to go level up, 1 to go level down
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$aff_id_out = '111111111111111111';
			$stmt->bindParam(':p_aff_id_in_out', $aff_id_out, SQLT_CHR, 255); //previous level visited - affiliate id
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cb_out', $cursor);
			$is_root = 0; //when equals to 1 don't allow to go level up
			$stmt->bindParam(':p_is_root_out', $is_root, SQLT_INT);
            $has_integration = 0;
			$stmt->bindParam(':p_has_integration_out', $has_integration, SQLT_INT);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			if($aff_id_out == '111111111111111111')$aff_id_out = null;
			return array("cursor"=>$cursor, "aff_id_out"=>$aff_id_out, "is_root"=>$is_root, "has_integration"=>$has_integration);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //list cash report per terminal
    /**
     * @param $session_id
     * @param $start_date
     * @param $end_date
     * @param $page_no
     * @param $hits_per_page
     * @param $order_by
     * @param $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReportPerTerminal($session_id, $start_date, $end_date, $page_no, $hits_per_page){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_TERMINAL_REPORT(:p_session_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_CB_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $page_no);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
            $order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
            $sort_order = "asc";
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_CB_out", $cursor);
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

    //lists credit report
    /**
     * @param $session_id
     * @param $start_date
     * @param $end_date
     * @param $currency
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listPlayerLiability($session_id, $start_date, $end_date, $currency, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		//debug here
		//$message = "REPORTS_BO.LIST_PLAYER_LIABILITY(:p_session_id_in = {$session_id}, :p_aff_id_in = {$affiliate_id}, to_date(:p_start_date_in = {$start_date}, 'DD-Mon-YYYY'), to_date(:p_end_date_in = {$end_date}, 'DD-Mon-YYYY'), :p_currency_in = {$currency}, :cur_result)";
		//$helperErrorMail = new ErrorMailHelper();
		//$helperErrorMail->writeError($message, $message);
		//var_dump("session_id = " . $session_id . " aff_current = " . $affCurrent . " currency_in = " . $currency_in . " direction = " . $direction . " start date = " . $start_date . " end_date = " . $end_date . " page number = " . $pageNumber . " per page = " . $hitsPerPage . " column = " . $columnNo . " order by = " . $orderBy);
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_PLAYER_LIABILITY(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')," . ':p_currency_in, :cur_result)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_currency_in', $currency);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':cur_result', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$result = array("cursor"=>$cursor);
			return $result;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //lists cash report total report
    /**
     * @param $session_id
     * @param $currency
     * @param $affiliate_id
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listPlayerLiabilityTotal($session_id, $currency, $affiliate_id, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_PLAYER_LIABILITY_T(:p_session_id_in, :p_aff_id_in, :p_currency_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :cur_list_liability)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.LIST_PLAYER_LIABILITY_T(:p_session_id_in, :p_aff_id_in, :p_currency_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :cur_list_liability)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":cur_list_liability", $cursor);
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

    //lists credit report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $currency_in
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @param $page_number
     * @param $per_page
     * @param string $game_type
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditReport($session_id, $affiliate_id, $currency_in, $direction, $start_date, $end_date, $page_number, $per_page, $game_type = ALL){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		//var_dump("session_id = " . $session_id . " aff_current = " . $affCurrent . " currency_in = " . $currency_in . " direction = " . $direction . " start date = " . $start_date . " end_date = " . $end_date . " page number = " . $pageNumber . " per page = " . $hitsPerPage . " column = " . $columnNo . " order by = " . $orderBy);
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CREDIT_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_version_in, :p_aff_id_in_out, :list_cb_gw_out, :p_is_root_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CREDIT_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_version_in, :p_aff_id_in_out, :list_cb_gw_out, :p_is_root_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id); //affiliate id to generate report for
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction); //0 to go level up, 1 to go level down
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $per_page);
			$stmt->bindParam(':p_version_in', $game_type);
			$aff_id_out = '111111111111111111';
			$stmt->bindParam(':p_aff_id_in_out', $aff_id_out, SQLT_CHR, 255); //previous level visited - affiliate id
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cb_gw_out', $cursor);
			$is_root = 0; //when equals to 1 don't allow to go level up
			$stmt->bindParam(':p_is_root_out', $is_root, SQLT_INT);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			if($aff_id_out == '111111111111111111')$aff_id_out = null;
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

    //lists credit report daily
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $currency_in
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @param $page_number
     * @param $per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditReportDaily($session_id, $affiliate_id, $currency_in, $direction, $start_date, $end_date, $page_number, $per_page){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$GAME_IN_OUT_AFF_REPORT_daily(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_id_in_out, :list_cb_gw_out, :p_is_root_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$GAME_IN_OUT_AFF_REPORT_daily(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_id_in_out, :list_cb_gw_out, :p_is_root_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id); //affiliate id to generate report for
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction); //0 to go level up, 1 to go level down
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $per_page);
            $columnNo = 1;
			$stmt->bindParam(':p_order_by_in', $columnNo);
            $orderBy = 'asc';
			$stmt->bindParam(':p_sort_order_in', $orderBy);
			$aff_id_out = '111111111111111111';
			$stmt->bindParam(':p_aff_id_in_out', $aff_id_out, SQLT_CHR, 255); //previous level visited - affiliate id
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cb_gw_out', $cursor);
			$is_root = 0; //when equals to 1 don't allow to go level up
			$stmt->bindParam(':p_is_root_out', $is_root, SQLT_INT);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			if($aff_id_out == '111111111111111111')$aff_id_out = null;
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

    //lists credit report total
    /**
     * @param $session_id
     * @param $currency_in
     * @param $aff_id
     * @param $direction
     * @param $startdate
     * @param $enddate
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditReportTotal($session_id, $currency_in, $aff_id, $direction, $startdate, $enddate){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$GAME_IN_OUT_AFF_REPORT_T(:p_session_id_in, :p_currency_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in, :p_aff_id_up_level_in, :list_cb_gw_t_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$startdate = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$GAME_IN_OUT_AFF_REPORT_T(:p_session_id_in, :p_currency_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in, :p_aff_id_up_level_in, :list_cb_gw_t_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_start_date_in', $startdate);
			$stmt->bindParam(':p_end_date_in', $enddate);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cb_gw_t_out', $cursor);
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

	//lists credit report for user - subject types
    /**
     * @param $session_id
     * @param $currency_in
     * @param $aff_id
     * @param $direction
     * @param $startdate
     * @param $enddate
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditReportTotalApt($session_id, $currency_in, $aff_id, $direction, $startdate, $enddate){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$GAME_IN_OUT_AFF_REPORT_APT(:p_session_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in, :p_aff_id_up_level_in, :list_cb_gw_t_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$startdate = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$GAME_IN_OUT_AFF_REPORT_APT(:p_session_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in, :p_aff_id_up_level_in, :list_cb_gw_t_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_start_date_in', $startdate);
			$stmt->bindParam(':p_end_date_in', $enddate);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cb_gw_t_out', $cursor);
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