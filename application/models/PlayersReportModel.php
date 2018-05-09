<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class PlayersReportModel
{
    public function __construct()
    {
    }

    //list games for drowpdown filter list
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGames($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_GAMES(:p_session_id_in, :p_games_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_games_list_out', $cursor);
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

    //list player history total
    /**
     * @param $player_name
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listPlayerHistoryTotal($player_name, $start_date, $end_date)
    {
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try {
            $rola = $_SESSION['auth_space']['session']['subject_type_name'];
            $stmt = "";
            if ($rola != ROLA_AD_COLLECTOR) {
                $stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_PLAYER_HISTORY_T(:p_player_name_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_players_sum_list_out)');
            } else {
                if ($_SESSION['auth_space']['session']['last_time_collect'] != "") {
                    $start_date = $_SESSION['auth_space']['session']['last_time_collect'];
                }
                $stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_PLAYER_HISTORY_T(:p_player_name_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_players_sum_list_out)');
            }
            $stmt->bindParam(':p_player_name_in', $player_name);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_players_sum_list_out', $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            return array("cursor"=>$cursor);
        } catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $helperErrorMail = new ErrorMailHelper();
            $helperErrorMail->writeError($message, $message);
            throw new Zend_Exception($message);
        }
    }

    //list credit transfer report
    /**
     * @param $session_id
     * @param $start_date
     * @param $end_date
     * @param $page_number
     * @param $hits_per_page
     * @param $order_by
     * @param $sort_order
     * @param $transaction_type_name
     * @param $subject_name
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getListCreditTransfers($session_id, $start_date, $end_date, $page_number, $hits_per_page, $order_by, $sort_order, $transaction_type_name, $subject_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = "";
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_CREDIT_TRANSFERS(:p_session_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_transaction_types_name_in, :p_subject_name_in, :p_list_transactions_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_date = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_CREDIT_TRANSFERS(:p_session_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_transaction_types_name_in, :p_subject_name_in, :p_list_transactions_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_start_time_in', $start_date);
			$stmt->bindParam(':p_end_time_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindParam(':p_transaction_types_name_in', $transaction_type_name);
			$stmt->bindParam(':p_subject_name_in', $subject_name);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_transactions_out', $cursor);
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

    //list credit transfer total report
    /**
     * @param $session_id
     * @param $start_time
     * @param $end_time
     * @param $transaction_type_name
     * @param $subject_name
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listCreditTransferTotal($session_id, $start_time, $end_time, $transaction_type_name, $subject_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//if is with role of collector send startdate with time
			$rola = $_SESSION['auth_space']['session']['subject_type_name'];
			$stmt = "";
			if($rola != ROLA_AD_COLLECTOR){
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_CREDIT_TRANSFERS_T(:p_session_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ',:p_transaction_types_name_in, :p_subject_name_in, :p_list_transactions_t_out)');
			}else{
				if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
					$start_time = $_SESSION['auth_space']['session']['last_time_collect'];
				}
				$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_CREDIT_TRANSFERS_T(:p_session_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ',:p_transaction_types_name_in, :p_subject_name_in, :p_list_transactions_t_out)');
			}
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_start_time_in', $start_time);
			$stmt->bindParam(':p_end_time_in', $end_time);
			$stmt->bindParam(':p_transaction_types_name_in', $transaction_type_name);
			$stmt->bindParam(':p_subject_name_in', $subject_name);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_transactions_t_out', $cursor);
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

    //list transaction types
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listTransactionTypes($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_TRANSACTION_TYPES(:p_session_id_in, :list_transaction_types)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_transaction_types', $cursor);
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

    //list game sessions report
    /**
     * @param $session_id
     * @param $start_date
     * @param $end_date
     * @param int $game_id
     * @param int $player_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getListGameSessions($session_id, $start_date, $end_date, $game_id = 0, $player_id = 0, $page_number = 1, $hits_per_page = 50, $order_by, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        //var_dump("[getListGameSessions] <br /> REPORTS.M_LIST_GAME_SESSIONS <br /> <br /> session id = $session_id start_date = $start_date <br /> end_date = $end_date <br /> game_id = $game_id <br /> pageNo = $page_number <br /> perPage = $per_page <br /> orderBy = $order_by <br /> sortOrder = $sort_order");
        try{
            $rola = $_SESSION['auth_space']['session']['subject_type_name'];
            if($rola != ROLA_AD_COLLECTOR){
                $stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_GAME_SESSIONS(:p_session_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_game_id_in, :p_player_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_players_list_out)');
            }else{
                if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
                    $start_date = $_SESSION['auth_space']['session']['last_time_collect'];
                }
                $stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_GAME_SESSIONS(:p_session_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_game_id_in, :p_player_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_players_list_out)');
            }
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            $stmt->bindParam(':p_game_id_in', $game_id);
            $stmt->bindParam(':p_player_id_in', $player_id);
            $stmt->bindParam(':p_page_number_in', $page_number);
            $stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
            $stmt->bindParam(':p_order_by_in', $order_by);
            $stmt->bindParam(':p_sort_order_in', $sort_order);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_players_list_out', $cursor);
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

    //list game sessions total report
    /**
     * @param $session_id
     * @param $start_date
     * @param $end_date
     * @param $game_id
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getListGameSessionsTotal($session_id, $start_date, $end_date, $game_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $rola = $_SESSION['auth_space']['session']['subject_type_name'];
            if($rola != ROLA_AD_COLLECTOR){
                $stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_GAME_SESSIONS_T(:p_session_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_game_id_in, :p_player_id_in, :p_sum_list_out)');
            }else{
                if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
                    $start_date = $_SESSION['auth_space']['session']['last_time_collect'];
                }
                $stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_GAME_SESSIONS_T(:p_session_id_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_game_id_in, :p_player_id_in, :p_sum_list_out)');
            }
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            $stmt->bindParam(':p_game_id_in', $game_id);
            $stmt->bindParam(':p_player_id_in', $player_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_sum_list_out', $cursor);
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

    //lists game session subdetails (LEVEL 3) report
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $per_page
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGameSessionSubdetails($session_id, $page_number = 1, $per_page = 25){
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

    //list game session subdetails total
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listGameSessionSubdetailsTotal($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.GS_GAME_SESSION_SUBDETAILS_T(:p_session_id_in, :p_details_out)');
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

    //list game session subdetails total for mystery skill game
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listGameSessionMysterySkillSubdetailsTotal($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.GS_GAME_SESSION_SUBDET_MYST_T(:p_session_id_in, :p_details_out)');
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

    /**
     * @param $session_id
     * @param null $player_id
     * @param null $player_name
     * @param null $affiliate_id
     * @param null $ticket_status
     * @param null $ticket_id
     * @param null $currency
     * @param null $date_from
     * @param null $date_to
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listSportBetSessionReport($session_id, $player_id = null, $player_name = null, $affiliate_id = null, $ticket_status = null, $ticket_id = null,
        $currency = null, $date_from = null, $date_to = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        //DEBUG THIS PART
        /*
        $message = "REPORTS_BO.SPORT_BET_SESSION_REPORT(:p_session_id_in = {$session_id}, :p_player_id_in = {$player_id}, :p_player_name = {$player_name}, :p_affiliate_id_in = {$affiliate_id}, :p_ticket_status = {$ticket_status},
        :p_ticket_id = {$ticket_id}, :p_currency_ics = {$currency}, :p_date_from = {$date_from}, :p_date_to = {$date_to}, :p_list_out)";
		$helperErrorMail = new ErrorMailHelper();
		$helperErrorMail->writeInfo($message, $message);
        */
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.SPORT_BET_SESSION_REPORT(:p_session_id_in, :p_player_id_in, :p_player_name, :p_affiliate_id_in, :p_ticket_status, :p_ticket_id, :p_currency_ics, :p_date_from, :p_date_to, :p_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_player_name', $player_name);
			$stmt->bindParam(':p_affiliate_id_in', $affiliate_id);
			$stmt->bindParam(':p_ticket_status', $ticket_status);
			$stmt->bindParam(':p_ticket_id', $ticket_id);
			$stmt->bindParam(':p_currency_ics', $currency);
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

    //list player sessions report
    /**
     * @param $affiliate_id
     * @param $player_name
     * @param $start_date
     * @param $end_date
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listPlayerSessions($affiliate_id, $player_name, $start_date, $end_date, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        ///debug here
        //$message = "REPORTS_BO.PS_PLAYER_SESSIONS(:p_aff_id_in = {$affiliate_id}, :p_player_name_in = {$player_name}, :p_start_date_in = {$start_date}, :p_end_date_in = {$end_date}, :p_page_number_in = {$page_number}, :p_hits_per_page_in = {$hits_per_page}, :p_order_by_in = {$order_by}, :p_sort_order_in = {$sort_order}, :p_players_list_out)";
        //$helperErrorMail = new ErrorMailHelper();
        //$helperErrorMail->writeError($message, $message);
        ///
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $rola = $_SESSION['auth_space']['session']['subject_type_name'];
            $stmt = "";
            if($rola != ROLA_AD_COLLECTOR){
                $stmt = $dbAdapter->prepare('CALL REPORTS_BO.PS_PLAYER_SESSIONS(:p_aff_id_in, :p_player_name_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_players_list_out)');
            }else{
                if($_SESSION['auth_space']['session']['last_time_collect'] != ""){
                    $start_date = $_SESSION['auth_space']['session']['last_time_collect'];
                }
                $stmt = $dbAdapter->prepare('CALL REPORTS_BO.PS_PLAYER_SESSIONS(:p_aff_id_in, :p_player_name_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY hh24:mi:ss')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_players_list_out)');
            }
            $stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $stmt->bindParam(':p_player_name_in', $player_name);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            $stmt->bindParam(':p_page_number_in', $page_number);
            $stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
            $stmt->bindParam(':p_order_by_in', $order_by);
            $stmt->bindParam(':p_sort_order_in', $sort_order);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_players_list_out', $cursor);
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

    //list player session total report
    /**
     * @param $session_id
     * @param $player_name
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listPlayerSessionDetails($session_id, $player_name, $start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL REPORTS_BO.PS_PLAYER_SESSION_DETAILS(:p_session_id_in, :p_player_name_in, :p_start_date_in, :p_end_date_in, :p_players_list_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_player_name_in', $player_name);
            $stmt->bindParam(':p_start_date_in', $start_date);
            $stmt->bindParam(':p_end_date_in', $end_date);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':p_players_list_out', $cursor);
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

    //list player session details total (2nd level player sessions total)
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
    public static function listPlayerSessionDetailsTransactionsReport($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        //DEBUG THIS PART
        /*
        $message = "REPORTS_BO.tr_transaction_details(:p_session_id_in = {$session_id}, :p_trans_curs)";
		$helperErrorMail = new ErrorMailHelper();
		$helperErrorMail->writeInfo($message, $message);
        */
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.tr_transaction_details(:p_session_id_in, :p_trans_curs)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_trans_curs", $cursor);
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

}