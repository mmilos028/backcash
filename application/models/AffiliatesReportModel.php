<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class AffiliatesReportModel{
	public function __construct(){
	}

	//list game analytics report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $start_date
     * @param $end_date
     * @param $page
     * @param $hits_per_page
     * @param $sort_column
     * @param $asc_desc
     * @param string $currency
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getGameAnalytics($session_id, $affiliate_id, $start_date, $end_date, $page, $hits_per_page, $sort_column = 1, $asc_desc = 'asc', $currency = ALL){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$GAMES_ANALYTICS(:p_session_id_in, :p_aff_id_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_start_date_out, 'DD-Mon-YYYY')" . ', :p_page_in, :p_hits_per_page_in, :p_sort_column, :p_asc_desc_in, :p_currency_in, :p_list_game_stat)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_start_date_out', $end_date);
			$stmt->bindParam(':p_page_in', $page);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_sort_column', $sort_column);
			$stmt->bindParam(':p_asc_desc_in', $asc_desc);
			$stmt->bindParam(':p_currency_in', $currency);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_game_stat', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("cursor"=>$cursor);
		}catch(Zend_Db_Statement_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
            throw $ex1;
		}catch(Zend_Db_Cursor_Exception $ex3){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex3);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
            throw $ex3;
		}catch(Zend_Exception $ex4){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex4);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw $ex4;
		}
	}

	//list game analytics details report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $game_id
     * @param $currency
     * @param $start_date
     * @param $end_date
     * @return array
     * @throws Zend_Exception
     */
	public static function getGameAnalyticsST($session_id, $affiliate_id, $game_id, $currency, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$GAMES_ANALYTICS_ST(:p_session_id_in, :p_aff_id_in, :p_game_id_in, :p_currency_in, :p_gamble_y_n_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_start_date_out, 'DD-Mon-YYYY')" . ', :p_list_game_stat_t)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$gamble_y_n = null;
			$stmt->bindParam(':p_gamble_y_n_in', $gamble_y_n);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_start_date_out', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_game_stat_t', $cursor);
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
            throw $ex;
		}
	}

	//list game analytics total report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $start_date
     * @param $end_date
     * @param string $currency_in
     * @return array
     * @throws Zend_Exception
     */
	public static function getGameAnalyticsTotal($session_id, $affiliate_id, $start_date, $end_date, $currency_in = ALL){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$GAMES_ANALYTICS_T(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_start_date_out, 'DD-Mon-YYYY')" . ', :p_currency_in, :p_list_game_stat_t)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_start_date_out', $end_date);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_game_stat_t', $cursor);
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
            throw $ex;
		}
	}

    //list subgrid details for game statistics for affiliate details
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $game_id
     * @param $currency
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Exception
     * @throws Zend_Exception
     */
	public static function getGameStatisticsSt($session_id, $affiliate_id, $game_id, $currency, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$GAMES_STATISTICS_ST(:p_session_id_in, :p_aff_id_in, :p_game_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_start_date_out, 'DD-Mon-YYYY')" . ', :p_list_game_stat_t)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_start_date_out', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_game_stat_t", $cursor);
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
			throw $ex;
		}
	}

	//game statistics for choosen affiliate details
	//list game statistics report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $start_date
     * @param $end_date
     * @param $page
     * @param $hits_per_page
     * @param $sort_column
     * @param $asc_desc
     * @return mixed
     * @throws Exception
     * @throws Zend_Exception
     */
	public static function getGameStatistics($session_id, $affiliate_id, $start_date, $end_date, $page, $hits_per_page, $sort_column, $asc_desc){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$GAMES_STATISTICS(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_start_date_out, 'DD-Mon-YYYY')" . ', :p_page_in, :p_hits_per_page_in, :p_sort_column, :p_asc_desc_in, :p_list_game_stat)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_start_date_out', $end_date);
			$stmt->bindParam(':p_page_in', $page);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_sort_column', $sort_column);
			$stmt->bindParam(':p_asc_desc_in', $asc_desc);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_game_stat", $cursor);
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
            throw $ex;
		}
	}

    //list game statistics total report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Exception
     * @throws Zend_Exception
     */
	public static function getGameStatisticsTotal($session_id, $affiliate_id, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$GAMES_STATISTICS_T(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_start_date_out, 'DD-Mon-YYYY')" . ', :p_list_game_stat_t)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_start_date_out', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_game_stat_t", $cursor);
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
            throw $ex;
		}
	}

    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $game_id
     * @param $currency
     * @param $start_date
     * @param $end_date
     * @param $bonus_type
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getGameStatisticsBonusSessionsDetails($session_id, $affiliate_id, $game_id, $currency, $start_date, $end_date, $bonus_type){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL AFF_REPORTS.GS_BONUS_DETAILS(:p_session_id_in, :p_aff_id_in, :p_game_id_in, :p_currency_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_start_date_out, 'DD-Mon-YYYY')" . ', :p_bonus_type_in, :p_list_bonuses)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $stmt->bindParam(':p_game_id_in', $game_id);
            $stmt->bindParam(':p_currency_in', $currency);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_start_date_out', $end_date);
            $stmt->bindParam(':p_bonus_type_in', $bonus_type);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_list_bonuses', $cursor);
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
            throw $ex;
        }
    }

    //list gamble sessions for selected subaffiliate
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGambleSessions($session_id, $affiliate_id, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_GAMBLE_SESSIONS(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_list_gamble_sessions)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_gamble_sessions", $cursor);
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
			throw $ex;
		}
	}

    /**
     * @param $session_id
     * @param $start_date
     * @param $end_date
     * @param $currency
     * @param $game_name
     * @param $gamble_type
     * @param $affiliate_id
     * @param $direction
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getListGambleAnalysis($session_id, $start_date, $end_date, $currency, $game_name, $gamble_type, $affiliate_id, $direction){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        /*
        $message = "AFF_REPORTS.GA_LIST_GAMBLE_SESSIONS(p_session_id_in = {$session_id}, p_start_date_in = {$start_date}, p_end_date_in = {$end_date}, p_currency_in = {$currency},
        p_game_name_in = {$game_name}, p_gamble_type_in = {$gamble_type}, p_aff_id_in = {$affiliate_id}, p_aff_id_up_level_in = {$direction}, p_aff_id_in_out, p_list_gamble_sessions, p_is_root_out)";
        $helperErrorMail = new ErrorMailHelper();
        $helperErrorMail->writeError($message, $message);
        */
        try{
            $stmt = $dbAdapter->prepare('CALL AFF_REPORTS.GA_LIST_GAMBLE_SESSIONS(:p_session_id_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_currency_in, :p_game_name_in, :p_gamble_type_in, :p_aff_id_in, :p_aff_id_up_level_in, :p_aff_id_in_out, :p_list_gamble_sessions, :p_is_root_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            $stmt->bindParam(':p_currency_in', $currency);
            $stmt->bindParam(':p_game_name_in', $game_name);
            $stmt->bindParam(':p_gamble_type_in', $gamble_type);
            $stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $stmt->bindParam(':p_aff_id_up_level_in', $direction);
            $affiliate_id_out = "";
            $stmt->bindParam(':p_aff_id_in_out', $affiliate_id_out, SQLT_CHR, 255);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_list_gamble_sessions', $cursor);
            $is_root = 0; //when equals to 1 don't allow to go level up
            $stmt->bindParam(':p_is_root_out', $is_root, SQLT_INT);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            if($affiliate_id_out == '111111111111111111')$affiliate_id_out = null;
            return array("cursor"=>$cursor);
            //$help = new CursorToArrayHelper($cursor);
            //$table = $help->getTableRows();
            //$info = $help->getPageRow();
            //$result = array($table, $info, $aff_id_out, $is_root);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw $ex;
        }
    }

    //lists cash report
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $currency_in
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @param int $pageNumber
     * @param int $hitsPerPage
     * @param int $columnNo
     * @param string $orderBy
     * @throws Exception
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReport($session_id, $affiliate_id, $currency_in, $direction, $start_date, $end_date, $pageNumber = 1, $hitsPerPage = 200, $columnNo = 1, $orderBy = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
            if($rola != ROLA_AD_COLLECTOR){
                $stmt = $dbAdapter->prepare('CALL REPORTS_BO.CASH_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
            }else{
                if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
                    $start_date = $_SESSION['auth_space']['session']['last_time_collect'];
                }
                $stmt = $dbAdapter->prepare('CALL REPORTS_BO.CASH_REPORT(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
            }
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_aff_id_in', $affiliate_id); //affiliate for which report is generated
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
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw $ex;
		}
	}

    //lists cash report total report
    /**
     * @param $session_id
     * @param $currency_in
     * @param $affiliate_id
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReportTotal($session_id, $currency_in, $affiliate_id, $direction, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.CASH_IN_OUT_AFF_REPORT_T(:p_session_id_in, :p_currency_in, :p_aff_id_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.CASH_IN_OUT_AFF_REPORT_T(:p_session_id_in, :p_currency_in, :p_aff_id_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$affiliate_id_out = null;
			$stmt->bindParam(':p_aff_id_in_out', $affiliate_id_out, SQLT_CHR, 255);
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
			throw $ex;
		}
	}

    //lists cash report total apt for subject types
    /**
     * @param $session_id
     * @param $currency_in
     * @param $affiliate_id
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReportTotalApt($session_id, $currency_in, $affiliate_id, $direction, $start_date, $end_date){
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
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_REPORT_APT(:p_session_id_in, :p_currency_in, :p_aff_id_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in_out, :list_cb_gw_t_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$affiliate_id_out = '';
			$stmt->bindParam(':p_aff_id_in_out', $affiliate_id_out, SQLT_CHR, 255);
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
			throw $ex;
		}
	}

    //lists cash report daily
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $currency_in
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @param $pageNumber
     * @param $hitsPerPage
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCashReportDaily($session_id, $affiliate_id, $currency_in, $direction, $start_date, $end_date, $pageNumber, $hitsPerPage, $columnNo = 1, $orderBy = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_REP_DAILY(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] !=""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$CASH_IN_OUT_AFF_REP_DAILY(:p_session_id_in, :p_aff_id_in, :p_currency_in, :p_aff_id_up_level_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY hh24:mi:ss')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_id_in_out, :list_cb_out, :p_is_root_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id); //affiliate id to generate report for
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_aff_id_up_level_in', $direction); //0 to go level up, 1 to go level down
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $pageNumber);
			$stmt->bindParam(':p_hits_per_page_in', $hitsPerPage);
			$stmt->bindParam(':p_order_by_in', $columnNo);
			$stmt->bindParam(':p_sort_order_in', $orderBy);
			$affiliate_id_out = '111111111111111111';
			$stmt->bindParam(':p_aff_id_in_out', $affiliate_id_out, SQLT_CHR, 255); //previous level visited - affiliate id
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cb_out', $cursor);
			$is_root = 0; //when equals to 1 don't allow to go level up
			$stmt->bindParam(':p_is_root_out', $is_root, SQLT_INT);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			if($affiliate_id_out == '111111111111111111')$aff_id_out = null;
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("table"=>$table, "info"=>$info, "affiliate_id_out"=>$affiliate_id_out, "is_root"=>$is_root);
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
     * @param $pageNumber
     * @param $hitsPerPage
     * @param int $columnNo
     * @param string $orderBy
     * @param string $game_type
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditReport($session_id, $affiliate_id, $currency_in, $direction, $start_date, $end_date, $pageNumber, $hitsPerPage, $columnNo = 1, $orderBy = 'asc', $game_type = ALL){
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
			$stmt->bindParam(':p_page_number_in', $pageNumber);
			$stmt->bindParam(':p_hits_per_page_in', $hitsPerPage);
			$stmt->bindParam(':p_version_in', $game_type);
			$affiliate_id_out = '111111111111111111';
			$stmt->bindParam(':p_aff_id_in_out', $affiliate_id_out, SQLT_CHR, 255); //previous level visited - affiliate id
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_cb_gw_out', $cursor);
			$is_root = 0; //when equals to 1 don't allow to go level up
			$stmt->bindParam(':p_is_root_out', $is_root, SQLT_INT);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			if($affiliate_id_out == '111111111111111111')$affiliate_id_out = null;
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("table"=>$table, "info"=>$info, "affiliate_id_out"=>$affiliate_id_out, "is_root"=>$is_root);
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
     * @param $affiliate_id
     * @param $direction
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditReportTotal($session_id, $currency_in, $affiliate_id, $direction, $start_date, $end_date){
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
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$GAME_IN_OUT_AFF_REPORT_T(:p_session_id_in, :p_currency_in, ' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in, :p_aff_id_up_level_in, :list_cb_gw_t_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
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
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditReportTotalApt($session_id, $currency_in, $aff_id, $direction, $start_date, $end_date){
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
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$GAME_IN_OUT_AFF_REPORT_APT(:p_session_id_in, :p_currency_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_aff_id_in, :p_aff_id_up_level_in, :list_cb_gw_t_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency_in);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
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

    //lists credit report daily
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
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditReportDaily($session_id, $affCurrent, $currency_in, $direction, $start_date, $end_date, $pageNumber, $hitsPerPage, $columnNo = 1, $orderBy = 'asc'){
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
			return array("table"=>$table, "info"=>$info, "affiliate_id_out"=>$aff_id_out, "is_root"=>$is_root);
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