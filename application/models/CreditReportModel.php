<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class CreditReportModel{

	public function __construct(){}

    //lists credit report
    /**
     * @param $session_id
     * @param $affCurrent
     * @param $currency_in
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @param $pageNumber
     * @param $hitsPerPage
     * @param int $columnNo
     * @param string $orderBy
     * @throws Zend_Exception
     */
	public static function listCreditReport($session_id, $affCurrent, $currency_in, $direction, $start_date, $end_date, $pageNumber, $hitsPerPage, $columnNo = 1, $orderBy = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		//var_dump("session_id = " . $session_id . " aff_current = " . $affCurrent . " currency_in = " . $currency_in . " direction = " . $direction . " start date = " . $start_date . " end_date = " . $end_date . " page number = " . $pageNumber . " per page = " . $hitsPerPage . " column = " . $columnNo . " order by = " . $orderBy);
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CREDIT_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_id_in_out, :list_cb_gw_out, :p_is_root_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CREDIT_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_id_in_out, :list_cb_gw_out, :p_is_root_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affCurrent); //affiliate id to generate report for
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction); //0 to go level up, 1 to go level down
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $pageNumber);
			$stmt->bindParam(':p_hits_per_page_in', $hitsPerPage);
			$stmt->bindParam(':p_order_by_in', $columnNo);
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
			return array("success"=>true, "table"=>$table, "info"=>$info, "affiliate_id"=>$aff_id_out, "is_root"=>$is_root);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
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
     * @throws Zend_Exception
     */
	public static function listCreditReportTotal($session_id, $currency_in, $aff_id, $direction, $startdate, $enddate){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
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
			return array("success"=>true, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
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
     * @throws Zend_Exception
     */
	public static function listCreditReportTotalApt($session_id, $currency_in, $aff_id, $direction, $startdate, $enddate){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
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
			return array("success"=>true, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //credit report details per system
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $currency
     * @param $start_date
     * @param $end_date
     * @throws Zend_Exception
     */
    public static function listCreditReportDetails($session_id, $affiliate_id, $currency, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CREDIT_REPORT_DETAILS(:p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :list_cred_details_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CREDIT_REPORT_DETAILS(:p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :list_cred_details_out)');
			}
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cred_details_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
    }

    //credit report details per system for affiliate level
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $currency
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listCreditReportDetailsForAffiliate($session_id, $affiliate_id, $currency, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.CREDIT_REPORT_DETAILS(:p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :list_cred_details_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.CREDIT_REPORT_DETAILS(:p_aff_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :list_cred_details_out)');
			}
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cred_details_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}
    }
}