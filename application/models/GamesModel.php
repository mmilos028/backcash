<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';

class GamesModel{
	public function __construct(){
	}

	//lists games in backoffice
    /**
     * @param $session_id
     * @param int $game_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getGames($session_id, $game_id = 0, $page_number = 1, $hits_per_page = 1000000, $order_by = 2, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{	
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_GAMES(:p_session_id_in, :p_game_id_in, :p_games_out, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$p_game_id_in = 0;
			$stmt->bindParam(':p_game_id_in', $p_game_id_in);
			$stmt->bindCursor(':p_games_out', $cursor);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
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
			throw $ex;
		}
	}
	
	//returns game details in backoffice
    /**
     * @param $session_id
     * @param $game_id
     * @return mixed
     * @throws Zend_Exception
     */
	public static function getGame($session_id, $game_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{	
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_GAMES(:p_session_id_in, :p_game_id_in, :p_games_out, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_game_id_in', $game_id);
			$stmt->bindCursor(':p_games_out', $cursor);
			$page_number= 0;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 0;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
			$sort_order = 'asc';
			$stmt->bindParam(':p_sort_order_in', $sort_order);
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
			throw $ex;
		}
	}
}

