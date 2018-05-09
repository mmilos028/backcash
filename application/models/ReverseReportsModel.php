<?php
class ReverseReportsModel
{
    //setup database adapter
    public function __construct()
    {
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
	public function listCashReportTotal($session_id, $currency_in, $affCurrent, $direction, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.CASH_REPORT_WITH_SCORE_TOTAL(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_aff_id_in', $affCurrent);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$aff_id = null;
			$stmt->bindParam(':p_aff_id_in_out', $aff_id, SQLT_CHR, 255);

			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_cb_out", $cursor);
            $is_root_out = null;
			$stmt->bindParam(':p_is_root_out', $is_root_out, SQLT_CHR, 255);

			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return $cursor;
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
	public function listCashReportTotalApt($session_id, $currency_in, $aff_id, $direction, $startdate, $enddate){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_REPORT_APT(:p_session_id_in, :p_currency_in, :p_aff_id_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_gw_t_out)');
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
			return $cursor;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //lists reverse cash report with score
    /**
     * @param $session_id
     * @param $affCurrent
     * @param $currency_in
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @param int $pageNumber
     * @param int $hitsPerPage
     * @param int $is_reverse_user
     * @return mixed
     * @throws Zend_Exception
     */
	public function listReverseCashReport($session_id, $affCurrent, $currency_in, $direction, $start_date, $end_date, $pageNumber = 1, $hitsPerPage = 200){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.CASH_REPORT_WITH_SCORE(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_aff_id_in', $affCurrent); //affiliate for which report is generated
            $stmt->bindParam(':p_currency_in', $currency_in);
            $stmt->bindParam(':p_aff_id_up_level_in', $direction); //direction 0 is for UP, 1 is for DOWN
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
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
			//$help = new CursorToArrayHelper($cursor);
			//$table = $help->getTableRows();
			//$info = $help->getPageRow();
			return array("cursor"=>$cursor, "aff_id_out"=>$aff_id_out, "is_root"=>$is_root);
            //return array($table, $info, $aff_id_out, $is_root);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //reset high score on site
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
	public function resetHighScore($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.reset_high_score(:p_session_id, :p_result_out)');
            $stmt->bindParam(':p_session_id', $session_id);
            $status_out = "";
			$stmt->bindParam(':p_result_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //reset high score on site
    /**
     * @param $subject_id
     * @return array
     * @throws Zend_Exception
     */
	public function resetHighScoreForLocation($subject_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.reset_high_score_loc(:p_aff_id, :p_result_out)');
            $stmt->bindParam(':p_aff_id', $subject_id);
            $status_out = "";
			$stmt->bindParam(':p_result_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //reset high score on site
    /**
     * @param $subject_id
     * @param $date
     * @param $repeat_time
     * @return array
     * @throws Zend_Exception
     */
	public function resetHighScoreScheduled($subject_id, $date, $repeat_time){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL web_reports.set_reset_hsl_duration(:p_aff_id, :p_date_in, :p_repeat_time)');
			$stmt->bindParam(':p_aff_id', $subject_id);
            $stmt->bindParam(':p_date_in', $date);
            $stmt->bindParam(':p_repeat_time', $repeat_time);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //reset high score on site
    /**
     * @param $subject_id
     * @return array
     * @throws Zend_Exception
     */
	public function getHighScoreScheduled($subject_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL web_reports.get_aff_reset_hsl_date(:p_aff_id, :p_date_out, :p_repeat_time_out)');
			$stmt->bindParam(':p_aff_id', $subject_id);
            $date = "";
            $stmt->bindParam(':p_date_out', $date, SQLT_CHR, 255);
            $repeat_time = "";
            $stmt->bindParam(':p_repeat_time_out', $repeat_time, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "date"=>$date, "repeat_time"=>$repeat_time);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //list high scores
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $sort_method
     * @param int $page_number
     * @param int $hits_per_page
     * @return array
     * @throws Zend_Exception
     */
	public function listHighScore($session_id, $affiliate_id = null, $sort_method, $page_number = 1, $hits_per_page = 200){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.list_high_score_bo(:p_session_id, :p_affiliate_id, :p_sort_metod, :p_page_number_in, :p_hits_per_page_in, :p_high_list_out)');
            $stmt->bindParam(":p_session_id", $session_id);
            $stmt->bindParam(":p_affiliate_id", $affiliate_id);
            $stmt->bindParam(":p_sort_metod", $sort_method);
            $stmt->bindParam(":p_page_number_in", $page_number);
            $stmt->bindParam(":p_hits_per_page_in", $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_high_list_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
            $cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
            $help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("status"=>OK, "table"=>$table, "info"=>$info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();			
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
    public function showClearHighScoreForLocation($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.show_reverse(:p_session_id, :p_show_reverse_out)');
            $stmt->bindParam(":p_session_id", $session_id);
            $show_reverse = "";
			$stmt->bindParam(":p_show_reverse_out", $show_reverse, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "show_reverse"=>$show_reverse);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    //reset high score on site
    /**
     * @param $session_id
     * @param $subject_id
     * @param $currency
     * @param $amount
     * @return array
     * @throws Zend_Exception
     */
	public function resetIncomeStatistic($session_id, $subject_id, $currency, $amount){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();

        /*if(true) {
            $message = "web_reports.reset_income_statistic(:p_session_id = {$session_id}, :p_subject_id = {$subject_id}, :p_currency = {$currency}, :p_amount_in = null, :p_status_out)";
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeInfo($message, $message);
        }*/
		try{
			$stmt = $dbAdapter->prepare('CALL web_reports.reset_income_statistic(:p_session_id, :p_subject_id, :p_currency, :p_amount_in, :p_status_out)');
			$stmt->bindParam(':p_session_id', $session_id);
            $stmt->bindParam(':p_subject_id', $subject_id);
            $stmt->bindParam(':p_currency', $currency);
            $amount = null;
            $stmt->bindParam(':p_amount_in', $amount);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //reset high score on site
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
	public function getResetUserDatetime($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL web_reports.get_reset_user_datetime(:p_session_id, :p_last_reset_date_out, :p_last_reset_time_out)');
			$stmt->bindParam(':p_session_id', $session_id);
            $last_reset_date = "";
            $stmt->bindParam(':p_last_reset_date_out', $last_reset_date, SQLT_CHR, 255);
            $stmt->bindParam(':p_last_reset_time_out', $last_reset_time, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "last_reset_date"=>$last_reset_date, "last_reset_time"=>$last_reset_time);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //lists high score per affiliate level
    /**
     * @param $session_id
     * @param $subject_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function listHighScoreForAffiliates($session_id, $subject_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.get_aff_list_first_level(:p_session_id, :p_subject_id, :cur_result_out)');
            $stmt->bindParam(':p_session_id', $session_id);
            $stmt->bindParam(':p_subject_id', $subject_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":cur_result_out", $cursor);
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

    //list high scores
    /**
     * @param $session_id
     * @param $affiliate_id
     * @return array
     * @throws Zend_Exception
     */
	public function listAffiliatesWithAllLevels($session_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.get_aff_list_with_all_levels(:p_session_id, :p_subject_id, :cur_result_out)');
            $stmt->bindParam(":p_session_id", $session_id);
            $stmt->bindParam(":p_subject_id", $affiliate_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":cur_result_out", $cursor);
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
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
}