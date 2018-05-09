<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class GameSessionsReportModel
{
    public function __construct()
    {
    }

    /**
     * @param $session_id
     * @param int $page_number
     * @param int $per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listGameSessionSubdetails($session_id, $page_number = 1, $per_page = 25, $order_by = 1, $sort_order = 'asc')
    {
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        //var_dump("[listGameSessionSubdetails] <br /> REPORTS.SESSION_SESSION_DETAILS <br /> <br /> session id = $session_id <br /> pageNo = $page_number <br /> perPage = $per_page <br /> orderBy = $order_by <br /> sortOrder = $sort_order");
        try {
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.GS_GAME_SESSION_DETAILS(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_details_out, :p_player_name_out, :p_aff_name_out, :p_ip_address_out, :p_country_name)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_page_number_in', $page_number);
            $stmt->bindParam(':p_hits_per_page_in', $per_page);
            $stmt->bindParam(':p_order_by_in', $order_by);
            $stmt->bindParam(':p_sort_order_in', $sort_order);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_details_out', $cursor);
            $player_name = '';
            $stmt->bindParam(':p_player_name_out', $player_name, SQLT_CHR, 255);
            $aff_name = '';
            $stmt->bindParam(':p_aff_name_out', $aff_name, SQLT_CHR, 255);
            $ip_address = '';
            $stmt->bindParam(':p_ip_address_out', $ip_address, SQLT_CHR, 255);
            $country_name = '';
            $stmt->bindParam(':p_country_name', $country_name, SQLT_CHR, 255);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            $help = new CursorToArrayHelper($cursor);
            $table = $help->getTableRows();
            $info = $help->getPageRow();
            return array("table" => $table, "info" => $info, "session_type_name" => '',
                "player_name" => $player_name, "affiliate_name" => $aff_name, "ip_address" => $ip_address,
                "country_name" => $country_name);
        } catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //lists game session subdetails (LEVEL 3) report
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGameSessionDetails($session_id, $page_number = 1, $per_page = 25){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.GS_GAME_SESSION_SUBDETAILS(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_details_out, :p_player_name_out, :p_aff_name_out, :p_ip_address_out, :p_country_name, :p_game_name)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_details_out', $cursor);
			$player_name = '';
			$stmt->bindParam(':p_player_name_out', $player_name, SQLT_CHR, 255);
			$aff_name = '';
			$stmt->bindParam(':p_aff_name_out', $aff_name, SQLT_CHR, 255);
			$ip_address = '';
			$stmt->bindParam(':p_ip_address_out', $ip_address, SQLT_CHR, 255);
			$country_name = '';
			$stmt->bindParam(':p_country_name', $country_name, SQLT_CHR, 255);
			$game_name = '';
			$stmt->bindParam(':p_game_name', $game_name, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("table"=>$table, "info"=>$info, "session_type_name"=>"",
			"player_name"=>$player_name, "affiliate_name"=>$aff_name, "ip_address"=>$ip_address,
			"country_name"=>$country_name, "game_name"=>$game_name, "is_skill"=>"");
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //list sport bet sessions integration (maxbet / betkiosk games)
    /**
     * @param $session_id
     * @param null $date_from
     * @param null $date_to
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listSportBetSessionSubdetailsReport($session_id, $date_from = null, $date_to = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        //DEBUG THIS PART
        /*
        $message = "REPORTS_BO.SPORT_BET_DETAILS_REPORT(:p_game_sess_id = {$session_id}, :p_date_from = {$date_from}, :p_date_to = {$date_to}, :p_list_out)";
		$helperErrorMail = new ErrorMailHelper();
		$helperErrorMail->writeInfo($message, $message);
        */
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.SPORT_BET_DETAILS_REPORT(:p_game_sess_id, :p_date_from, :p_date_to, :p_list_out)');
			$stmt->bindParam(':p_game_sess_id', $session_id);
			$stmt->bindParam(':p_date_from', $date_from);
			$stmt->bindParam(':p_date_to', $date_to);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_out", $cursor);
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
			throw new Zend_Exception($message);
		}
    }

    //lists game session subdetails (LEVEL 3) report for skill games
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGameSessionSkillDetails($session_id, $page_number = 1, $per_page = 25){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.GS_GAME_SESSION_SUBDET_SKILL(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_details_out, :p_player_name_out, :p_aff_name_out, :p_ip_address_out, :p_country_name, :p_game_name)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_page_number_in', $page_number);
            $stmt->bindParam(':p_hits_per_page_in', $per_page);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_details_out', $cursor);
            $player_name = '';
            $stmt->bindParam(':p_player_name_out', $player_name, SQLT_CHR, 255);
            $aff_name = '';
            $stmt->bindParam(':p_aff_name_out', $aff_name, SQLT_CHR, 255);
            $ip_address = '';
            $stmt->bindParam(':p_ip_address_out', $ip_address, SQLT_CHR, 255);
            $country_name = '';
            $stmt->bindParam(':p_country_name', $country_name, SQLT_CHR, 255);
            $game_name = '';
            $stmt->bindParam(':p_game_name', $game_name, SQLT_CHR, 255);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            $help = new CursorToArrayHelper($cursor);
            $table = $help->getTableRows();
            $info = $help->getPageRow();
            return array("table"=>$table, "info"=>$info, "session_type_name"=>"",
                "player_name"=>$player_name, "affiliate_name"=>$aff_name, "ip_address"=>$ip_address,
                "country_name"=>$country_name, "game_name"=>$game_name);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

     //lists game session subdetails (LEVEL 3) report for mystery skill games
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGameSessionMysterySkillDetails($session_id, $page_number = 1, $per_page = 25){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();

        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.GS_GAME_SESSION_SUBDET_MYST(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_details_out, :p_player_name_out, :p_aff_name_out, :p_ip_address_out, :p_country_name, :p_game_name)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_page_number_in', $page_number);
            $stmt->bindParam(':p_hits_per_page_in', $per_page);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_details_out', $cursor);
            $player_name = '';
            $stmt->bindParam(':p_player_name_out', $player_name, SQLT_CHR, 255);
            $aff_name = '';
            $stmt->bindParam(':p_aff_name_out', $aff_name, SQLT_CHR, 255);
            $ip_address = '';
            $stmt->bindParam(':p_ip_address_out', $ip_address, SQLT_CHR, 255);
            $country_name = '';
            $stmt->bindParam(':p_country_name', $country_name, SQLT_CHR, 255);
            $game_name = '';
            $stmt->bindParam(':p_game_name', $game_name, SQLT_CHR, 255);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            $help = new CursorToArrayHelper($cursor);
            $table = $help->getTableRows();
            $info = $help->getPageRow();
            return array("table"=>$table, "info"=>$info, "session_type_name"=>"",
                "player_name"=>$player_name, "affiliate_name"=>$aff_name, "ip_address"=>$ip_address,
                "country_name"=>$country_name, "game_name"=>$game_name);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //list all affiliates players sessions
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $start_date
     * @param $end_date
     * @param $game_id
     * @param $player_id
     * @param $pageNo
     * @param $hitsPerPage
     * @param $order
     * @param $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function playerSessionsAll($session_id, $affiliate_id, $start_date, $end_date, $game_id, $player_id, $pageNo, $hitsPerPage, $order, $sort_order){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//var_dump("[playerSessionsAll] <br /> AFF_REPORTS.M_LIST_GAME_SESSIONS <br /> <br /> session id = $session_id <br /> affiliate_id = $affiliate_id <br /> start_date = $start_date <br /> end_date = $end_date <br /> game_id = $game_id <br /> player_id = $player_id <br /> pageNo = $pageNo <br /> perPage = $hitsPerPage <br /> orderBy = $order <br /> sortOrder = $sort_order");
			$stmt = $dbAdapter->prepare('CALL AFF_REPORTS.M$LIST_GAME_SESSIONS(:p_session_id_in, :p_aff_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_game_id_in, :p_player_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_players_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $hitsPerPage);
			$stmt->bindParam(':p_order_by_in', $order);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_players_list_out", $cursor);
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

    //list game session subdetails total for skill
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listGameSessionSkillSubdetailsTotal($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.GS_GAME_SESSION_SUBDET_SKILL_T(:p_session_id_in, :p_details_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_details_out', $cursor);
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