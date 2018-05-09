<?php
require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
class PlayersModel{
	public function __construct(){
	}
	//reset wrong logins to player in gc application
    /**
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function resetWrongLoginsLeft($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.reset_wrong_logins_left(:p_subject_id_in)');
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
            throw new Zend_Exception($ex);
		}
	}
	
	//list direct players for player list
    /**
     * @param $session_id
     * @param int $p_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param string $banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listDirectPlayers($session_id, $p_id = 0, $page_number = 1, $hits_per_page = 50, $banned = ALL){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_DIRECT_AFFILIATES(:p_session_id_in, :p_id_in, :p_subject_type, :p_player_type, :p_baned_y_n, :p_page_number_in, :p_hits_per_page_in, :p_affiliates_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $p_id);
			$subject_type = SubjectTypesModel::getSubjectType('MANAGMENT_TYPES.NAME_IN_PLAYER');
            $subject_type = $subject_type['value'];
			$stmt->bindParam(':p_subject_type', $subject_type);
			$player_type = self::getPlayerTypeID($session_id, $subject_type);
			$stmt->bindParam(':p_player_type', $player_type['player_type_id']);
			$stmt->bindParam(':p_baned_y_n', $banned);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_affiliates_out', $cursor);
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
	
	//search for players in backoffice
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
     * @param null $currency
     * @param string $show_banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function search($session_id, $pageNo = 1, $perPage = 25, $column = 1, $order = 'asc', $username = null, $first_name = null, $last_name = null, $city = null, $country = null, $parent_aff = null, $currency = null, $show_banned = YES){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		if($currency == '' || is_null($currency))
			$currency = ALL;
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$SEARCH_USERS(:p_session_id_in, :p_user_name_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_country_in, :p_parent_aff_in, :p_currency_in, :p_banned_in, :p_subject_type_name, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_users_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_user_name_in', $username);
			$stmt->bindParam(':p_first_name_in', $first_name);
			$stmt->bindParam(':p_last_name_in', $last_name);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_parent_aff_in', $parent_aff);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_banned_in', $show_banned);
			$subject_type = SubjectTypesModel::getSubjectType('MANAGMENT_TYPES.NAME_IN_PC_PLAYER');
            $subject_type = $subject_type['value'];
			$stmt->bindParam(':p_subject_type_name', $subject_type);			
			$stmt->bindParam(':p_page_number_in', $pageNo);
			$stmt->bindParam(':p_hits_per_page_in', $perPage);
			$stmt->bindParam(':p_order_by_in', $column);	
			$stmt->bindParam(':p_sort_order_in', $order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':list_users_out', $cursor);
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
		
	//returns player details in backoffice
    /**
     * @param $session_id
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getPlayerDetails($session_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SUBJECT_DETAIL(:p_session_id_in, :p_subject_id_in, :p_subject_detail_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_subject_detail_out', $cursor);
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

	//returns player details in backoffice
    /**
     * @param $session_id
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getPlayer($session_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_AFFILIATES_PLAYERS(:p_session_id_in, :p_id_in, :p_subject_type, :p_player_type, :p_baned_y_n, :p_page_number_in, :p_hits_per_page_in, :p_game_pay_back_y_n, :p_affiliates_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_id_in', $player_id);
			$subject_type = SubjectTypesModel::getSubjectType('MANAGMENT_TYPES.NAME_IN_PC_PLAYER');
            $subject_type = $subject_type['value'];
			$stmt->bindParam(':p_subject_type', $subject_type);
			
			$player_type = self::getPlayerTypeID($session_id, $subject_type);
			$stmt->bindParam(':p_player_type', $player_type['player_type_id']);
			$banned = ALL;
			$stmt->bindParam(':p_baned_y_n', $banned);
			
			$page_number = 1;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 1;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			//$player_type = $this->getPlayerTypeID($session_id, 'PC player - Casino');
		    //$stmt->bindParam(':p_player_type', $player_type);
			$game_pay_back_y_n = NO;
			$stmt->bindParam(':p_game_pay_back_y_n', $game_pay_back_y_n);
			$stmt->bindCursor(':p_affiliates_out', $cursor);
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
	
	//resets player password in system
    /**
     * @param $session_id
     * @param $subject_id
     * @param $password_new
     * @return mixed
     * @throws Zend_Exception
     */
	public static function resetPlayerPassword($session_id, $subject_id, $password_new){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$RESET_PASSWORD(:p_session_id_in, :p_subject_id_in, :p_password_new_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->bindParam(':p_password_new_in', $password_new);
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
	
	//returns list of players in system
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $per_page
     * @param string $banned
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getPlayers($session_id, $page_number = 1, $per_page = 50, $banned = ALL){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();		
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_AFFILIATES_PLAYERS(:p_session_id_in, :p_id_in, :p_subject_type, :p_player_type, :p_baned_y_n, :p_page_number_in, :p_hits_per_page_in, :p_game_pay_back_y_n, :p_affiliates_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$p_id = 0;
			$stmt->bindParam(':p_id_in', $p_id);
			$subject_type = SubjectTypesModel::getSubjectType('MANAGMENT_TYPES.NAME_IN_PLAYER');
            $subject_type = $subject_type['value'];
			$stmt->bindParam(':p_subject_type', $subject_type);
			$player_type = self::getPlayerTypeID($session_id, $subject_type);
			$stmt->bindParam(':p_player_type', $player_type['player_type_id']);
			$stmt->bindParam(':p_baned_y_n', $banned);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $per_page);
			$game_pay_back_y_n = NO;
			$stmt->bindParam(':p_game_pay_back_y_n', $game_pay_back_y_n);
			$stmt->bindCursor(':p_affiliates_out', $cursor);
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
	
	//returns player type id in system
    /**
     * @param $session_id
     * @param $player_type_name
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getPlayerTypeID($session_id, $player_type_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_PLAYERS_ROLES(:p_session_id_in, :p_player_type_name_in, :p_player_type_ID)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_player_type_name_in', $player_type_name);
			$player_type_id = 0;
			$stmt->bindParam(':p_player_type_ID', $player_type_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            return array("player_type_id", $player_type_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			ErrorMailHelper::writeError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
}