<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';

class CashierReportModel{

	public function __construct(){}

    //list active bo sessions report
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function listActiveBoSessions($session_id, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_ACTIVE_BO_SESSIONS(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_list_bo_sessions)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_bo_sessions', $cursor);
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

    /**
     * @param $session_id
     * @param $player_id
     * @param $game_id
     * @param $page
     * @param $hits_per_page
     * @return mixed
     * @throws Zend_Exception
     */
    public static function getActiveGameSessionST($session_id, $player_id, $game_id, $page, $hits_per_page){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS_BO.ACTIVE_GAME_SESSIONS_ST(:p_session_id_in, :p_player_id_in, :p_game_id_in, :p_page_in, :p_hits_per_page_in, :p_list_game_stat)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$stmt->bindParam(':p_page_in', $page);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_game_stat', $cursor);
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
            return null;
		}
	}

	//new active game sessions report for backoffice
    /**
     * @param $session_id
     * @param $page
     * @param $hits_per_page
     * @return null
     * @throws Zend_Exception
     */
	public static function getActiveGameSession($session_id, $page, $hits_per_page){
		//$cacheObj = Zend_Registry::get('db_cache');
		//$result = unserialize($cacheObj->load("REPORTS_BO__ACTIVE_GAME_SESSIONS_session_id_{$session_id}_page_{$page}_hits_per_page_{$hits_per_page}") );
		//if(!isset($result) || $result == null || !$result){

		//$time1 = microtime(true);
		//require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
		//$errorMailHelper = new ErrorMailHelper();
		//$errorMailHelper->sendMail("STARTED <br /> REPORTS_BO.ACTIVE_GAME_SESSIONS(:p_session_id_in = {$session_id}, :p_page_in = {$page}, :p_hits_per_page_in = {$hits_per_page}, :p_list_game_stat)");

		if(true){
            /* @var $dbAdapter Zend_Db_Adapter_Oracle */
			$dbAdapter = Zend_Registry::get('db_auth');
			$dbAdapter->beginTransaction();
			try{
				$stmt = $dbAdapter->prepare('CALL REPORTS_BO.ACTIVE_GAME_SESSIONS(:p_session_id_in, :p_page_in, :p_hits_per_page_in, :p_list_game_stat)');
				$stmt->bindParam(':p_session_id_in', $session_id);
				$stmt->bindParam(':p_page_in', $page);
				$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
				$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
				$stmt->bindCursor(':p_list_game_stat', $cursor);
				$stmt->execute(null, false);
				//$time2 = microtime(true);
                //$errorMailHelper->sendMail("STATEMENT EXECUTE <br /> REPORTS_BO.ACTIVE_GAME_SESSIONS(:p_session_id_in = {$session_id}, :p_page_in = {$page}, :p_hits_per_page_in = {$hits_per_page}, :p_list_game_stat) <br /> TIME REQUIRED: " . ($time2 - $time1));
				$dbAdapter->commit();
				//$time3 = microtime(true);
                //$errorMailHelper->sendMail("DBADAPTER.COMMIT <br /> REPORTS_BO.ACTIVE_GAME_SESSIONS(:p_session_id_in = {$session_id}, :p_page_in = {$page}, :p_hits_per_page_in = {$hits_per_page}, :p_list_game_stat) <br /> TIME REQUIRED: " . ($time3 - $time2));
				$cursor->execute();
				//$time4 = microtime(true);
                //$errorMailHelper->sendMail("CURSOR.EXECUTE <br /> REPORTS_BO.ACTIVE_GAME_SESSIONS(:p_session_id_in = {$session_id}, :p_page_in = {$page}, :p_hits_per_page_in = {$hits_per_page}, :p_list_game_stat) <br /> TIME REQUIRED: " . ($time4 - $time3));
				$cursor->free();
				$dbAdapter->closeConnection();
				$help = new CursorToArrayHelper($cursor);
				$table = $help->getTableRows();
				$info = $help->getPageRow();
				$result = array($table, $info);
				//$cacheObj->save(serialize($result), "REPORTS_BO__ACTIVE_GAME_SESSIONS_session_id_{$session_id}_page_{$page}_hits_per_page_{$hits_per_page}");
           		//$time5 = microtime(true);
                //$errorMailHelper->sendMail("ENDED <br /> REPORTS_BO.ACTIVE_GAME_SESSIONS(:p_session_id_in = {$session_id}, :p_page_in = {$page}, :p_hits_per_page_in = {$hits_per_page}, :p_list_game_stat) <br /> TIME REQUIRED: " . ($time5 - $time1));
				return array("result"=>$result);
			}catch(Zend_Exception $ex){
				$dbAdapter->rollBack();
				$dbAdapter->closeConnection();
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				ErrorMailHelper::writeError($message, $message);
                return null;
			}
		}else{
			//return $result;
            return null;
		}
	}
}