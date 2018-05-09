<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
class TerminalPlayersModel{
	public function __construct(){
	}

	//list direct terminal players for terminal players list
    /**
     * @param $session_id
     * @param int $p_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param string $banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listDirectTerminalPlayers($session_id, $p_id = 0, $page_number = 1, $hits_per_page = 50, $banned = ALL){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_TERMINAL_PLAYERS_DIRECT(:p_session_id_in, :p_id_in, :p_baned_y_n, :p_page_number_in, :p_hits_per_page_in, :p_null_not_null_in, :list_terminal_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $p_id);
			$stmt->bindParam(':p_baned_y_n', $banned);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$type = YES;
			$stmt->bindParam(':p_null_not_null_in', $type);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":list_terminal_out", $cursor);
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

	//return type from terminal player
    /**
     * @param $player_type
     * @return mixed
     * @throws Zend_Exception
     */
	private static function getTerminalPlayerType($player_type){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('BEGIN :p := DYNVAR.VAL(:var_in); END;');
			$stmt->bindParam(':var_in', $player_type);
			$value_out = "";
			$stmt->bindParam(':p', $value_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("value"=>$value_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	//searches terminals
    /**
     * @param $session_id
     * @param int $pageNo
     * @param int $perPage
     * @param int $column
     * @param string $order
     * @param null $username
     * @param null $hardware_key
     * @param null $ba_serial
     * @param null $city
     * @param null $country
     * @param null $parent_aff
     * @param null $currency
     * @param string $show_banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function search($session_id, $pageNo = 1, $perPage = 25, $column = 1, $order = 'asc', $username = null, $hardware_key = null, $ba_serial = null, $city = null, $country = null, $parent_aff = null, $currency = null, $show_banned = YES){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		if($currency == "" || is_null($currency)){
            $currency = ALL;
        }
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$SEARCH_USERS(:p_session_id_in, :p_user_name_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_country_in, :p_parent_aff_in, :p_currency_in, :p_banned_in, :p_subject_type_name, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_users_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_user_name_in', $username);
			$stmt->bindParam(':p_first_name_in', $hardware_key);
			$stmt->bindParam(':p_last_name_in', $ba_serial);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_parent_aff_in', $parent_aff);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_banned_in', $show_banned);
			$subject_type_res = SubjectTypesModel::getSubjectType("MANAGMENT_TYPES.NAME_IN_TERMINAL_PLAYER");
            $subject_type = $subject_type_res['value'];
			$stmt->bindParam(':p_subject_type_name', $subject_type);	
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);
			$stmt->bindParam(':p_sort_order_in', $order);
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
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}
	}
	//returns terminal player details
    /**
     * @param $session_id
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getTerminalPlayerDetails($session_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SUBJECT_DETAIL(:p_session_id_in, :p_subject_id_in, :p_subject_detail_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $player_id);
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
			$helperErrorMail = new ErrorMailHelper();
			$helperErrorMail->writeError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	//returns terminal players and lists active terminals
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param string $banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getTerminalPlayers($session_id, $page_number = 1, $hits_per_page = 25, $banned = ALL){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_TERMINAL_PLAYERS(:p_session_id_in, :p_id_in, :p_baned_y_n, :p_page_number_in, :p_hits_per_page_in, :list_terminal_out)');
            //$stmt = $dbAdapter->prepare('CALL QUICK_FIX.M$LIST_TERMINAL_PLAYERS(:p_session_id_in, :p_id_in, :p_baned_y_n, :p_page_number_in, :p_hits_per_page_in, :list_terminal_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$p_id = 0;
			$stmt->bindParam(':p_id_in', $p_id);
			$stmt->bindParam(':p_baned_y_n', $banned);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_terminal_out', $cursor);
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
}